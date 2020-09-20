<?php
namespace App\Components\Service;

use App\Extensions\Helpers\BSHtmlHelpers;
use App\Extensions\Utils\Html;
use Nette\DI\Container;
use Nette\Application\Responses\FileResponse;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Carbon\Carbon;

class ServiceLog extends ServicePanel{

	private $container;

	private $logDir;

	private $tempDir;

	public function __construct(Container $container){
		$this->container = $container;

		$pars = $this->container->getParameters();

		$this->logDir = $pars['appDir'] . '/../log';
		$this->tempDir = $pars['tempDir'];

		$actions = [
			'clearLogs' => [
				'kind' => 'primary',
				'color' => 'red',
				'title' => 'Smazat Logy'
			],
			'downloadLogs' => [
				'kind' => 'primary',
				'color' => 'yellow',
				'title' => 'Stáhnout logy'
			]
		];

		parent::__construct('Log', [], $actions, function (){
			return $this->createList();
		});
	}

	public function createList(){
		$files = [];

		foreach(Finder::findFiles('exception-*')->in($this->logDir) as $k => $f){
			$time = date("Y m.d. H:i:s", filectime($f));

			$link = $this->link('openLogFile!', basename($f));
			$btn = BSHtmlHelpers::button('Otevřít', 'default btn-xs', $link)->setAttribute('target', '_blank');

			$files[$time . '-' . $k] = Html::el('tr')->addHtml(Html::el('td')->addText($time))
				->addHtml(Html::el('td')->addHtml($btn));
		}

		ksort($files, SORT_STRING);
		$files = array_reverse($files);

		if(!$files){
			return Html::el('p')->class('alert alert-info')->addText('Na serveru nejsou logy');
		}

		$table = Html::el('table')->class('table table-condensed table-striped');

		foreach($files as $f){
			$table->addHtml($f);
		}

		return $table;
	}

	public function handleOpenLogFile($file){
		echo file_get_contents($this->logDir . '/' . $file);
		$this->presenter->terminate();
	}

	public function handleClearLogs(){
		foreach(Finder::findFiles('*')->in($this->logDir) as $f){
			FileSystem::delete($f);
		}
		$this->presenter->flashSuccess('Logy smazány.');
		$this->presenter->redirect('default');
	}

	public function handleDownloadLogs(){
		$zip = new \ZipArchive();
		$file = $this->tempDir . '/logs_' . Carbon::now()->format('Ymd_His') . '.zip';

		if($zip->open($file, \ZipArchive::CREATE) !== true){
			$this->presenter->flashError('Zip se nepodařilo vytvořit');
			$this->presenter->redirect('default');
			return;
		}

		foreach(Finder::findFiles('exception-*')->in($this->logDir) as $f){
			$zip->addFile($f, basename($f));
		}

		$zip->close();

		$this->presenter->sendResponse(new FileResponse($file));
	}
}