<?php
class ImportConfigClass{
	/**
	 * Tabulky na zapis
	 *
	 * @var array
	 */
	var $tables;
	/**
	 * sloupce na zapis, mohou byt uvedeny sloupce s id -1 tyto se pak naplni napriklad v callback fci
	 *
	 * @var array - 'tabulka' => array('sloupec' => id v csv)
	 */
	var $rows;
	/**
	 * sloupce ktere nesmi byt prazdne
	 *
	 * @var array - 'tabulka' => array('sloupec',...)
	 */
	var $noEmpty = [];
	/**
	 * tabulka pro vytvoreni noveho zaznamu
	 *
	 * @var array
	 */
	var $newItemsDBDef = [];
	/**
	 * index sloupce s klicem
	 *
	 * @var array
	 */
	var $tablesIndexRows = [];

	var $reverseIndexRefs = [];
}