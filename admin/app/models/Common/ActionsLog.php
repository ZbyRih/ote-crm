<?php

class MActionsLog extends ModelClass{
	var $name = 'ActionsLog';
	var $table = 'log_actions';
	var $primaryKey = 'id';
	var $rows = ['id', 'created', 'key', 'message'];
}