<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

list($_SERVER['DOCUMENT_ROOT']) = explode('/bitrix/modules', __DIR__);
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
	define("BX_SKIP_SESSION_EXPAND", true);
	define("NO_KEEP_STATISTIC", "Y");
	define("NO_AGENT_STATISTIC","Y");
	define("NO_AGENT_CHECK", true);
	define("DisableEventsCheck", true);
	require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
	global $DB, $DBType;
	$DBType = strtolower($DB->type);
	require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php';
	CModule::IncludeModule('iblock');
	CModule::IncludeModule('obx.core');
}