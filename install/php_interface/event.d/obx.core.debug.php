<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

use OBX\Core\Tools;

if( !function_exists('wd') ) {
	function wd($mixed, $collapse = null, $bPrint = true) {
		Tools::debug($mixed, $collapse, $bPrint);
	}
}
if( !function_exists('d') ) {
	function d($mixed, $collapse = null, $bPrint = true) {
		Tools::debug($mixed, $collapse, $bPrint);
	}
}
if( !function_exists('dd') ) {
	function dd($mixed, $collapse = null, $bPrint = true) {
		Tools::debug($mixed, $collapse, $bPrint);
	}
}
