<?php


class MInfo extends ModelClass{

	var $name = 'Info';

	var $table = 'tx_info';

	var $primaryKey = 'id';

	var $rows = [
		'id',
		'user_id',
		'created',
		'viewed',
		'message',
		'type'
	];

	public function addInfo($userId, $message, $type){
		foreach($userId as $id){
			$a = [
				$this->name => [
					'user_id' => $id,
					'message' => $message,
					'created' => 'NOW()',
					'type' => $type
				]
			];
			$this->Save($a);
		}
	}

	public function getNew($userId){
		return MArray::GetMArrayForOneModel(
			$this->FindAll([
				'!(DATE(`viewed`) > DATE(NOW() - INTERVAL 1 DAY) OR viewed IS NULL)',
				'user_id' => $userId
			], [], [
				'!-ISNULL(viewed)' => '',
				'created' => 'DESC'
			]), $this->name);
	}

	public function getNewCount($userId){
		if($c = $this->Count('id', [
			'!viewed IS NULL',
			'user_id' => $userId
		])){
			return reset($c)['num'];
		}
		return 0;
	}

	public function setViewed($userId){
		$viewed = $this->FindAll([
			'!viewed IS NULL',
			'user_id' => $userId
		]);

		foreach($viewed as $k => $v){
			$viewed[$k][$this->name]['viewed'] = 'NOW()';
		}

		$this->Save($viewed);
	}
}