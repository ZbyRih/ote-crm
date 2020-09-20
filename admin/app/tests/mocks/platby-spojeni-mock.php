<?php

$mock = new MPlatby();
$mock = new MZalohy();
$mock = new MContacts();
$mock = new MPVParFZ();

runkit_method_remove('MPlatby', 'Save');
runkit_method_remove('MZalohy', 'Save');

class MockVS{

	public static $vs = 123465879;
}

class MockPlatby extends MockModel{
}

class MockZalohy extends MockModel{
}

class MockContacts extends MockModel{
}

class MockPVParFZ extends MockModel{
}

runkit_method_copy('MPlatby', 'Save', 'MockPlatby');
runkit_method_copy('MZalohy', 'Save', 'MockZalohy');
runkit_method_copy('MPVParFZ', 'Save', 'MockPVParFZ');
