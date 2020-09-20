<?php

interface IImageThumbNail{

	/**
	 *
	 * @var OBE_AttachmentThumbnail $imageObj
	 * @var string $resizeType
	 * @return boolean
	 */
	static function createThumbNail(
		$imageObj,
		$thumbNailFullFileName,
		$resizeType = OBE_Attachment::RESIZE_NORMAL);
}

class OBE_AttachmentThumbnail extends OBE_Attachment{

	var $big = NULL;

	static $resizeFce2Type = [
		OBE_Attachment::T_IMAGE => 'resizeImage',
		OBE_Attachment::T_FLASH => 'resizeFlash',
		OBE_Attachment::T_OTHER => 'resizeOther',
		OBE_Attachment::T_VIDEO => 'resizeVideo'
	];

	/**
	 *
	 * @param String $fileName
	 */
	function __construct(
		$file)
	{
		parent::__construct($file);
	}

	public function createInfo(
		array $resizeTo = NULL,
		&$resizeType = OBE_Attachment::RESIZE_NORMAL)
	{
		if(parent::createInfo()){
			$this->setOutSize($resizeTo, $resizeType);
			return true;
		}
		return false;
	}

	/**
	 * Vytvori nahledový obrázek
	 * @param Array $resizeType
	 * @return Boolean
	 */
	public function createThumbNail(
		$resizeType = OBE_Attachment::RESIZE_NORMAL)
	{
		if(isset(self::$resizeFce2Type[$this->type]) && $this->src !== NULL){
			if(call_user_func([
				$this,
				self::$resizeFce2Type[$this->type]
			], $resizeType)){
				if($resizeType == OBE_Attachment::RESIZE_INSIDE_CUT){
					$this->ox = 0;
					$this->oy = 0;
				}
				return true;
			}
		}
		return false;
	}

	private function resizeImage(
		$resizeType = OBE_Attachment::RESIZE_NORMAL)
	{
		$this->big = $this->src;
		$miniatureFile = $this->getThumbFileName();
		if(!file_exists($miniatureFile)){
			$flag = NULL;
			try{
				if(extension_loaded('imagick')){
					try{
						$flag = IMImageThumbNail::createThumbNail($this, $miniatureFile, $resizeType);
					}catch(Exception $e){
						$flag = GDImageThumbNail::createThumbNail($this, $miniatureFile, $resizeType);
					}
				}else{
					$flag = GDImageThumbNail::createThumbNail($this, $miniatureFile, $resizeType);
				}
			}catch(OBE_Exception $e){
				$message = $e->getMessage();
				$this->src = '';
				$this->reason = $message;
				OBE_Log::logle('ERROR: ResizeImage: ' . $message);
				return false;
			}
			if(!$flag){
				return false;
			}
		}
		$this->src = $miniatureFile;
		return true;
	}

	public function getThumbFileName()
	{
		return 'm' . ceil($this->size[SWIDTH]) . ceil($this->size[SHEIGHT]) . '-' . $this->name . '.jpg';
	}

	private function resizeFlash()
	{
		return true;
	/**
	 * FLASH umrel
	 * flash se neresizuje, ale mel by mit parametry rozmeru nejak u sebe
	 */
	}

	private function resizeVideo()
	{
		/* video se neresizuje dela se icona */
		return true;
	}

	private function resizeOther()
	{
		/* other se neresizuje dela se icona */
		return true;
	}
}

class GDImageThumbNail implements IImageThumbNail{

	static $bmpTypeWrongError;

