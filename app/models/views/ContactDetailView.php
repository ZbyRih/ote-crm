<?php

namespace App\Models\Views;

class ContactDetailView{

	public static function sname(
		$cd)
	{
		return trim(($cd['kind']) ? $cd['firm_name'] : ($cd['firstname'] . ' ' . $cd['lastname']));
	}
}