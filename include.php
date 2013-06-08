<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

if(!CModule::IncludeModule('iblock')){
	return false;
}

$arModuleClasses = require dirname(__FILE__).'/classes/.classes.php';
CModule::AddAutoloadClasses('obx.core', $arModuleClasses);
?>