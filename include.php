<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

//// [pronix:2013=06-24]
//// Без этого фикса не будет корректно подключено в режиме cli (соответственно не будут работать phpunit-тесты)
//global $DBType;
//$DBType = strtolower($DB->type);

//// [pronix:2013-06-24]
//// Если мы подключаем другой модуль тут, нужно помнить, что obx.core будет подключен в событии OnPageStart,
//// т.е. будет отрабатывать каждый хит. Вместо этого имеет отключить тут лишние моодули, а
//// позаботиться о подключении инфоблоков в тез ф-иях и методах, которые будут обращаться к CIBlock*
//if(!CModule::IncludeModule('iblock')){
//	return false;
//}

$arModuleClasses = require dirname(__FILE__).'/classes/.classes.php';
CModule::AddAutoloadClasses('obx.core', $arModuleClasses);
?>