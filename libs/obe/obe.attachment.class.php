<?php

define('WIDTH', 0);
define('HEIGHT', 1);
define('RESIZE_TYPE', 2);

define('SWIDTH', 'w');
define('SHEIGHT', 'h');
define('ICO_EXT', '.png');

class OBE_AttachmentInfo extends OBE_File{
	var $info = null;
	var $size = null;//array('w' => x, 'h' => y)
	var $fsize = 0;
	var $ico = '';
	var $id = null;
	var $ox = 0;
	var $oy = 0;
	var $org = null;//array('w' => x, 'h' => y)
	var $mime = null;
	var $reason = null;
}

class OBE_Attachment extends OBE_AttachmentInfo{
	const RESIZE_NORMAL = 'normal';
	const RESIZE_INSIDE_CUT = 'in_c';

	static $types_get_func = [
		  self::T_IMAGE => 'imageGetFunc'
		, self::T_FLASH => 'flashGetFunc'
		, self::T_VIDEO => 'videoGetFunc'
		, self::T_OTHER => 'otherGetFunc'
	];

	static $exts2ico = [
		  'xls' => 'xls'
		, 'ods' => 'xls'
		, 'doc' => 'doc'
		, 'odf' => 'doc'
		, 'pdf' => 'pdf'
		, 'zip' => 'rar'
		, 'rar' => 'rar'
		, 'tar' => 'rar'
		, 'gz' => 'rar'
	];

	static $callByType = [
		  self::RESIZE_NORMAL => 'oneSidePut'
		, self::RESIZE_INSIDE_CUT => 'cutFromInside'
	];

	static $dontUpScale = false;
	static $icoRealPath = LIBS_DIR . '/../';

	function __construct($file){
		parent::__construct($file);
	}

	public function createInfo(){
		$this->name = preg_replace('~^([\w\W^(/\\\\)]+(/|\\\\))+~i', '', $this->file);
		if(preg_match('~^([\w\W]*)\.([\w\W]+)$~i', $this->name, $regs)){
			$this->name = $regs[1];
		}
		$this->ext = strtolower(self::getExt($this->file));
		$this->type = self::getType($this->ext);
		$this->src = $src = $this->getFullSrcPath();

		if($this->isExist()){
			$this->generateFileInfo();
			return true;
		}else{
			$this->type = null;
			$this->src = null;
		}
		OBE_Log::loglw('Požadovaný soubor neexistuje "' . $src . '" in OBE_Attachment::createInfo');
		return false;
	}

	public function setOutSize(array $resizeTo = null, $resizeType = OBE_Attachment::RESIZE_NORMAL){
		$resizeTo = $this->checkResize($resizeTo);
		if($resizeTo !== null && $this->type == 'image'){
			if(isset($resizeTo[RESIZE_TYPE])){
				$resizeType = $resizeTo[RESIZE_TYPE];
			}
			$this->org = $this->size;
			list($size, $offset) = call_user_func(['OBE_Attachment', self::$callByType[$resizeType]], [$this->size[SWIDTH], $this->size[SHEIGHT]], $resizeTo);
			$this->size[SWIDTH] = $size[WIDTH];
			$this->size[SHEIGHT] = $size[HEIGHT];
			$this->ox = $offset[WIDTH];
			$this->oy = $offset[HEIGHT];
		}
	}

	public function checkResize($resizeTo){
		if(is_array($resizeTo)){
			if((is_numeric($resizeTo[0]) || $resizeTo[0] === null) && (is_numeric($resizeTo[1]) || $resizeTo[1] === null)){
				if($resizeTo[0] > 0 || $resizeTo[1] > 0){
					return $resizeTo;
				}
			}
		}
		return null;
	}

	private function generateFileInfo(){
		if(isset(self::$types_get_func[$this->type])){
			$this->{self::$types_get_func[$this->type]}();
		}
	}

	private function imageGetFunc(){
		if($this->fsize = filesize($this->src)){
			$info = getimagesize($this->src);
			$this->size = [
				  SWIDTH => $info[0]
				, SHEIGHT => $info[1]
			];
			$this->sysType = $info[2];
			$this->mime = $info['mime'];
		}
	}

	private function flashGetFunc(){
		$this->fsize = filesize($this->src);
		if(!empty($this->info)){
			$this->info = unserialize($this->info);
			$this->size = [
				  SWIDTH => $info['w']
				, SHEIGHT => $info['h']
			];
		}else{
			$this->info = null;
		}
	}

