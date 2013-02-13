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

$currentDir = dirname(__FILE__);
$arModuleClasses = require $currentDir.'/classes/.classes.php';
foreach ($arModuleClasses as $classPath) {
	$classPath = $currentDir.'/'.$classPath;
	if(is_file($classPath)) {
		require_once $classPath;
	}
}
?>
