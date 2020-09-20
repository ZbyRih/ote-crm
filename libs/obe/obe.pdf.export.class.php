<?php


// use Mpdf;
class DownloadResponseException extends Exception{
}

class OBE_PDFExport{

	private static $init = false;

	public static function export(
		$html,
		$filename,
		$bPreview = false,
		$callback = null)
	{
		if($bPreview){
			return $html;
		}

		$dompdf = self::create($html, $callback);

		$dompdf->Output($filename, 'D');
		throw new DownloadResponseException();
	}

	public static function getPdf(
		$html,
		$filename = null,
		$fileToSave = null,
		$callback = null)
	{
		$dompdf = self::create($html, $callback);

		if($fileToSave){
			$dompdf->Output($fileToSave, 'F');
		}

		if($filename){
			$dompdf->Output($filename, 'D');
			throw new DownloadResponseException();
		}
	}

	public static function create(
		$html,
		$callback)
	{
		$dompdf = new Mpdf\Mpdf([
			'default_font_size' => 8,
			'default_font' => 'dejavusans'
		]);

		$title = self::config($callback, $dompdf);

		$titleLeft = '';
		$titleMid = '';

		if($title){
			list($titleLeft, $titleMid) = $title;
		}

		$dompdf->showImageErrors = true;
		$dompdf->setAutoTopMargin = 'stretch';

		$dompdf->SetHeader($titleLeft . '|' . $titleMid . '|stránka {PAGENO}/{nbpg}');

		$dompdf->WriteHTML($html);

		return $dompdf;
	}

	public static function config(
		$calback,
		$mpdf)
	{
		if(is_callable($calback)){
			return call_user_func($calback, $mpdf);
		}
	}
}