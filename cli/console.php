<?php
list($_SERVER['DOCUMENT_ROOT']) = explode('/bitrix/modules', dirname(__FILE__));
/**
 * http://php.net/manual/en/features.commandline.interactive.php
 * Note:
 * Files included through auto_prepend_file and auto_append_file are parsed in this mode but with some restrictions - e.g. functions have to be defined before called.
 *
 * Note:
 * Autoloading is not available if using PHP in CLI interactive mode.
 *
 * Потому делаем ф-ию, а её уже вызываем в интерактивном режиме и далее работаем с битриксом
 */
function _bx() {
	require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
	global $DB, $DBType;
	$DBType = strtolower($DB->type);
	require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
	CModule::IncludeModule('iblock');
	CModule::IncludeModule('obx.core');
}