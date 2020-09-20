<?php
namespace App\Extensions\App;

use App\Extensions\Utils\Html;
use App\Extensions\Utils\Strings;
use App\Extensions\Utils\Helpers\Image;
use Nette\FileNotFoundException;
use Nette\Utils\FileSystem;

class UnsupportedFileType extends \Exception{
}

class WebPackManifest{

	private static $manifests;

	public static function script(
		$basePath,
		$files)
	{
		$realFiles = [];
		foreach($files as $f){
			if($rf = self::checkManifestForFile($f)){
				$realFiles[] = $rf;
			}
		}
		$out = '';
		foreach($realFiles as $f){
			if(Strings::endsWith($f, '.js')){
				$out .= '<script src="' . rtrim($basePath, '/') . $f . '" type="text/javascript"></script>' . PHP_EOL;
			}else if(Strings::endsWith($f, '.css')){
				$out .= '<link href="' . rtrim($basePath, '/') . $f . '" rel="stylesheet" type="text/css" media="all" />' . PHP_EOL;
			}else{
				throw new UnsupportedFileType($f);
			}
		}
		return $out;
	}

	public static function imageBase64(
		$basePath,
		$params)
	{
		$file = array_shift($params);
		if($rf = self::checkManifestForFile($file)){

			$srcFile = WWW_DIR . $rf;

			$fFile = FileSystem::read($srcFile);
			$ext = pathinfo($srcFile, PATHINFO_EXTENSION);
			if($ext == 'png'){
				return Image::fromString($fFile)->toBase64();
			}
		}else{
			throw new FileNotFoundException($file);
		}
	}

	public static function image(
		$basePath,
		$params)
	{
		$file = array_shift($params);
		if($rf = self::checkManifestForFile($file)){

			$ii = getimagesize(WWW_DIR . $rf);

			return Html::el('img')->addAttributes([
				'src' => rtrim($basePath, '/') . $rf
			] + $params + [
				'width' => $ii[0] . 'px',
				'height' => $ii[1] . 'px'
			]);
		}else{
			throw new FileNotFoundException($file);
		}
	}

	public static function file(
		$basePath,
		$params)
	{
		$file = array_shift($params);
		if($rf = self::checkManifestForFile($file)){
			return rtrim($basePath, '/') . $rf;
		}else{
			throw new FileNotFoundException($file);
		}
	}

	protected static function checkManifestForFile(
		$f)
	{
		$pi = pathinfo($f);
		$rp = $pi['dirname'];
		$fn = basename($f);

		$manf = WWW_DIR . $rp . '/manifest.json';
		if(isset(self::$manifests[$manf])){
			$outs = self::$manifests[$manf];
		}else{
			if($rp && $fn && file_exists($manf)){
				$outs = self::$manifests[$manf] = json_decode(file_get_contents($manf), true);
			}else{
				throw new FileNotFoundException($manf);
			}
		}

		if(array_key_exists($fn, $outs)){
			return $rp . '/' . $outs[$fn];
		}else{
			throw new FileNotFoundException($fn);
		}
	}
}