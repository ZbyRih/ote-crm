<?php


class FakturaPreview{

	public static function make($html){
		return ViewsFactory::createHtml('<div><style>#faktura *{font-size: 14px !important;}</style>' . $html . '</div>');
	}
}