<?php

class AttachmentCtrlClass2{
	const COL_FILE = 'filename';
	const COL_TYPE = 'filetype';

	const SAFE_MOD_INFO = 'Na hostingu je Aktivován safe_mode, proto není spolehlivé požít rozšíření IMagick, a proto bude omezen maximální rozměr obrázku z nehož bude systém schopen vytvořit náhled';
	const NONE_IMAGICK = 'Na hostingu není dostupné rozšíření IMagick. Velikost obrázku z nějž může systém vytvořit náhled bude omezena dostupnou pamětí pro běh PHP scriptu';

	var $previewSize = null;

	/**
	 * @var AttachmentCtrlClass2
	 */
	public static $self = null;

	private static $sinfo = null;
	private static $uMax = 0; // upload
	private static $pMax = 0; // post
	private static $imagick = false;
	private static $safeMode = false;

	function __construct(){
		if(!self::$self){
			self::$self = $this;
			self::init();
		}
	}

	public function setPreviewSize($previewSize){
		$this->previewSize = $previewSize;
	}

	/**
	 * odchytava uitype pro zobrazeni prilozeneho obrazku
	 *
	 * @param Integer $attachmentId
	 * @param Array $resizeTo
	 * @param Boolean $bCreateMiniaturize
	 * @param String $resizeType - RESIZE_NORMAL, RESIZE_INSIDE_CUT
	 * @return FrontAttachment
	 */
	function getView($attachmentId = null, $resizeTo = null, $bCreateMiniaturize = true, $resizeType = OBE_Attachment::RESIZE_NORMAL){
		if($resizeTo === null){
			$resizeTo = $this->previewSize;
		}

		if($attachmentId !== null || !empty($attachmentId)){
			$Model = $this->getModel();

			if($data = $Model->FindBy($Model->primaryKey, $attachmentId)){

				$data = reset($data);
				$imageObj = $this->getImageObj($data);

				$imageObj->createInfo($resizeTo, $resizeType);

				if($bCreateMiniaturize){
					$imageObj->createThumbNail($resizeType);
				}

				return $imageObj;
			}
		}

		return null;
	}

	/**
	 *
	 * @param array $array
	 * @return boolean
	 */
	function uploadFile($file){
		if(!empty($file) && $file['error'] == 0){

// 			OBE_Log::varDump($file);

			$tmp = $file['tmp_name'];
			$model = $this->getModel();

			$item = [
				$model->name => [
					  self::COL_FILE => $file['name']
					, self::COL_TYPE => $file['ext']
					, 'obtype' => $this->getTypeIndex(OBE_File::getType($file['ext']))
				]
			];

// 			OBE_Log::varDump($item);

			MEntity::preSet($item, MODULES::ATTACHMENT);

			$model->Save($item);

			$imageObj = $this->getImageObj($item);

			$file = $imageObj->getFullSrcPath();

			move_uploaded_file($tmp, $file);
			chmod($file, 0775);

			return $imageObj->id;
		}
		return null;
	}

	/**
	 *
	 * @param array $item - modelitem
	 */
	function removeFile($item){
		if($imageObj = $this->getImageObj($item)){
			$file = $imageObj->getFullSrcPath();
			if(file_exists($file)){
				unlink($file);
			}
			$this->cleanMiniaturizeDir($imageObj->id);
		}
	}

	function cleanMiniaturizeDir($fileId){
		$dir =  EnviromentConfig::$global['obe']['thumbnailsPath'];
		$hDir = opendir($dir);
		$compStr = $fileId . '_';
		while($file = readdir($hDir)){
			if(fnmatch($compStr . '*', $file)){
				unlink($dir . $file);
			}
		}
	}

	/**
	 *
	 * @param Array $data
	 * @param ModelFormClass2 $editFormObj
	 * @param String $lastSavedFile
	 */
	function fileUploadPrepare($data, $editFormObj, &$lastSavedFile){
		$attchModel = $this->getModel();

		if($this->isUploadedFileInModelData($data)){
			$lastSavedFile = $this->getCachedFileData($data);

			if($editFormObj->recordId !== null){
				$item = $attchModel->FindOneById($editFormObj->recordId);
				$this->removeFile($item[$attchModel->name]);
				$this->cleanMiniaturizeDir($editFormObj->recordId);
			}
			$lastSavedFile = OBE_File::sanitizeUpload($lastSavedFile);
			$data = $this->setModelDataByCorrectName($data, $lastSavedFile);
		}else{
			$data = $this->removeFromModelData($data);
		}
		return $data;
	}

	/**
	 * @return ModelClass
	 */
	function getModel(){
		$modelObj = new MAttachment();
		return $modelObj;
	}

	/**
	 *
	 * @param Array $item
	 * @return FrontAttachment
	 */
	function getImageObj($item){
		$Model = $this->getModel();
		if(isset($item[$Model->name])){
			return new FrontAttachment($item[$Model->name]);
		}
		return null;
	}

	function getTypeIndex($type){
		$fOBT = array_flip(OBE_File::$types_list);
		if(isset($fOBT[$type])){
			return $fOBT[$type];
		}
		return null;
	}

	private static function init(){
		if(!self::$uMax){
			self::$uMax = OBE_Math::getFormatIniLimits(ini_get('upload_max_filesize'));
			self::$pMax = OBE_Math::getFormatIniLimits(ini_get('post_max_size'));
			self::$imagick = extension_loaded('imagick');
			self::$safeMode = ini_get('safe_mode');

			if(self::$imagick && self::$safeMode){
				self::$sinfo[] = self::SAFE_MOD_INFO;
			}else{
				self::$sinfo[] = self::NONE_IMAGICK;
			}

			if(!empty(self::$sinfo)){
				self::$sinfo[] = 'Systém může vytvořit náhled z obrázku o přibližné maximální velikosti <b>' . OBE_Math::getFormatedBytes((OBE_Core::$memoryLimit - memory_get_usage(true)) / 10) . 'px</b>';
			}

			self::$sinfo = ['Maximální velikost uploadovaného souboru : <b>' . OBE_Math::getFormatedBytes((self::$uMax > self::$pMax)?self::$pMax : self::$uMax) . 'B</b>'] + self::$sinfo;
		}
	}

	public function append($a){
		return ($a + [
			  'maxSize' => self::$pMax
			, 'info' => self::$sinfo
		]);
	}
}