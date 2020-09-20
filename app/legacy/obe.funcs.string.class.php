<?php

class OBE_Strings
{

	const PRERG_CZ_CHARS = 'áéíýóúůčěřšžňťďÁÉÍÝÓÚŮČĚŘŠŤŇĎŽ';

	/**
	 * ceske diakriticke znaky
	 * @var Array
	 */
	static $cz = [
		'á',
		'ä',
		'č',
		'ď',
		'é',
		'ě',
		'ë',
		'í',
		'ň',
		'ó',
		'ö',
		'ř',
		'š',
		'ť',
		'ú',
		'ů',
		'ü',
		'ý',
		'ž',
		'Á',
		'Ä',
		'Č',
		'Ď',
		'É',
		'Ě',
		'Ë',
		'Í',
		'Ň',
		'Ó',
		'Ö',
		'Ř',
		'Š',
		'Ť',
		'Ú',
		'Ů',
		'Ü',
		'Ý',
		'Ž'
	];

	/**
	 * bezdiakriticke alternativy k ceskym s diakritikou
	 * @var Array
	 */
	static $us = [
		'a',
		'a',
		'c',
		'd',
		'e',
		'e',
		'e',
		'i',
		'n',
		'o',
		'o',
		'r',
		's',
		't',
		'u',
		'u',
		'u',
		'y',
		'z',
		'A',
		'A',
		'C',
		'D',
		'E',
		'E',
		'E',
		'I',
		'N',
		'O',
		'O',
		'R',
		'S',
		'T',
		'U',
		'U',
		'U',
		'Y',
		'Z'
	];

	/**
	 * pro ereg match podle znaků s diakritikou
	 * @var Array
	 */
	static $ereg_replace = [
		'aáä',
		'cč',
		'dď',
		'eéěë',
		'ií',
		'nň',
		'oóö',
		'rř',
		'sš',
		'tť',
		'uúůü',
		'yý',
		'zž',
		'AÁÄ',
		'CČ',
		'DĎ',
		'EÉĚË',
		'IÍ',
		'NŇ',
		'OÓÖ',
		'RŘ',
		'SŠ',
		'TŤ',
		'UÚŮÜ',
		'YÝ',
		'ZŽ',
		'aá',
		'aä',
		'cč',
		'dď',
		'eé',
		'eě',
		'eë',
		'ií',
		'nň',
		'oó',
		'oö',
		'rř',
		'sš',
		'tť',
		'uú',
		'uů',
		'uü',
		'yý',
		'zž',
		'AÁ',
		'AÄ',
		'CČ',
		'DĎ',
		'EÉ',
		'EĚ',
		'EË',
		'IÍ',
		'NŇ',
		'OÓ',
		'OÖ',
		'RŘ',
		'SŠ',
		'TŤ',
		'UÚ',
		'UŮ',
		'UÜ',
		'YÝ',
		'ZŽ'
	];

	/**
	 * uplne vsechny alfabeticke znaky pro cz
	 * @var Array
	 */
	static $chars = [
		'a',
		'c',
		'd',
		'e',
		'i',
		'n',
		'o',
		'r',
		's',
		't',
		'u',
		'y',
		'z',
		'A',
		'C',
		'D',
		'E',
		'I',
		'N',
		'O',
		'R',
		'S',
		'T',
		'U',
		'Y',
		'Z',
		'á',
		'ä',
		'č',
		'ď',
		'é',
		'ě',
		'ë',
		'í',
		'ň',
		'ó',
		'ö',
		'ř',
		'š',
		'ť',
		'ú',
		'ů',
		'ü',
		'ý',
		'ž',
		'Á',
		'Ä',
		'Č',
		'Ď',
		'É',
		'Ě',
		'Ë',
		'Í',
		'Ň',
		'Ó',
		'Ö',
		'Ř',
		'Š',
		'Ť',
		'Ú',
		'Ů',
		'Ü',
		'Ý',
		'Ž'
	];

