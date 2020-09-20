<?php


/**
 * trida pro vytvoreni xml domu

 *
 */
class OBE_XmlObjectClass{

	private static $trans_tbl = null;

	var $xml = '';

	var $indent = ' ';

	var $terminate = "\r\n";

	var $stack = [];

	/**
	 * konstruktor, vytvori kontent
	 * @param string $indent
	 */
	function __construct(
		$indent = '  ',
		$xml_head = '')
	{
		if(!self::$trans_tbl){
			self::initialize_trans_table();
		}
		$this->indent = $indent;
		$this->xml = $xml_head . ((empty($xml_head)) ? '' : $this->terminate);
	}

	/**
	 * vraci vygenerovane xml
	 * @return string
	 */
	public function getXml()
	{
		return $this->xml;
	}

	/**
	 * echo
	 */
	public function out()
	{
		echo $this->xml;
	}

	/**
	 * Otevira element
	 * @param string $element
	 * @param [] $attributes
	 */
	public function push(
		$element,
		$attributes = [])
	{
		$this->_indent();
		$this->xml .= '<' . $element;
		$this->xml .= $this->compileAtrributes($attributes);
		$this->xml .= ">" . $this->terminate;
		$this->stack[] = $element;
		return $this;
	}

	/**
	 * vlozi inline element obalujici nejaky obsah
	 * @param string $element
	 * @param string $content
	 * @param array $attributes - key => val
	 */
	public function elementCD(
		$element,
		$content,
		$attributes = [])
	{
		$this->_indent();
		$this->xml .= '<' . $element;
		$this->xml .= $this->compileAtrributes($attributes);
		$this->xml .= '><![CDATA[' . $content . ']]></' . $element . '>' . $this->terminate;
		return $this;
	}

	/**
	 * Prazdny element ???
	 * @param string $element
	 * @param array $attributes - key => val
	 */
	public function shortElement(
		$element,
		$attributes = [])
	{
		$this->_indent();
		$this->xml .= '<' . $element;
		$this->xml .= $this->compileAtrributes($attributes);
		$this->xml .= " />" . $this->terminate;
		return $this;
	}

	public function elementNotEmpty(
		$element,
		$data)
	{
		if(!empty($data)){
			$this->_indent();
			$this->xml .= '<' . $element . '>';
			$this->xml .= $data;
			$this->xml .= '</' . $element . '>' . $this->terminate;
		}
		return $this;
	}

	/**
	 *
	 * @param String $element
	 * @param String $data
	 * @param Integer $cutLenght
	 */
	public function elementStripAndCut(
		$element,
		$data,
		$cutLenght = null)
	{
		if($cutLenght){
			$this->element($element, mb_ereg_replace(' {1,}|\r\n', ' ', trim(mb_substr(strip_tags($data), 0, $cutLenght))));
		}else{
			$this->element($element, mb_ereg_replace(' {1,}|\r\n', ' ', trim(strip_tags($data))));
		}
		return $this;
	}

	/**
	 *
	 * @param String $element
	 * @param String $data
	 * @param Integer $cutLenght
	 */
	public function elementCut(
		$element,
		$data,
		$cutLenght = null)
	{
		if($cutLenght){
			$this->element($element, mb_substr($data, 0, $cutLenght));
		}else{
			$this->element($element, trim($data));
		}
		return $this;
	}

	/**
	 * vlozi inline element obalujici nejaky obsah
	 * @param string $element
	 * @param string $content
	 * @param array $attributes - key => val
	 */
	public function element(
		$element,
		$content,
		$attributes = [])
	{
		$this->_indent();
		$this->xml .= '<' . $element;
		$this->xml .= $this->compileAtrributes($attributes);
		$this->xml .= '>' . self::unhtmlentitiesUtf8($content) . '</' . $element . '>' . $this->terminate;
		return $this;
	}

	/**
	 * opousti vnoreny element vytvoreny pres push
	 */
	public function pop()
	{
		$element = array_pop($this->stack);
		$this->_indent();
		$this->xml .= "</$element>" . $this->terminate;
		return $this;
	}

	/**
	 * Odsazeni
	 */
	private function _indent()
	{
		for($i = 0, $j = count($this->stack); $i < $j; $i++){
			$this->xml .= $this->indent;
		}
	}

	/**
	 * Slozi atributy z pole kde klic je nazev atributu a hodnota je jeho hodnota
	 * @param Array $attributes
	 * @return String
	 */
	private function compileAtrributes(
		$attributes = [])
	{
		$atrib = '';
		if(is_array($attributes) && !empty($attributes)){
			foreach($attributes as $key => $value){
				$atrib .= ' ' . $key . '="' . $value . '"';
			}
		}
		return $atrib;
	}

	static function unhtmlentitiesUtf8(
		$string)
	{
		if(!preg_match('/^<\!\[CDATA\[.*\]\]>$/', $string)){
			$string = strtr($string, self::$trans_tbl);
			$string = str_replace('<', '&lt;', $string);
			$string = str_replace('>', '&gt;', $string);
		}
		return $string;
	}

	static function initialize_trans_table()
	{
		$trans_tbl = get_html_translation_table(HTML_ENTITIES, null, 'UTF-8');
		$trans_tbl = array_flip($trans_tbl);
//		foreach($trans_tbl as $key => $value ) {
//			$trans_tbl[$key] = iconv('ISO-8859-1', 'UTF-8', $value);
//		}
		$trans_tbl['&euro;'] = '€';
		$trans_tbl['&scaron;'] = 'š';
		$trans_tbl['&ndash;'] = '-';
		$trans_tbl['&bdquo;'] = '„';
		$trans_tbl['&ldquo;'] = '“';
		$trans_tbl['&rdquo;'] = '”';
		$trans_tbl['&'] = '&amp;';
		unset($trans_tbl['&lt;']);
		unset($trans_tbl['&gt;']);
		unset($trans_tbl['&amp;']);
		self::$trans_tbl = $trans_tbl;
	}
}

/**
 * xml objekt pro nastaveni hlavicek a poslani xml na bufer nebo zapsani do souboru

 *
 */
class OBE_XmlWriterClass extends OBE_XmlObjectClass{

	/**
	 * konstruktor, vytvori kontent s xml hlavickou
	 * @param String $indent
	 * @param String $xml_head
	 */
	function __construct(
		$indent = '  ',
		$xml_head = '')
	{
		if(empty($xml_head)){
			$xml_head = '<?xml version="1.0" encoding="utf-8"?>';
		}
		parent::__construct($indent, $xml_head);
	}

	function headInitialForceDownload(
		$filename,
		$contentType = 'application/force-download')
	{
		OBE_http::headForForceDownload($filename, mb_strlen($this->xml, '8bit'), $contentType);
		$this->out();
		OBE_Http::flushAndClose();
	}

	function headInitialFileWMimeType(
		$filename = NULL,
		$contentType = 'text/xml; charset=utf-8')
	{
		OBE_Http::headForFileWMimeType($filename, mb_strlen($this->xml, '8bit'), $contentType);
		$this->out();
		OBE_Http::flushAndClose();
	}
}
