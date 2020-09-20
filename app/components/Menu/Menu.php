<?php
namespace App\Components;

use App\Extensions\Components\Menu\BaseMenu;
use App\Extensions\Components\Menu\MenuItemLink;

class Menu extends BaseMenu{

	/**
	 * {@inheritdoc}
	 * @see BaseMenu::build()
	 */
	protected function build()
	{
		$this->addItem(new MenuItemLink('/admin/index.php?module=contacts', 'Klients:'), 'Odběratelé', 'md md-account-child');
		$this->addItem(new MenuItemLink('/admin/index.php?module=fakskups', 'FakSkups:'), 'Fakturační skupiny', 'fa fa-folder-open');
		$this->addItem(new MenuItemLink('/admin/index.php?module=odbermist', 'OdberMist:'), 'Odběrná místa', 'md md-av-timer');
		$this->addItem('Zalohy:Default:', 'Zálohy', 'fa fa-leanpub');
		$this->addItem('Faktury:Default:', 'Faktury', 'fa fa-dollar');
		$this->addItem('Platby:Default:', 'Platby', 'fa fa-money');
		$this->addItem('AccountBalance:Default:', 'Zúčtování', 'md md-account-balance');
		$this->addItem('OteZpravy:Default:', 'OTE Zprávy', 'md md-message');
		$this->addItem('OteGP6:Default:', 'OTE GP6', 'md md-crop');
		$this->addItem('Activity:Default:', 'Audit log', 'md md-traffic');
		$this->addItem('Info:Default:', 'Info', 'md md-settings-remote');

		$n = $this->addNode('Nastaveni:', 'Nastavení', 'md md-settings');

		$n->addItem('Settings:Default:', 'Nastavení', 'md md-brightness-high');
		$n->addItem('User:Default:', 'Uživatelé', 'glyphicon glyphicon-user');
		$n->addItem('Role:Default:', 'Role', 'fa fa-users');
		$n->addItem('Tags:Default:', 'Tags', 'fa fa-tags');
		$n->addItem('Helper:Default:', 'Nápověda', 'fa fa-question');
		$n->addItem('Ciselniky:Default:', 'Číselníky', 'fa fa-gears');

		$this->addItem('Service:', 'Servis', 'fa fa-gears');
		$this->addItem('MailBoxes:Default:', 'Mail Boxy', 'md md-email');
	}
}