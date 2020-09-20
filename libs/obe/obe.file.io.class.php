<?php

class OBE_FileIO{

	/**
	 *
	 * @var string
	 */
	protected $name = null;

	/**
	 *
	 * @var int
	 */
	protected $handle = null;

	/**
	 *
	 * @var string
	 */
	private $buff = '';

	/**
	 *
	 * @var string
	 */
	private $mode = 'write';

	/**
	 *
	 * @var string
	 */
	private $open = 'w+';

	public function __construct(
		$name,
		$mode = 'write')
	{
		$this->name = $name;
		$this->mode = $mode;
	}

	public function __destruct()
	{
		$this->flush();
		$this->close();
	}

	public function open(
		$mode = 'w+')
	{
		if($this->name){
			$this->handle = fopen($this->name, $mode);
			$this->open = $mode;
		}
	}

	public function close()
	{
		if($this->handle){
			fclose($this->handle);
			$this->handle = null;
			$this->buff = '';
		}
	}

	public function flush()
	{
		if($this->name){
			if(!empty($this->buff)){
				if(!$this->handle){
					$this->open($this->open);
				}
				$this->write($this->buff);
			}
			$this->buff = '';
		}
	}

	public function unlink()
	{
		if($this->name){
			if($this->handle){
				$this->close();
			}
			@unlink($this->name);
		}
	}

	public function write(
		$data)
	{
		if($this->handle){
			fwrite($this->handle, $data);
		}else{
			$this->buff .= $data;
		}
	}

	public function lines(
		$data,
		$lineSeparator = '')
	{
		$this->buff .= (is_array($data) ? (implode($lineSeparator, $data) . $lineSeparator) : ($data . $lineSeparator));
		if($this->handle){
			fwrite($this->handle, $this->buff);
			$this->buff = '';
		}
	}

	public function getName()
	{
		return $this->name;
	}

	public function getContent()
	{
		if($this->handle){
			$pos = ftell($this->handle);
			rewind($this->handle);
			$cnt = fread($this->handle, $pos + 1);
			fseek($this->handle, $pos);
			return $cnt;
		}else if(($this->mode == 'buff' && !empty($this->buff)) || $this->name === null){
			return $this->buff;
		}else if($this->name){
			return file_get_contents($this->name);
		}
		return null;
	}

	public function putContent(
		$content)
	{
		if($this->name){
			return file_put_contents($this->name, $content);
		}
	}

	public function getSize()
	{
		if($this->name){
			return filesize($this->name);
		}
	}

	public function output()
	{
		if($this->name){
			readfile($this->name);
		}
	}

	public function copyWithTimeMark()
	{
		if($this->name){
			$timeMark = date('Y-m-d--H-i-s');
			list($fileName, $ext) = $this->getNameAndExt();
			$fileNameWTimeMark = $fileName . '_' . $timeMark . '.' . $ext;
			copy($this->name, $fileNameWTimeMark);
		}
	}

	public function getNameAndExt()
	{
		return self::fileNameToNameAndExt($this->name);
	}

	public static function fileNameToNameAndExt(
		$fileName)
	{
		$elements = explode('.', $fileName);
		$ext = ((count($elements) > 1) ? array_pop($elements) : '');
		$outName = implode('.', $elements);
		return [
			$outName,
			$ext
		];
	}

	public static function getTemp()
	{
		if(!OBE_Core::$safeMode){
			return tempnam('tmp', 'exp');
		}else{
			return tempnam('tmp', 'exp');
		}
	}
}