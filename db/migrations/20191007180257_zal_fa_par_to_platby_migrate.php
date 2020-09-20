<?php
use Phinx\Migration\AbstractMigration;

class ZalFaParToPlatbyMigrate extends AbstractMigration{

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
		$vazby = $this->fetchAll(
			'SELECT
				    pp.id, p.platba_id, f.`klient_id`, f.`om_id`, f.`vystaveno`, f.`cis`, f.`preplatek`, f.`uhrazeno`, p.`platba`
				FROM
				    tx_pv_par_fz AS pp, tx_platby AS p, tx_faktury AS f
				WHERE pp.`platba_id` = p.`platba_id`
				    AND pp.`faktura_id` = f.`id`
				    AND pp.`zaloha_id` IS NULL
				ORDER BY cis DESC');

		$items = collection($vazby)->groupBy('platba_id')->toArray();

		$ins = [];
		foreach($items as $platbaId => $i){
			if(count($i) == 1){
				$ins[] = [
					'platba_id' => $i[0]['platba_id'],
					'klient_id' => $i[0]['klient_id'],
					'om_id' => $i[0]['om_id']
				];
				continue;
			}

			$ic = collection($i);
			$klis = array_unique($ic->extract('klient_id')->toList());
			$oms = array_unique($ic->extract('om_id')->toList());

			if(count($klis) > 1){
				continue;
			}

			if(count($oms) > 1){
				$ins[] = [
					'platba_id' => $platbaId,
					'klient_id' => $i[0]['klient_id'],
					'om_id' => null
				];
				continue;
			}

			$ins[] = [
				'platba_id' => $platbaId,
				'klient_id' => reset($klis),
				'om_id' => reset($oms)
			];
		}

		foreach($ins as $i){
			$this->execute('DELETE FROM tx_pv_par_fz WHERE platba_id = ' . $i['platba_id'] . ';');
			$this->execute('UPDATE tx_platby SET type = \'f\' WHERE platba_id = ' . $i['platba_id'] . ';');
		}

		$this->table('platby_zarazeni')
			->insert($ins)
			->save();
	}
}