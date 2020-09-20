<?php

class OBE_DebugBar{

	private $panels;

	/**
	 *
	 * @param OBE_DebugPanel $panel
	 * @return OBE_DebugPanel
	 */
	public function addPanel(
		$panel)
	{
		$this->panels[$panel->label] = $panel;
		return $panel;
	}

	/**
	 *
	 * @return OBE_DebugPanel
	 */
	public function getPanel(
		$key)
	{
		if($key == 'dumps' && !isset($this->panels[$key])){
			$this->panels[$key] = new OBE_DumpsDebugPanel();
		}
		return $this->panels[$key];
	}

	public function create(
		$debug)
	{
	}
}

class OBE_DebugPanel{

	public $head = '';

	public $label = '';

	public $id = '';

	public $image = null;

	public function __construct(
		$id)
	{
		$this->id = $id;
	}

	public function render()
	{
		return '<div class="panel" id="panel-' . $this->id . '" style="display:none">
					<h1>' . $this->head . '</h1>
					<div class="inner">' . $this->getContent() . '</div>
					<div class="icons">
						<a href="#" rel="close" title="close window">×</a>
					</div>
				</div>';
	}

	public function renderLabel()
	{
		return '<li><a href"#" rel="panel-' . $this->id . '"><span>' . (($this->image) ? '<img src="data:image/png;base64,' . $this->image . '"></img>' : '') . $this->getLabel() . '</span></a></li>';
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function getContent()
	{
		return '';
	}
}

class OBE_QrysDebugPanel extends OBE_DebugPanel{

	private $elapsed = 0;

	public function __construct(
		$id = '')
	{
		parent::__construct('querys');
		$this->head = (OBE_App::$db) ? OBE_App::$db->getDBName() : 'no-database';
		$this->image = 'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC';
	}

	public function getContent()
	{
		$cnt = '';
		$elapsed = 0;

		if(OBE_App::$db){
			$cnt .= '<table><tr><th>elased</th><th>sql</th><th>rows</th></tr>';
			foreach(OBE_App::$db->querys as $sql){
				$cnt .= '<tr><td>' . $sql->elapsed . '</td><td>' . $sql->getSql() . '</td><td class="num">' . $sql->rows . '</td></tr>';
				$elapsed += $sql->elapsed;
			}
			$cnt .= '</table>';
		}

		$this->elapsed = $elapsed;

		return $cnt;
	}

	public function getLabel()
	{
		return ((OBE_App::$db) ? OBE_App::$db->stat() : 'null') . ' (' . $this->elapsed . 's)';
	}
}

class OBE_LogDebugPanel extends OBE_DebugPanel{

	public function __construct(
		$id = '')
	{
		parent::__construct('log');
		$this->head = 'Log';
		$this->label = 'log';
		$this->image = null;
	}

	public function getContent()
	{
		return '<div><pre class="no-wrap">' . OBE_Log::getFileLog() . '</pre></div>';
	}
}

class OBE_DumpsDebugPanel extends OBE_DebugPanel{

	private $dumps = [];

	public function __construct(
		$id = '')
	{
		parent::__construct('dumps');
		$this->head = 'Dumps';
		$this->label = 'dumps';
		$this->image = null;
	}

	public function getContent()
	{
		$cnt = '<table><tr><th>dump</th></tr>';
		foreach($this->dumps as $d){
			$cnt .= '<tr><td>' . \Tracy\Dumper::toHtml($d) . '</td></tr>';
		}
		$cnt .= '</table>';

		return $cnt .= '</table>';
	}

	public function addDump()
	{
		$this->dumps = array_merge($this->dumps, func_get_args());
	}
}