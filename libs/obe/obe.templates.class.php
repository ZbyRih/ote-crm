<?php

class OBE_Template{

	public $id;

	public $content;

	public $subject;

	public $sub_content;

	public function __construct(
		$id,
		$content,
		$subject,
		$sub_content)
	{
		$this->id = $id;
		$this->content = $content;
		$this->subect = $subject;
		$this->sub_content = $sub_content;
	}

	public function replace(
		$replace)
	{
		foreach($replace as $key => $value){
			$this->subject = str_replace('{' . $key . '}', $value, $this->subject);
			$this->content = str_replace('{' . $key . '}', $value, $this->content);
			$this->sub_content = str_replace('{' . $key . '}', $value, $this->sub_content);
		}
	}

	public function fullReplace(
		$replace)
	{
		foreach($replace as $key => $value){
			$this->subject = str_replace($key, $value, $this->subject);
			$this->content = str_replace($key, $value, $this->content);
			$this->sub_content = str_replace($key, $value, $this->sub_content);
		}
	}

	public function removeIf(
		$bool)
	{
		$this->subject = self::remove($this->subject, $bool);
		$this->content = self::remove($this->content, $bool);
		$this->sub_content = self::remove($this->sub_content, $bool);
	}

	private static function remove(
		$cnt,
		$bool)
	{
		$mathces = [];
		$replaces = [];
		if(preg_match_all('~\{\#.*\#\}~', $cnt, $mathces) > 0){
			foreach($mathces[0] as $item){
				$org = $item;
				if($bool){
					$item = '';
				}else{
					$item = str_replace('{#', '', $item);
					$item = str_replace('#}', '', $item);
				}
				$replaces[$org] = $item;
			}
		}
		foreach($replaces as $search => $to){
			$cnt = str_replace($search, $to, $cnt);
		}
		return $cnt;
	}
}

class OBE_Templates{

	/**
	 *
	 * @param string $contentKey
	 * @return OBE_Template
	 */
	public static function load(
		$contentKey)
	{
	// 		$sql = 'SELECT d.description, n.subject, n.sub_content, n.template_id
	// 				FROM obe_templates AS n, obe_entitys AS e, obe_descriptions AS d
	// 				WHERE n.key = \'' . $contentKey . '\'
	// 				AND n.template_id = e.entityid
	// 				AND d.descriptionid = e.entityid
	// 				AND e.langid = ' . OBE_Language::$id . '';

// 		if($item = OBE_App::$db->FetchSingleArray($sql)){
	// 			return new OBE_Template($item['template_id'], $item['description'], $item['subject'], $item['sub_content']);
	// 		}
	// 		throw new OBE_Exception('Šablona \'' . $contentKey . '\' neexistuje');
	// 		return NULL;
	}

	public static function fromFile(
		$file)
	{
		$desc = file_get_contents($file);
		return new OBE_Template(null, $desc, '', '');
	}
}