<?php

namespace App\Models\ABO;

class GPCParseItem implements IGPCParse{

	public function parse(
		$line)
	{
		$gpc = new GPCItem();
		$gpc->AccountNumber = substr($line, 4, 16);
		$gpc->OffsetAccount = substr($line, 20, 16);
		$gpc->RecordNumber = substr($line, 36, 13);
		$gpc->Value = substr($line, 49, 12) / 100;
		$gpc->Code = substr($line, 61, 1);
		$gpc->VariableSymbol = intval(substr($line, 62, 10));
		$gpc->BankCode = substr($line, 74, 4);
		$gpc->ConstantSymbol = intval(substr($line, 78, 4));
		$gpc->SpecificSymbol = intval(substr($line, 82, 10));
		$gpc->Valut = substr($line, 92, 6);
		$gpc->ClientName = iconv('windows-1250', 'UTF-8', substr($line, 98, 20));
		$gpc->CurrencyCode = substr($line, 119, 4);
		$gpc->DueDate = mktime(0, 0, 0, substr($line, 125, 2), substr($line, 123, 2), substr($line, 127, 2));
		$gpc->CheckSum = sha1(md5($line) . $line);
		return $gpc;
	}
}