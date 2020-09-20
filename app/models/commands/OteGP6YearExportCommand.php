<?php

namespace App\Models\Commands;

use App\Extensions\Interfaces\ICommand;
use App\Models\Selections\OteGP6FakturovaneByYearSelection;
use ZipStream\ZipStream;
use App\Models\Resources\OteXmlFileResource;
use Nette\Application\Responses\FileResponse;
use Contributte\EventDispatcher\EventDispatcher;
use App\Models\Events\ResponseSendEvent;
use Nette\SmartObject;
use App\Models\Resources\OteXmlFile;

class OteGP6YearExportCommand implements ICommand{

	use SmartObject;

	/** @var EventDispatcher */
	private $dispatcher;

	/** @var OteGP6FakturovaneByYearSelection */
	private $selGp6;

	/** @var int */
	private $year;

	/** @var array */
	public $onError = [];

	public function __construct(
		EventDispatcher $dispatcher,
		OteGP6FakturovaneByYearSelection $selGp6)
	{
		$this->selGp6 = $selGp6;
		$this->dispatcher = $dispatcher;
	}

	public function setYear(
		$year)
	{
		$this->year = $year;
	}

	public function execute()
	{
		$tmpfname = tempnam(sys_get_temp_dir(), 'exp');

		ob_start();

		$gp6s = $this->selGp6->get($this->year);
		$zip = new ZipStream();

		foreach($gp6s->fetchAll() as $g){
			try{

				$file = new OteXmlFile($g->received->format('Y'), $g->ote_kod, $g->ote_id);
				if(file_exists($file)){
					$res = new \SplFileObject($file);
					$zip->addFile($res->getFilename(), $res->fread($res->getSize()));
				}else{

					// zkusit extract z mailu
				}
			}catch(\Exception $e){
				$this->onError($e->getMessage());
				continue;
			}
		}

		$zip->finish();

		$cnt = ob_get_clean();

		file_put_contents($tmpfname, $cnt);

		$res = new FileResponse($tmpfname, 'export.zip');

		$ev = new ResponseSendEvent();
		$ev->response = $res;

		$this->dispatcher->dispatch(...$ev->disp());
	}
}