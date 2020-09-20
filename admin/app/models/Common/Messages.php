<?php

class MMessagesLog extends ModelClass{
	var $name = 'MessagesLog';
	var $table = 'log_messages';
	var $primaryKey = 'id';
	var $rows = ['id', 'created', 'message'];
}