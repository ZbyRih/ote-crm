<?php
use Phinx\Migration\AbstractMigration;

class DokladyMigrate extends AbstractMigration{

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
		$ins = [];
		$plas = $this->fetchAll('SELECT * FROM tx_platby WHERE cislo IS NOT NULL');

		$ts = (new \DateTime('2019-08-31 23:59:59'))->getTimestamp();

		foreach($plas as $p){

			$dt = new \DateTime($p['vystaven']);

			if($dt->getTimestamp() <= $ts){
				$dk = 0.1736;
			}else{
				$dk = 0.17355371900826;
			}

			$ins[] = [
				'platba_id' => $p['platba_id'],
				'created' => $p['vystaven'],
				'cislo' => $p['cislo'],
				'platba' => $p['platba'],
				'dph_coef' => $dk,
				'den_zdan_pln' => $p['vystaven']
			];
		}

		$this->table('doklady')
			->insert($ins)
			->save();
	}
}

// potom smazat
// preplatek   decimal(14,2)        (NULL)         YES             0.00                     select,insert,update,references
// cislo       varchar(14)          utf8_czech_ci  YES             (NULL)                   select,insert,update,references
// vystaven    datetime             (NULL)         YES             (NULL)                   select,insert,update,references
// zaloha_id   bigint(20)           (NULL)         YES             (NULL)                   select,insert,update,references
// faktura_id  int(20)              (NULL)         YES             (NULL)                   select,insert,update,references