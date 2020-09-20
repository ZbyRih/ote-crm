<?php
class OBE_MailReader extends MailboxReader{
	function __construct($settings){
		parent::__construct($settings['server'], $settings['user'], $settings['pass'], $settings['opts']);
	}
}