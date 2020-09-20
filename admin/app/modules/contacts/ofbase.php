<?php


class OFBaseSubModule extends SubModule{

	public function getSession($sessionKey = null){
		$ses = parent::getSession('faktury');
		if($ses->kli != $this->info->scope->parent->recordId){
			$ses->kli = $this->info->scope->parent->recordId;
			$ses->ote = [];
		}
		if(!$ses->params){
			$ses->params = [
				'title' => null,
				'z' => 0,
				'd' => 0,
				'c' => 0
			];
		}
		if(!$ses->dzp){
			$ses->dzp = null;
		}
		return $ses;
	}
}