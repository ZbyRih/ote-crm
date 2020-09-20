<?php

namespace App\Extensions\Utils;

use Nette\Utils\Html as NHtml;

class Html extends NHtml{

	public static function removeByName($html, $name){
		if($html instanceof \Nette\Utils\Html){
			foreach($html as $i => $sub){
				if($sub instanceof \Nette\Utils\Html && $sub->getName() == $name){
					unset($html[$i]);
				}else{
					self::removeByName($sub, $name);
				}
			}
		}
		return $html;
	}

	public static function arrToSpan($a){
		$e = Html::el('span');
		array_walk($a, function ($v) use ($e){
			if(!$v){
				return;
			}
			$e->addText($v)->addHtml('&nbsp');
		});
		return $e;
	}
}