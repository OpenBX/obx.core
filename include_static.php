<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

$currentDir = dirname(__FILE__);
$arModuleClasses = require $currentDir.'/classes/.classes.php';
$arNotIncludeStatically = array(
	'OBX\Core\Test\TestCase'
);
foreach ($arModuleClasses as $class => $classPath) {
	if(in_array($class, $arNotIncludeStatically)) {
		continue;
	}
	$classPath = $currentDir.'/'.$classPath;
	if(is_file($classPath)) {
		require_once $classPath;
	}
}
?>
