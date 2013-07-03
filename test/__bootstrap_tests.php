<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

define('DBPersistent', true);
$curDir = dirname(__FILE__);
$wwwRootStrPos = strpos($curDir, '/bitrix/modules/obx.core');
if( $wwwRootStrPos === false ) {
	die('Can\'t find www-root');
}

$_SERVER['DOCUMENT_ROOT'] = substr($curDir, 0, $wwwRootStrPos);
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
require_once dirname(__FILE__).'/../classes/TestCase.php';