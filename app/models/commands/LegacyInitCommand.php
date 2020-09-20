<?php

namespace App\Models\Commands;

use Nette\Database\Connection;
use Nette\Http\Session;
use Nette\Security\User;
use App\Extensions\App\User\IdentityFactory;
use App\Models\Orm\Orm;
use App\Models\Backward\LegacyMap;
use App\Models\Repositories\SettingsRepository;
use Nette\DI\Container;

class LegacyInitCommand{

	/** @var Orm */
	private $orm;

	/** @var Session */
	private $ses;

	/** @var Connection */
	private $conn;

	/** @var User */
	private $user;

	/** @var Container */
	private $container;

	/** @var IdentityFactory */
	private $if;

	/** @var SettingsRepository */
	private $repSettings;

	public function __construct(
		Orm $orm,
		User $user,
		Session $ses,
		Connection $conn,
		IdentityFactory $if,
		Container $container,
		SettingsRepository $repSettings)
	{
		$this->if = $if;
		$this->orm = $orm;
		$this->ses = $ses;
		$this->user = $user;
		$this->conn = $conn;
		$this->container = $container;
		$this->repSettings = $repSettings;
	}

	public function execute()
	{
		if(class_exists('\OBE_App', false)){
			return;
		}

		if(!$this->user->isLoggedIn()){
			$this->user->login($this->if->create([
				'id' => 1
			]));
		}

		$user = $this->orm->users->getById($this->user->id);

		include_once (APP_DIR . '../admin/old.php');

		\OBE_App::$newVars = [
			'komunikace' => LegacyMap::getKomunikace($this->repSettings),
			'front' => LegacyMap::getFront($this->repSettings)
		];
		\OBE_AppCore::init();
		\OBE_App::$db->setNDB($this->conn);
		\OBE_Language::$id = 1;
		\OBE_AppCore::LoadDBVars();

		$a = [];
		$a['superuser'] = $this->user->isInRole('super');
		$a['platby'] = $this->user->isAllowed('Platby', 'view');
		$a['onlyown'] = !$this->user->isAllowed('Klients', 'view_all');
		$a['chowner'] = !$this->user->isAllowed('Klients', 'change_owner');
		$a['delegate'] = $this->user->isAllowed('User', 'edit');

		\AdminApp::$container = $this->container;
		\AdminUserClass::init($a + $user->toArray(), $this->user->isInRoles('super'), $this->ses->getSection('legacy'));
	}
}