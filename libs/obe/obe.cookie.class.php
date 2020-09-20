<?php

class OBE_Cookie{
    static function read($var){
        if (isset($_COOKIE[$var])) {
            return $_COOKIE[$var];
        } else {
            return false;
        }
    }

    static function exists($var){
        return isset($_COOKIE[$var]);
    }

    static function write($var, $val, $path = '/', $domain = NULL, $expire = 86400/*den*/){
        self::checkHeaders();
        setcookie($var, $val, time() + $expire, $path, $domain);
    }

    static function writePerm($var, $val, $path = '/', $domain = NULL){
    	self::write($var, $val, $path, $domain, 86400*90);
    }

    static function delete($var, $path = '/', $domain = NULL){
        self::checkHeaders();
        setcookie($var, false, time() - 60000, $path, $domain);
    }

    static function checkHeaders(){
        if (headers_sent()) {
            Die("Nelze nastavit cookie, hlavicky byly jiz odeslany.");
        }
    }
}