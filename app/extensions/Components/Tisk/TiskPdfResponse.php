<?php

namespace App\Extensions\Components\Tisk;

use Joseki\Application\Responses\PdfResponse;
use Nette\Utils\FileSystem;

class TiskPdfResponse extends PdfResponse{

	/** @var [] */
	public $onCreatePDF = [];

	/** @var TiskComponent */
	private $tisk;

	public function __construct(
		TiskComponent $tisk)
	{
		$this->tisk = $tisk;
		parent::__construct($this->tisk->template);
	}

	public function __toString()
	{
		$this->preparePDF();
		return parent::__toString();
	}

	public function save(
		$file,
		$plonk = null)
	{
		$dir = dirname($file);
		$file = basename($file);
		FileSystem::createDir($dir);
		$this->preparePDF();
		return parent::save($dir, $file);
	}

	/**
	 * {@inheritdoc}
	 * @see \Joseki\Application\Responses\PdfResponse::send()
	 */
	public function send(
		\Nette\Http\IRequest $httpRequest,
		\Nette\Http\IResponse $httpResponse)
	{
		$this->preparePDF();
		parent::send($httpRequest, $httpResponse);
	}

	private function preparePDF()
	{
		$this->tisk->prepareTemplate();

		$this->setPageFormat($this->tisk->getFormat());

		$this->onCreatePDF($this, $this->getMPDF());

		$this->setDocumentTitle($this->tisk->getTitle());
	}

	/**
	 * {@inheritdoc}
	 * @see PdfResponse::getMPDFConfig()
	 */
	protected function getMPDFConfig()
	{
		return [
			'tempDir' => APP_DIR . '../temp/mpdf',
			'default_font_size' => 9,
			'default_font' => 'dejavusans'
		] + parent::getMPDFConfig();
	}
}