	/**
	 *
	 * @var OBE_AttachmentThumbnail $imageObj
	 * @var string $resizeType
	 * @return boolean
	 */
	static function createThumbNail(
		$imageObj,
		$thumbNailFullFileName,
		$resizeType = OBE_Attachment::RESIZE_NORMAL)
	{
		if($imgTmp = self::createImageTmp($imageObj)){
			switch($resizeType){
				default:
				case OBE_Attachment::RESIZE_NORMAL:
					if($resampleImg = imagecreatetruecolor($imageObj->size[SWIDTH], $imageObj->size[SHEIGHT])){
						imagecopyresampled($resampleImg, $imgTmp, 0, 0, 0, 0, $imageObj->size[SWIDTH], $imageObj->size[SHEIGHT], $imageObj->org[SWIDTH],
							$imageObj->org[SHEIGHT]);
					}
					break;
				case OBE_Attachment::RESIZE_INSIDE_CUT:
					if($resampleImg = imagecreatetruecolor($imageObj->size[SWIDTH], $imageObj->size[SHEIGHT])){
						imagecopyresampled($resampleImg, $imgTmp, 0, 0, (int) $imageObj->ox, (int) $imageObj->oy, $imageObj->size[SWIDTH],
							$imageObj->size[SHEIGHT], ($imageObj->org[SWIDTH] - (2 * $imageObj->ox)), ($imageObj->org[SHEIGHT] - (2 * $imageObj->oy)));
					}
					break;
			}
			$ret = imagejpeg($resampleImg, $thumbNailFullFileName, EnviromentConfig::$global['thumbnailQuality']);
			imagedestroy($resampleImg);
			imagedestroy($imgTmp);
		}else{
			return false;
		}
		return true;
	}

	/**
	 *
	 * @var OBE_AttachmentThumbnail $imageObj
	 * @return boolean
	 */
	static function createImageTmp(
		$imageObj)
	{
		$imgTmp = NULL;
		if(self::checkAvaibleMemory($imageObj->org, $imageObj->size) && (((OBE_Core::$memoryLimit - memory_get_usage(true)) / 10) > ($imageObj->org[SWIDTH] * $imageObj->org[SHEIGHT]))){
			set_error_handler([
				'GDImageThumbNail',
				'wrongImageTypeHandler'
			], E_ALL);
			switch($imageObj->sysType){
				case 1:
					$imgTmp = imagecreatefromgif($imageObj->src);
					break;
				case 2:
					$imgTmp = imagecreatefromjpeg($imageObj->src);
					break;
				case 3:
					$imgTmp = imagecreatefrompng($imageObj->src);
					break;
				case 6:
					$imgTmp = imagecreatefromwbmp($imageObj->src);
					break;
			}
			restore_error_handler();
			if(self::$bmpTypeWrongError){
				throw new OBE_Exception(self::$bmpTypeWrongError);
			}
		}else{
			throw new OBE_Exception(
				'Obrázek ' . $imageObj->src . ' je příliš velký pro vytvoření náhledu pomocí knihovny GD (knihovna ImageMagick na serveru buď neni instalována, nebo je aktivni direktiva safe_mode)');
		}
		return $imgTmp;
	}

	static function checkAvaibleMemory(
		$orgSize,
		$newSize)
	{
		$needMem = ($orgSize[SWIDTH] * $orgSize[SHEIGHT] * 5) + ($newSize[SWIDTH] * $newSize[SHEIGHT] * 4);
		return OBE_Core::isAvaibleMemory($needMem);
	}

	private static function wrongImageTypeHandler(
		$errno,
		$errstr,
		$errfile,
		$errline,
		$errcontext)
	{
		self::$bmpTypeWrongError = $errstr;
		return true;
	}
}

class IMImageThumbNail implements IImageThumbNail{

	/**
	 *
	 * @var OBE_AttachmentThumbnail $imageObj
	 * @return boolean
	 */
	static function createThumbNail(
		$imageObj,
		$thumbNailFullFileName,
		$resizeType = OBE_Attachment::RESIZE_NORMAL)
	{
		$image = new Imagick($imageObj->src);
		switch($resizeType){
			default:
			case OBE_Attachment::RESIZE_NORMAL:
				$image->resizeImage($imageObj->size[SWIDTH], $imageObj->size[SHEIGHT], Imagick::FILTER_LANCZOS, 1);
				break;
			case OBE_Attachment::RESIZE_INSIDE_CUT:
				$image->cropImage(($imageObj->org[SWIDTH] - (2 * $imageObj->ox)), ($imageObj->org[SHEIGHT] - (2 * $imageObj->oy)), (int) $imageObj->ox,
					(int) $imageObj->oy);
				$image->resizeImage($imageObj->size[SWIDTH], $imageObj->size[SHEIGHT], Imagick::FILTER_LANCZOS, 1);
				break;
		}
		return $image->writeImage($thumbNailFullFileName);
	}
}