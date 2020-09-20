<?php

namespace Tests\App\Models\Strategies\Ote;

require_once __DIR__ . '/../../../../bootstrap.php';

use App\Models\Orm\Orm;
use App\Models\Strategies\Ote\XmlCDSGasPofStrategy;
use Tester\Assert;
use Tests\Utils\IntegrationTestCase;
use App\Models\Strategies\Ote\OteXmlException;
use App\Models\Orm\OteGP6Body\OteGP6BodyEntity;

class GP6ProcessTest extends IntegrationTestCase{

	/** @var Orm */
	private $orm;

	public function __construct()
	{
		$this->orm = $this->getContainer()->getService('nextras.orm.model');
	}

	public function getFile()
	{
		return [
			[
				'gp6-ab',
				false
			],
			[
				'gp6-ccm',
				false
			],
			[
				'gp6-fail',
				true
			],
			[
				'gp6-fail2',
				true
			]
		];
	}

	/**
	 * @dataProvider getFile
	 */
	public function testXml(
		$file,
		$throw)
	{
		$xml = simplexml_load_string(file_get_contents(__DIR__ . '/src/' . $file . '.xml'));

		if($throw){
			Assert::throws(function () use (
			$xml)
			{
				XmlCDSGasPofStrategy::from($xml, $this->getContainer());
			}, OteXmlException::class);
		}else{
			Assert::type(OteGP6BodyEntity::class, $entity = XmlCDSGasPofStrategy::from($xml, $this->getContainer()));
			$entity->oteId = '-user-';
			$entity->headId->oteId = '-user-';
			$this->orm->persist($entity);
			$this->orm->faktury->getMapper()->rollback();
		}
	}
}

(new GP6ProcessTest())->run();