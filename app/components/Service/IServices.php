<?php
namespace App\Components\Service;

use App\Components\Service\Components\TestMailDlg;

interface IServices{

	/**
	 *
	 * @return Services
	 */
	public function create();
}

interface ITestMailDlg{

	/**
	 *
	 * @return TestMailDlg
	 */
	public function create();
}