	private function videoGetFunc(){
		$this->fsize = filesize($this->src);
		if(!empty($this->info)){
			$this->info = unserialize($this->info);
			$this->size = [
				  SWIDTH => $this->info['w']
				, SHEIGHT => $this->info['h']
			];
		}else{
			$this->info = null;
		}
	}

	private function otherGetFunc(){
		$this->fsize = filesize($this->src);
		if(isset(self::$exts2ico[$this->ext])){
			$icoFile = OBE_AppCore::getAppConf('ico_size', true) . EnviromentConfig::$global['icons'] . self::$exts2ico[$this->ext] . ICO_EXT;
			$realIcoPath = self::$icoRealPath . $icoFile;
			if(file_exists($realIcoPath)){
				$this->ico = $icoFile;
			}
		}
	}

	static function cutFromInside($source, $target){
		$ps = $source[WIDTH] / $source[HEIGHT];//pomer sirky k vejsce
		$pt = $target[WIDTH] / $target[HEIGHT];//pomer sirky k vejsce
		$offset = [WIDTH => 0, HEIGHT => 0];
		if($ps < $pt){
			if($ps < 1){
				$offset[HEIGHT] = (int)round(($source[HEIGHT] - ($source[WIDTH] / $pt)) / 2);
			}else{
				$offset[HEIGHT] = (int)round(($source[HEIGHT] - ($source[WIDTH] / $pt)) / 2);
			}
		}else{
			if($ps < 1){
				$offset[WIDTH] = (int)round(($source[WIDTH] - ($source[HEIGHT] * $pt)) / 2);
			}else{
				$offset[WIDTH] = (int)round(($source[WIDTH] - ($source[HEIGHT] * $pt)) / 2);
			}
		}
		return [$target, $offset];
	}

	static function oneSidePut($source, $target){
		if(empty($source[HEIGHT])){
			return [0, 0];
		}
		$pomer = $source[WIDTH] / $source[HEIGHT];//pomer sirky k vejsce
		if(empty($target) || (empty($target[WIDTH]) && empty($target[HEIGHT]))){
			return [0, 0];
		}elseif(empty($target[WIDTH])){
			$target[WIDTH] = (int)round($target[HEIGHT] * $pomer);
		}elseif(empty($target[HEIGHT])){
			$target[HEIGHT] = (int)round($target[WIDTH] * (1 / $pomer));
		}
		return self::rect2rect($source, $target);
	}

	static function rect2rect($source, $target){
		$pomer = $source[WIDTH] / $source[HEIGHT];//pomer sirky k vejsce
		$offset = [0, 0];
		$smin = false;
		if(self::$dontUpScale){
			$smin = ($source[WIDTH] < $target[WIDTH] && $source[HEIGHT] < $target[HEIGHT])? true: false;
		}
		if($pomer < ($target[WIDTH] / $target[HEIGHT])){//kdyz je vyssi nez sirsi
			if(!($smin && self::$dontUpScale)){
				$dif = $source[HEIGHT] - $target[HEIGHT];
				$source[WIDTH] -= (int)round($dif * $pomer);
				$source[HEIGHT] -= $dif;
			}
			$offset[WIDTH] = round(($target[WIDTH] - $source[WIDTH]) / 2);
			$offset[HEIGHT] = round(($target[HEIGHT] - $source[HEIGHT]) / 2);
		}else{// kdyz je sirsi nez vyssi
			if(!($smin && self::$dontUpScale)){
				$dif = $source[WIDTH] - $target[WIDTH];
				$source[WIDTH] -= $dif;
				$source[HEIGHT] -= (int)round($dif * (1 / $pomer));
			}
			$offset[WIDTH] = round(($target[WIDTH] - $source[WIDTH]) / 2);
			$offset[HEIGHT] = round(($target[HEIGHT] - $source[HEIGHT]) / 2);
		}
		return [$source, $offset];
	}

	public function GetBase64ContentMimePrefix(){
		return 'data:'. $this->mime . ';base64,' . $this->getBase64Content();
	}
}

OBE_Attachment::$dontUpScale = OBE_AppCore::getAppConf('images_dont_upscale');
OBE_Attachment::$icoRealPath = OBE_AppCore::getAppConf('ico_real_path', true);