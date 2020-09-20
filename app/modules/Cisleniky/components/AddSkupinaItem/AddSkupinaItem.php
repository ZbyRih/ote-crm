<?php
namespace App\Modules\Ciselniky\Components;

use App\Extensions\Components\BaseComponent;
use App\Models\Tables\CiselnikySkupinyTable;

class AddSkupinaItem extends BaseComponent{

	/** @var CiselnikySkupinyTable */
	private $skup;

	/** @var [] */
	public $onSave = [];

	public function __construct(CiselnikySkupinyTable $skup){
		$this->skup = $skup;
	}

	public function createComponentForm(){
		$f = $this->createForm()
			->makeInline()
			->makeSmall();

		$f->addText('nazev', 'Název', 20)->setRequired('Název musí být zadán.');
		$f->addSubmit('add', 'Přidat');

		$f->onSuccess[] = function ($f, $v){
			$this->onSave($f, $v);
		};
		return $f;
	}
}