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

define("OBX_MAGIC_WORD", "I_KNOW_WHAT_I_DO");
define("I_KNOW_WHAT_I_DO", "I_KNOW_WHAT_I_DO");

$arModuleClasses = array(
	 'OBX_Tools'						=> 'classes/Tools.php'
	,"OBX_IMessagePool"					=> "classes/MessagePool.php"
	,"OBX_IMessagePoolStatic"			=> "classes/MessagePool.php"
	,"OBX_CMessagePool"					=> "classes/MessagePool.php"
	,"OBX_CMessagePoolStatic"			=> "classes/MessagePool.php"
	,"OBX_CMessagePoolDecorator"		=> "classes/MessagePool.php"
	,'OBX_IDBSimple'					=> 'classes/DBSimple.php'
	,'OBX_IDBSimpleStatic'				=> 'classes/DBSimple.php'
	,'OBX_DBSimple'						=> 'classes/DBSimple.php'
	,'OBX_DBSimpleStatic'				=> 'classes/DBSimple.php'
);
return $arModuleClasses;




?>
