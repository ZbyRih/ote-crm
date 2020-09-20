<?php
class OBE_MailBase{
	static $bFuncGetMXRR = true;

	static function init(){
		if(!function_exists('getmxrr')){
			self::$bFuncGetMXRR = false;
		}
	}

	static function __getmxrr($hostname, &$mxhosts, &$mxweight = false){
		if(self::$bFuncGetMXRR){
			return getmxrr($hostname, $mxhosts, $mxweight);
		}else{
			return self::_getmxrr($hostname, $mxhosts, $mxweight);
		}
	}

	static function _getmxrr($hostname, &$mxhosts, &$mxweight = false){
	    if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'){
	    	return;
	    }
	    if(!is_array ($mxhosts)){
	    	$mxhosts = [];
	    }
	    if(empty($hostname)){
	    	return;
		}
		$exec = 'nslookup -type=MX ' . escapeshellarg($hostname);
		@exec($exec, $output);
		if(empty($output)){
	    	return;
		}
		$i=-1;
		foreach($output as $line){
			$i++;
			if(preg_match("/^$hostname\tMX preference = ([0-9]+), mail exchanger = (.+)$/i", $line, $parts)){
				$mxweight[$i] = trim($parts[1]);
				$mxhosts[$i] = trim($parts[2]);
			}
			if(preg_match('/responsible mail addr = (.+)$/i', $line, $parts)){
				$mxweight[$i] = $i;
				$mxhosts[$i] = trim($parts[1]);
			}
		}
		return ($i != -1);
	}

	static function smtp_commands($fp, $commands){
	    foreach ($commands as $command) {
	        fwrite($fp, "$command\r\n");
	        $s = fgets($fp);
	        if (substr($s, 0, 3) != '250') {
	            return false;
	        }
	        while ($s[3] == '-') {
	            $s = fgets($fp);
	        }
	    }
	    return true;
	}

	/** Ověření funkčnosti e-mailu
	* @param string adresa příjemce
	* @param string adresa odesílatele
	* @return bool na adresu lze doručit zpráva, null pokud nejde ověřit
	* @copyright Jakub Vrána, http://php.vrana.cz/
	*/
	static function try_email($email, $from) {
		if(OBE_Core::$debug){
			return true;
		}
	    $domain = preg_replace('~.*@~', '', $email);
		$mxs = [];
		self::__getmxrr($domain, $mxs);
	    if (!in_array($domain, $mxs)) {
	        $mxs[] = $domain;
	    }
	    $commands = [
	        "HELO " . preg_replace('~.*@~', '', $from),
	        "MAIL FROM: <$from>",
	        "RCPT TO: <$email>",
	    ];
	    $return = null;
	    foreach($mxs as $mx){
	        $fp = @fsockopen($mx, 25);
	        if($fp){
	            $s = fgets($fp);
	            while($s[3] == '-'){
	                $s = fgets($fp);
	            }
	            if(substr($s, 0, 3) == '220'){
	                $return = self::smtp_commands($fp, $commands);
	            }
	            fwrite($fp, "QUIT\r\n");
	            fgets($fp);
	            fclose($fp);
	            if (isset($return)) {
	                return $return;
	            }
	        }
	    }
	    return false;
	}
}

OBE_MailBase::init();