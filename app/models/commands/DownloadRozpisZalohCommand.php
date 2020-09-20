<?php

namespace App\Models\Commands;

use App\Components\ITiskRozpisZaloh;
use App\Extensions\ITiskComponent;
use App\Models\DTO\TiskRospisZalohDTO;
use App\Models\Strategies\ICreateRozpisZalohEntityStrategy;
use App\Extensions\Components\Tisk\TiskPdfResponse;
use App\Models\Events\ResponseSendEvent;
use Contributte\EventDispatcher\EventDispatcher;
use Mpdf\Mpdf;

class DownloadRozpisZalohCommand{

	/** @var ICreateRozpisZalohEntityStrategy @inject */
	private $facRozpisEntity;

	/** @var ITiskRozpisZaloh @inject */
	private $facRozpis;

	/** @var ITiskComponent @inject */
	private $facTisk;

	/** @var TiskRospisZalohDTO */
	private $params;

	public function __construct(
		ITiskComponent $facTisk,
		EventDispatcher $dispatcher,
		ITiskRozpisZaloh $facRozpis,
		ICreateRozpisZalohEntityStrategy $facRozpisEntity)
	{
		$this->facTisk = $facTisk;
		$this->facRozpis = $facRozpis;
		$this->dispatcher = $dispatcher;
		$this->facRozpisEntity = $facRozpisEntity;
	}

	/**
	 * @param TiskRospisZalohDTO $params
	 */
	public function setParams(
		TiskRospisZalohDTO $params)
	{
		$this->params = $params;
	}

	public function execute()
	{
		$str = $this->facRozpisEntity->create();
		$str->setParams($this->params);
		$rozpis = $str->create();

		$page = $this->facRozpis->create();
		$page->setZalohy($rozpis);

		$tisk = $this->facTisk->create();
		$tisk->setTitle($rozpis->getFileName());
		$tisk->addPage($page);

		$resp = new TiskPdfResponse($tisk);
		$resp->onCreatePDF[] = function (
			TiskPdfResponse $resp,
			Mpdf $mpdf)
		{
			$mpdf->SetHeader('||stránka {PAGENO}/{nbpg}');
		};

		$ev = new ResponseSendEvent();
		$ev->response = $resp;

		$this->dispatcher->dispatch(ResponseSendEvent::NAME, $ev);
	}
}