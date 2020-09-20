<?php
use Phinx\Migration\AbstractMigration;

class SetRoles extends AbstractMigration{

	/**
	 * Change Method.
	 * Write your reversible migrations using this method.
	 * More information on writing migrations is available here:
	 * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
	 * The following commands can be used in this method and Phinx will
	 * automatically reverse them when rolling back:
	 * createTable
	 * renameTable
	 * addColumn
	 * addCustomColumn
	 * renameColumn
	 * addIndex
	 * addForeignKey
	 * Any other destructive changes will result in an error when trying to
	 * rollback the migration.
	 * Remember to call "create()" or "update()" and NOT "save()" when working
	 * with the Table class.
	 */
	public function change()
	{
		$this->query(
			'UPDATE role SET perms =\'{"Info": "delete", "Logs": "delete", "Role": "delete", "Tags": "delete", "User": "delete", "Denied": "view", "Helper": "delete", "Legacy": "delete", "OteGP6": "delete", "Platby": "delete", "Pohyby": "delete", "Zalohy": "delete", "Faktury": "delete", "Klients": "delete", "Service": 0, "Activity": "delete", "FakSkups": "delete", "Homepage": "view", "Settings": "delete", "Ciselniky": "delete", "Nastaveni": "delete", "OdberMist": "delete", "OteZpravy": "delete", "Role_priv": {"change": 1}, "Templates": "delete", "Klients_priv": {"view_all": 1, "change_owner": 1}, "OteAComSettings": "delete"}\' WHERE id = 1');
		$this->query(
			'UPDATE role SET perms =\'{"Info": "delete", "Logs": 0, "Role": 0, "Tags": 0, "User": 0, "Denied": "view", "Helper": "delete", "Legacy": 0, "OteGP6": "delete", "Platby": 0, "Pohyby": 0, "Zalohy": "view", "Faktury": 0, "Klients": "delete", "Service": 0, "Activity": 0, "FakSkups": 0, "Homepage": "view", "Settings": 0, "Ciselniky": 0, "Nastaveni": 0, "OdberMist": 0, "OteZpravy": 0, "Role_priv": {"change": 0}, "Templates": 0, "Klients_priv": {"view_all": 0, "change_owner": 0}, "OteAComSettings": 0}\' WHERE id = 2');
		$this->query(
			'UPDATE role SET perms =\'{"Info": "view", "Logs": 0, "Role": 0, "Tags": "view", "User": 0, "Denied": 0, "Helper": "view", "Legacy": 0, "OteGP6": "view", "Platby": "view", "Pohyby": "view", "Zalohy": "view", "Faktury": "view", "Klients": "delete", "Service": 0, "Activity": 0, "FakSkups": 0, "Homepage": 0, "Settings": 0, "Ciselniky": 0, "Nastaveni": 0, "OdberMist": 0, "OteZpravy": "view", "Role_priv": {"change": 0}, "Templates": 0, "Klients_priv": {"view_all": 0, "change_owner": 1}, "OteAComSettings": 0}\' WHERE id = 5');
	}
}
