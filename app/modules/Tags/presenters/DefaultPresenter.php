<?php

namespace App\Modules\Tags\Presenters;

use App\Models\Commands\ITagDeleteCommand;
use App\Modules\Tags\Factories\ITagsGrid;
use App\Modules\Tags\Factories\ITagsGridDataSource;
use App\Models\Orm\Tags\TagEntity;

class DefaultPresenter extends BasePresenter{

	/** @var ITagsGrid @inject */
	public $comTagsGrid;

	/** @var ITagsGridDataSource @inject */
	public $comTagsGridDataSource;

	/** @var ITagDeleteCommand @inject */
	public $cmdDeleteTag;

	public function createComponentTagsGrid()
	{
		$src = $this->comTagsGridDataSource->create();
		$src->setUserId($this->user->id);

		$g = $this->comTagsGrid->create();
		$g->setDataSource($src);

		$g->onDelete[] = function (
			$id){
			$cmd = $this->cmdDeleteTag->create();
			$cmd->setId($id);
			$cmd->execute();
			$this->orm->flush();
		};

		$g->onSave[] = function (
			$values){

			if($values['id']){
				$tag = $this->orm->tags->getById($values['id']);
			}else{
				$tag = new TagEntity();
				$tag->userId = $this->user->id;
			}

			$tag->name = $values['name'];
			$tag->color = $values['color'];

			$this->orm->persistAndFlush($tag);
		};

		return $g;
	}
}