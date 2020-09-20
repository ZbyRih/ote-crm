<?php
use Phinx\Migration\AbstractMigration;

class ZarazeniDoFakSkup extends AbstractMigration{

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
		$platby = $this->fetchAll(
			'
				SELECT
				    p.platba_id, p.platba, p.when, so.`id` AS soId, so.`fak_skup_id`, fs.cis, pz.id AS pzId
				FROM
				    tx_sml_om AS so,
				    tx_fak_skup AS fs,
				    tx_platby AS p,
				    platby_zarazeni AS pz
				WHERE
					so.`fak_skup_id` IS NOT NULL
				AND
					fs.`fak_skup_id` = so.`fak_skup_id`
				AND
					p.when BETWEEN so.`od` AND so.`do`
				AND
					p.type = \'z\'
				AND
					p.vs = fs.`cis`
				AND
					pz.platba_id = p.`platba_id`
				GROUP BY
					p.`platba_id`');

		foreach($platby as $p){
			$this->execute('UPDATE platby_zarazeni SET fakskup_id = ' . $p['fak_skup_id'] . ' WHERE id = ' . $p['pzId'] . ';');
		}
	}
}
