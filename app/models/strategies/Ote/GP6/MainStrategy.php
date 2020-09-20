<?php

namespace App\Models\Strategies\Ote\GP6;

use App\Models\Orm\OteGP6Head\OteGP6HeadEntity;
use Carbon\Carbon;
use App\Models\Orm\OteGP6Body\OteGP6BodyEntity;
use App\Models\Selections\OdberMistSelection;
use App\Models\Strategies\Ote\XmlUtils;
use App\Models\Strategies\Ote\OteXmlException;

class MainStrategy{

	/** @var OdberMistSelection */
	private $selOdbm;

	public function __construct(
		OdberMistSelection $selOdbm)
	{
		$this->selOdbm = $selOdbm;
	}

	public function execute(
		$xml)
	{
		$oteId = $xml['id'];

		foreach($xml as $node){
			if($node->getName() != 'invoice'){
				continue;
			}

			if(!isset($node->head) || !isset($node->body)){
				continue;
			}

			$headEntity = $this->getHead($node->head);
			$bodyEntity = $this->getBody($node->body);

			if(!$headEntity || !$bodyEntity){
				continue;
			}

			$headEntity->depricated = false;
			$headEntity->type = $bodyEntity->type;
			$headEntity->oteId = $oteId;

			$bodyEntity->headId = $headEntity;
			$bodyEntity->oteId = $oteId;

			$res = $this->selOdbm->findByEic($headEntity->subjectsOpm);
			if(empty($res)){
				throw new OteXmlException('Pro XML se nenašlo odběrné místo');
			}

			$om = reset($res);
			$headEntity->odberMistId = $om->odber_mist_id;
			$bodyEntity->odberMistId = $om->odber_mist_id;

			return $bodyEntity;
		}
		return null;
	}

	/**
	 *
	 * @param \SimpleXMLElement $node
	 * @return OteGP6HeadEntity
	 */
	protected function getHead(
		$node)
	{
		$a = current($node->attributes());

		$entity = new OteGP6HeadEntity();

		$entity->pofId = (isset($a['pofId']) ? $a['pofId'] : null);
		$entity->version = (isset($a['version']) ? $a['version'] : null);
		$entity->priceTotal = $a['priceTotal'];
		$entity->priceTotalDph = (isset($a['priceTotalDph']) ? $a['priceTotalDph'] : null); // float 14,2
		$entity->from = Carbon::parse($a['periodFrom']);
		$entity->to = Carbon::parse($a['periodTo']);
		$entity->cancelled = (isset($a['cancelled']) ? true : false);
		$entity->yearReCalculatedValue = (isset($a['yearReCalculatedValue']) ? $a['yearReCalculatedValue'] : null); // 16,2
		$entity->attributesSegment = (string) $node->attributes['segment']; // INV, COR, EXT, CAN, EOC, HST
		$entity->attributesNumber = XmlUtils::extractAttribute($node, 'number'); // 15
		$entity->attributesAnumber = XmlUtils::extractAttribute($node, 'anumber'); // 25
		$entity->attributesCorReason = XmlUtils::extractAttribute($node, 'corReason'); // 2
		$entity->attributesComplId = XmlUtils::extractAttribute($node, 'complId'); // 25
		$entity->attributesSCNumber = XmlUtils::extractAttribute($node, 'SCNumber'); // 15
		$entity->subjectsOpm = (string) $node->subjects['opm']; // 27ZG300Z0227838S

		return $entity;
	}

	/**
	 *
	 * @param \SimpleXMLElement $node
	 * @return OteGP6BodyEntity
	 */
	protected function getBody(
		$node)
	{
		$reading = null;

		if(isset($node->instrumentReadingC)){
			$reading = new ReadingCCMStrategy();
		}else if(isset($node->instrumentReading)){
			$reading = new ReadingABStrategy();
		}

		if($reading){
			$data = $reading->execute($node);

			$bodyEntity = new OteGP6BodyEntity();
			$bodyEntity->type = $data['type'];
			$bodyEntity->data = serialize($data);
			return $bodyEntity;
		}

		return null;
	}
}