<?php

class OBE_FileException extends OBE_Exception{

}

class OBE_File{
	var $file = '';
	var $name = '';
	var $ext = '';
	var $src = '';
	var $type = self::T_OTHER;

	const T_IMAGE = 'image';
	const T_FLASH = 'flash';
	const T_VIDEO = 'video';
	const T_OTHER = 'other';

	static $types_list = [self::T_IMAGE, self::T_FLASH, self::T_VIDEO, self::T_OTHER];

	static $types = [
		  self::T_IMAGE => ['jpg', 'jpeg', 'gif', 'png', 'bmp']
		, self::T_FLASH => ['swf']
		, self::T_VIDEO => ['flv', 'mkv', 'wmv', 'mp4']
	];

	public function __construct($file){
		$this->file = $file;
	}

	public function getFullSrcPath(){
		return $this->file;
	}

	public function getHttp(){
		return OBE_Core::$configEnviroment['url'] . $this->file;
	}

	public static function getExt($name){
		$elements = explode('.', $name);
		return end($elements);
	}

	public static function getType($ext){
		foreach(self::$types as $type => $exts){
			if(in_array($ext, $exts)){
				return $type;
			}
		}
		return self::T_OTHER;
	}

	public function getBase64Content(){
		$content = file_get_contents($this->src);
		return base64_encode($content);
	}

	public function isExist(){
		return file_exists($this->src);
	}

	public static function sanitizeUpload($upload){
		if(!empty($upload)){
			if(isset($upload['error']) && $upload['error'] != 0){
				return null;
			}
			$name = urldecode($upload['name']);

			$exts = explode('.', self::normalizeFile($name));
			$ext = array_pop($exts);

			$clearName = substr(implode('.', $exts), 0, 255 - (strlen($ext) + 1)) . '.' . $ext;

			$upload['name'] = $clearName;
			$upload['ext'] = $ext;
		}
		return $upload;
	}

	public static function moveUpload($upload, $targetDir){
		if(!empty($upload)){
			move_uploaded_file($upload['tmp_name'], $targetDir . $upload['name']);
		}
		return false;
	}

	public static function getInfo($file){
		if(file_exists($file)){
			$size = filesize($file);
			$file = explode('/', $file);
			return [
				  'name' => end($file)
				, 'size' => OBE_Math::getFormatedBytes($size, 2) . 'B'
				, 'type' => 'info'
			];
		}
		return ['name' => 'missing.', 'size' => '', 'type' => 'danger'];
	}

	/**
	 * @param array $dirs - '/dir/dir'
	 * @param string|null $path
	 * @throws OBE_Exception
	 */
	public static function checkDirectorys($dirs, $path = null){
		$dirs = MArray::AllwaysArray($dirs);
		$path = ($path)? $path : '';
		foreach($dirs as $d){
			$full = self::normalizeDir($path . $d);

			if(!file_exists($full)){
				$parent = substr($full, 0, strrpos($full, '/', -2));

				if(!is_dir($parent) && !empty($parent)){
					self::checkDirectorys($parent, false);
				}

				if(mkdir($full)){
					if(!chmod($full, 0775) || !is_writable($full)){
						throw new OBE_FileException('Adresáři \'' . $full . '\' se nepodařilo nastavit práva');
					}
				}else{
					throw new OBE_FileException('Adresář \'' . $full . '\' se nepodařilo vytvořit');
				}
			}
		}
	}

	public static function normalizeDirs($dirs){
		$dirs = MArray::AllwaysArray($dirs);
		foreach($dirs as $k => $d){
			$dirs[$k] = self::normalizeDir($d);
		}
		return $dirs;
	}

	public static function normalizeDir($dir){
		return str_replace('\\', '/', $dir);
	}

	public static function normalizeFile($file){
		$file = OBE_Strings::remove_diacritics($file);
		$file = str_replace([' ', '_'], '-', trim($file));
		$file = preg_replace('/[^\.\-a-z0-9]+/i', '-', $file);
		$file = preg_replace('/-+/', '-', $file);
		return preg_replace('/\.+/', '.', trim($file, '-'));
	}
}