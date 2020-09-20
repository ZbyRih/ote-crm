<?php

namespace App\Models\ABO;

class GPCParseReport implements IGPCParse{

	public function parse(
		$line)
	{
		$gpc = new GPCReport();
		$gpc->AccountNumber = substr($line, 4, 16);
		$gpc->AccountName = trim(substr($line, 20, 20));
		$gpc->OldBalanceDate = mktime(0, 0, 0, substr($line, 42, 2), substr($line, 40, 2), '20' . substr($line, 44, 2));
		$gpc->OldBalanceValue = (substr($line, 60, 1) . substr($line, 46, 14)) / 100;
		$gpc->NewBalanceValue = (substr($line, 75, 1) . substr($line, 61, 14)) / 100;
		$gpc->DebitValue = (substr($line, 90, 1) . substr($line, 76, 14)) / 100;
		$gpc->CreditValue = (substr($line, 105, 1) . substr($line, 91, 14)) / 100;
		$gpc->SequenceNumber = intval(substr($line, 106, 3));
		$gpc->Date = mktime(0, 0, 0, substr($line, 111, 2), substr($line, 109, 2), '20' . substr($line, 113, 2));
		$gpc->CheckSum = sha1(md5($line) . $line);
		return $gpc;
	}
}