	/**
	 * odstrani diakritiku
	 */
	static function remove_diacritics(
		$mstring
	) {
		return str_replace(OBE_Strings::$cz, OBE_Strings::$us, $mstring);
	}

	/**
	 * ereg replace
	 */
	static function forEregDiacritics(
		$mstring
	) {
		$ret = '';
		$rchars = array_flip(self::$chars);
		for ($i = 0; $i < mb_strlen($mstring); $i++) {
			$r = mb_substr($mstring, $i, 1);
			if (isset($rchars[$r])) {
				$r = '[' . OBE_Strings::$ereg_replace[$rchars[$r]] . ']';
			}
			$ret .= $r;
		}
		return '' . $ret . '';
	}

	/**
	 *
	 * @param string $str - retezec pro normalizaci
	 * @return string
	 */
	static function normalizeStr(
		$str
	) {
		$str = OBE_Strings::remove_diacritics(trim(rtrim($str, '-')));
		$str = strtolower($str);
		$str = str_replace([
			' ',
			'_'
		], '-', trim($str));
		$str = preg_replace('/[^-a-z0-9]+/', '', $str);
		$str = preg_replace('/-+/', '-', $str);
		return $str;
	}

	static function sha256(
		$strtohash
	) {
		//dokud nam nepovoli knihovnu mhash return base64_encode(bin2hex(mhash(MHASH_SHA256,$strtohash)));
		return sha1($strtohash);
		//		return md5($strtohash);
	}

	static function generate_code(
		$length
	) {
		$key = "";
		$pattern = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		for ($i = 0; $i < $length; $i++) {
			$key .= $pattern[rand(0, 61)];
		}
		return $key;
	}

	static function sbar_url_encode(
		$str
	) {
		return urlencode($str);
	}

	static function sub_string_remove(
		$string,
		$substring
	) {
		return str_replace($substring, '', $string);
	}

	static function simple_transfer(
		$string,
		$param
	) {
		$begin = substr($string, 0, $param);
		$end = substr($string, $param);
		return $end . $begin;
	}

	static function simple_transfer_rev(
		$string,
		$param
	) {
		$end = substr($string, -$param);
		$begin = substr($string, 0, -$param);
		return $end . $begin;
	}

	static function cutText(
		$string,
		$length
	) {
		$string = strip_tags($string);
		return mb_substr($string, 0, $length);
	}

	static function removeWhiteCharsAndCut(
		$string,
		$length
	) {
		self::removeWhiteChars($string);
	}

	static function removeWhiteChars(
		$string
	) {
		$string = str_replace([
			"\t",
			"\r\n",
			"\n",
			"\r"
		], [
			'',
			' ',
			'',
			''
		], $string);
		$string = mb_ereg_replace(" {2,}", '', $string);
		$string = html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
		$string = rtrim($string);
		return $string;
	}

	static function mb_str_pad(
		$input,
		$pad_length,
		$pad_string = ' ',
		$pad_style = STR_PAD_RIGHT
	) {
		return str_pad($input, strlen($input) - mb_strlen($input) + $pad_length, $pad_string, $pad_style);
	}

	static function extractFirstParagraf(
		$wholeWSWG,
		$tag = NULL
	) {
		if (!empty($wholeWSWG)) {
			$domDocObj = new DOMDocument('1.0', 'UTF-8');

			$lastSet = libxml_use_internal_errors(true);

			if ($domDocObj->loadHTML(
				'<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $wholeWSWG . '</body></html>'
			)) {

				libxml_use_internal_errors($lastSet);

				if ($ps = $domDocObj->getElementsByTagName('p')) {
					if ($ps->length > 0) {
						if ($tag) {
							return '<' . $tag . '>' . $ps->item(0)->nodeValue . '</' . $tag . '>';
						} else {
							return $ps->item(0)->nodeValue;
						}
					}
				}
			} else {
				// $this->bIntroSaved = 'Nevalidní HTML - Html obsahuje entity které nejsou součístí specifikace HTML';
				libxml_use_internal_errors($lastSet);
				libxml_clear_errors();
			}
		}
		return NULL;
	}
}
