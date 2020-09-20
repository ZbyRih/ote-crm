<?php

namespace App\Components\Homepage;

interface ICertificateInfo{

	/**
	 * @return CertificateInfo
	 */
	public function create();
}

interface IInfoReport{

	/**
	 * @return InfoReport
	 */
	public function create();
}