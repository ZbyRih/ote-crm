<?php


use Mpdf\Mpdf;

class FakturaFile
{

	public $file;

	public $fileName;

	private $klient;

	private $eic;

	private $from;

	private $cislo;

	private $html;

	/**
	 *
	 * @param OTEFaktura $fak
	 */
	public function __construct(
		$fak = null
	) {
		if ($fak instanceof OTEFaktura) {
			$klient = MContactDetails::name($fak->klient['ContactDetails']);
			$this->cislo = $fak->cislo;
			$this->from = $fak->from;
			$this->html = $fak->getHtml();
			$this->eic = MOdberMist::identity($fak->om, false);
			$ext = 'pdf';
		} elseif ($fak) {
			$_F = new MFaktury();
			$f = $_F->FindOneById($fak);

			$klient = MContactDetails::name((new MOdberatel())->FindOneById($f[$_F->name]['klient_id'])['ContactDetails']);
			$om = (new MOdberMist())->FindOneById($f[$_F->name]['om_id']);
			$this->cislo = $f[$_F->name]['cis'];
			$this->from = new DateTime($f[$_F->name]['od']);
			$this->html = $f[$_F->name]['html'];
			$this->eic = MOdberMist::identity($om, false);
			$ext = $f[$_F->name]['ext'];
		}

		if ($fak) {
			$this->klient = str_replace([
				'/',
				'\\'
			], [
				'-',
				'-'
			], OBE_Strings::remove_diacritics($klient));
			$this->makeFile($ext);
		}
	}

	public function saveAndSendPdf(
		$saveOnly = false
	) {
		OBE_PDFExport::getPdf($this->html, ($saveOnly) ? null : $this->fileName, $this->file, [
			$this,
			'configPdf'
		]);
	}

	public function sendPdf()
	{
		OBE_PDFExport::getPdf($this->html, $this->fileName, null, [
			$this,
			'configPdf'
		]);
	}

	public function downloadFile()
	{
		OBE_Http::headForForceDownload($this->fileName, filesize($this->file));
		readfile($this->file);
		exit();
	}

	private function makeFile(
		$ext = 'pdf'
	) {
		$dir = $this->checkDir($this->from->format('Y'));

		$this->fileName = $this->formatFile($this->klient, $this->from, $this->cislo, $ext);
		$this->file = $dir . $this->fileName;
	}

	public static function getFile(
		$name,
		$date,
		$cis,
		$ext = 'pdf'
	) {
		$date = ($date instanceof DateTime) ? $date : new DateTime($date);
		$self = new static();
		$dir = $self->checkDir($date->format('Y'));
		return $dir . $self->formatFile(OBE_Strings::remove_diacritics($name), $date, $cis, $ext);
	}

	public function checkDir(
		$year
	) {
		$parms = OBE_AppCore::getAppConf('faktury');
		$dir = APP_DIR_OLD . '/' . $parms['save-dir'];

		OBE_File::checkDirectorys([
			$dir,
			$dir . '/' . $year
		]);

		return $dir . '/' . $year . '/';
	}

	public function formatFile(
		$k,
		$d,
		$c,
		$e
	) {
		return $k . '_' . $d->format('Y_md') . '_' . $c . '.' . $e;
	}

	/**
	 *
	 * @param Mpdf $mpdf
	 */
	public function configPdf(
		$mpdf
	) {
		// 		$mpdf->use_kwt = true;
		$mpdf->SetDefaultFontSize(8);
		return [
			$this->eic,
			$this->cislo
		];
	}
}
