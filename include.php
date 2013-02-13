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

if(!CModule::IncludeModule('iblock')){
	return false;
}

$arModuleClasses = require dirname(__FILE__).'/classes/.classes.php';
CModule::AddAutoloadClasses('obx.core', $arModuleClasses);
?>