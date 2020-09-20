<?php

namespace App\Modules\OteZpravy;

use App\Extensions\Components\BaseComponent;
use App\Extensions\Components\IFlashMessageConstants;
use App\Models\Strategies\Ote\OteXmlException;
use App\Models\Strategies\Ote\XmlProcessorStrategy;
use App\Models\Orm\Orm;
use Nette\DI\Container;
use App\Components\Controls\MAUploadControl;

class UploadForm extends BaseComponent{

	/** @var Orm */
	private $orm;

	/** @var Container */
	private $container;

	/** @var [] */
	public $onSuccess = [];

	/** @var [] */
	public $onError = [];

	public function __construct(
		Orm $orm,
		Container $container)
	{
		$this->orm = $orm;
		$this->container = $container;
	}

	public function createComponentForm()
	{
		$f = $this->createForm();

		$input = new MAUploadControl('XML soubor se zprávou');
		$input->setRequired();
		$f->addComponent($input, 'file');
		$f->addSubmit('upload', 'Nahrát');

		$f->onSuccess[] = [
			$this,
			'onSuccessSubmit'
		];

		return $f;
	}

	public function onSuccessSubmit(
		$form,
		$vs)
	{
		if(!$cnt = $vs->file->getContents()){
			$this->onError('Soubor je prázdný', IFlashMessageConstants::FLASH_WARNING);
			return;
		}

		try{
			if(!$entity = XmlProcessorStrategy::from($cnt, $this->container)){
				$this->onError('XML se nepodařilo zpracovat', IFlashMessageConstants::FLASH_WARNING);
				return;
			}
		}catch(OteXmlException $e){
			$this->onError($e->getMessage(), IFlashMessageConstants::FLASH_WARNING);
			return;
		}

		$this->orm->persist($entity);

		$this->onSuccess('XML uloženo a zpracováno');
	}
}