<?php
/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @license Affero GPLv3           **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

use OBX\Core\Tools;

if( !function_exists('wd') ) {
	function wd($data, $collapseTitle = null, $bPrintCondition = true) {
		Tools::debug($data, $collapseTitle, $bPrintCondition);
	}
}
if( !function_exists('d') ) {
	function d($data, $collapseTitle = null, $bPrintCondition = true) {
		Tools::debug($data, $collapseTitle, $bPrintCondition);
	}
}
if( !function_exists('dd') ) {
	function dd($data, $collapseTitle = null, $bPrintCondition = true) {
		Tools::debug($data, $collapseTitle, $bPrintCondition);
	}
}
if( !function_exists('dc') ) {
	function dc($data, $collapseTitle = '', $bPrintCondition = true) {
		Tools::debugConsoleLog($data, $collapseTitle, $bPrintCondition);
	}
}
