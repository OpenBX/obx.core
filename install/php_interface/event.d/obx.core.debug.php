<?php
/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @License GPLv3                  **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

if( !function_exists('wd') ) {
	function wd($mixed, $collapse = null, $bPrint = true) {
		return OBX_Tools::debug($mixed, $collapse, $bPrint);
	}
}
if( !function_exists('d') ) {
	function d($mixed, $collapse = null, $bPrint = true) {
		return OBX_Tools::debug($mixed, $collapse, $bPrint);
	}
}
if( !function_exists('dd') ) {
	function dd($mixed, $collapse = null, $bPrint = true) {
		return OBX_Tools::debug($mixed, $collapse, $bPrint);
	}
}
?>