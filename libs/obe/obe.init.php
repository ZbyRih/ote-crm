<?php

function array_keys_exist($keys, $array) {
	if (count (array_intersect($keys,array_keys($array))) == count($keys)) {
		return true;
	}
	return false;
}
