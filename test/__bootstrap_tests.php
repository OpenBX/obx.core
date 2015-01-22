<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

define("BX_SKIP_SESSION_EXPAND", true);
//define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
//define('BX_PULL_SKIP_LS', true);
//if (!defined('BX_DONT_SKIP_PULL_INIT'))
//	define("BX_SKIP_PULL_INIT", true);

define('DBPersistent', true);
$curDir = dirname(__FILE__);
$wwwRootStrPos = strpos($curDir, '/bitrix/modules/obx.core');
if( $wwwRootStrPos === false ) {
	die('Can\'t find www-root');
}

$_SERVER['DOCUMENT_ROOT'] = substr($curDir, 0, $wwwRootStrPos);
//print_r($_SERVER);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
__IncludeLang(__DIR__.'/lang/'.LANGUAGE_ID.'/__bootstrap_tests.php');
global $USER;
global $DB;
// Без этого фикса почему-то не работает. Не видит это значение в include.php модуля
global $DBType;
$DBType = strtolower($DB->type);

$USER->Authorize(1);
if( !CModule::IncludeModule('iblock') ) {
	die('Module iblock not installed');
}

if( !CModule::IncludeModule('obx.core') ) {
	die('Module OBX:Core not installed');
}
//require_once __DIR__.'/../lib/test/testcase.php';