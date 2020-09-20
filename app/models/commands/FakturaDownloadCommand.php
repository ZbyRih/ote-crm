<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\Orm\Orm;
use App\Models\Orm\Faktury\FakturaEntity;
use App\Models\Strategies\Fakturace\CreateFileInfoFakturaStrategy;
use Mpdf\Mpdf;
use App\Models\Strategies\Fakturace\CreateHtmlFakturaStrategy;
use App\Models\Repositories\ParametersRepository;
use Contributte\EventDispatcher\EventDispatcher;
use Nette\Application\Responses\FileResponse;
use App\Models\Events\ResponseSendEvent;

class FakturaDownloadCommand implements ICommand{

	/** @var Orm */
	private $orm;

	/** @var ParametersRepository */
	private $params;

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var FakturaEntity */
	private $fa;

	public function __construct(
		Orm $orm,
		EventDispatcher $dispatcher,
		ParametersRepository $params)
	{
		$this->orm = $orm;
		$this->params = $params;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param FakturaEntity $fa
	 */
	public function setFa(
		$fa)
	{
		$this->fa = $fa;
	}

	public function execute()
	{
		$str = new CreateFileInfoFakturaStrategy($this->orm, $this->params);
		$fif = $str->create($this->fa);

		if($this->fa->storno){
			$strHtml = new CreateHtmlFakturaStrategy();
			$html = $strHtml->get($this->fa);

			$dompdf = new Mpdf([
				'default_font_size' => 8,
				'default_font' => 'dejavusans'
			]);
			$dompdf->SetDefaultFontSize(8);
			$dompdf->showImageErrors = true;
			$dompdf->setAutoTopMargin = 'stretch';

			$dompdf->SetHeader($fif->eic . '|' . $fif->cislo . '|stránka {PAGENO}/{nbpg}');
			$dompdf->WriteHTML($html);

			$dompdf->Output($fif->name, 'D');
		}else{
			$ev = new ResponseSendEvent();
			$ev->response = new FileResponse($fif->file, $fif->name);
			$this->dispatcher->dispatch(...$ev->disp());
		}
	}
}