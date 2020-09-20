<?php
use Phinx\Migration\AbstractMigration;

class SettingsDalsi extends AbstractMigration{

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
		$t = $this->table('settings');

		$t->changeColumn('value', 'string', [
			'limit' => 100,
			'null' => true
		])->save();

		$t->insert([
			'key' => 'splatnost',
			'value' => 12,
			'type' => 'integer',
			'require' => true,
			'description' => 'Splatnost dnů',
			'group' => 'main'
		])->save();

		$t->insert(
			[
				'key' => 'uhrada',
				'value' => 'bankovním převodem',
				'type' => 'string',
				'require' => true,
				'description' => 'Úhrada na fakturu',
				'group' => 'main'
			])->save();

		$t->insert(
			[
				'key' => 'cislo_uctu',
				'value' => '',
				'type' => 'string',
				'require' => true,
				'description' => 'Číslo účtu na doklady',
				'group' => 'company'
			])->save();

		$t->insert(
			[
				'key' => 'ident_a',
				'value' => '',
				'type' => 'string',
				'require' => true,
				'description' => 'Identifikace obchodníka A',
				'group' => 'company'
			])->save();

		$t->insert(
			[
				'key' => 'ident_b',
				'value' => '',
				'type' => 'string',
				'require' => true,
				'description' => 'Identifikace obchodníka B',
				'group' => 'company'
			])->save();

		$t->insert(
			[
				'key' => 'platby_mail_server',
				'value' => '{pop3.localhost:143/notls/novalidate-cert}',
				'type' => 'string',
				'require' => true,
				'description' => 'Mail server',
				'group' => 'platby'
			])->save();

		$t->insert(
			[
				'key' => 'platby_mail_login',
				'value' => 'banka@localhost',
				'type' => 'string',
				'require' => true,
				'description' => 'Login',
				'group' => 'platby'
			])->save();

		$t->insert([
			'key' => 'platby_mail_pass',
			'value' => '',
			'type' => 'string',
			'require' => true,
			'description' => 'Heslo',
			'group' => 'platby'
		])->save();

		$t->insert(
			[
				'key' => 'platby_mail_folder',
				'value' => '{pop3.localhost}INBOX',
				'type' => 'string',
				'require' => true,
				'description' => 'Složka',
				'group' => 'platby'
			])->save();

		$t->insert(
			[
				'key' => 'ote_mail_server',
				'value' => '{pop3.localhost:143/notls/novalidate-cert}',
				'type' => 'string',
				'require' => true,
				'description' => 'Mail server',
				'group' => 'ote'
			])->save();

		$t->insert([
			'key' => 'ote_mail_login',
			'value' => 'ote@localhost',
			'type' => 'string',
			'require' => true,
			'description' => 'Login',
			'group' => 'ote'
		])->save();

		$t->insert([
			'key' => 'ote_mail_pass',
			'value' => '',
			'type' => 'string',
			'require' => true,
			'description' => 'Heslo',
			'group' => 'ote'
		])->save();

		$t->insert(
			[
				'key' => 'ote_mail_folder',
				'value' => '{pop3.localhost}INBOX',
				'type' => 'string',
				'require' => true,
				'description' => 'Složka',
				'group' => 'ote'
			])->save();

		$t->insert(
			[
				'key' => 'ote_cert_file',
				'value' => '',
				'type' => 'file',
				'require' => true,
				'description' => 'OTE Certifikát pro příjem zpráv',
				'group' => 'ote'
			])->save();

		$t->insert(
			[
				'key' => 'ote_cert_pass',
				'value' => '',
				'type' => 'string',
				'require' => true,
				'description' => 'Heslo pro extrakci pri. klíče',
				'group' => 'ote'
			])->save();

		$t->insert(
			[
				'key' => 'ote_cert_valid',
				'value' => '',
				'type' => 'date',
				'require' => true,
				'description' => 'Platnost certifikátu do',
				'group' => 'ote'
			])->save();

		$t->insert(
			[
				'key' => 'ote_cert_smime',
				'value' => '/config/certs/ote-smime.pem',
				'type' => 'file',
				'require' => true,
				'description' => 'Certifikát smime OTE',
				'group' => 'ote'
			])->save();
	}
}
