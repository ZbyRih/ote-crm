<?php

$mock = new MActivityLog();

runkit_method_remove('MActivityLog', 'Save');

class MockActivityLog{

	public function Save($s){

	}
}

runkit_method_copy('MActivityLog', 'Save', 'MockActivityLog');