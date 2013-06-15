<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 **         Artem P. Morozov  aka tashiro     **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @mailto tashiro@yandex.ru                 **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

define('OBX_MAGIC_WORD', '__I_KNOW_WHAT_I_DO__');
define('I_KNOW_WHAT_I_DO', '__I_KNOW_WHAT_I_DO__');

$arModuleClasses = array(
	 'OBX_Tools'						=> 'classes/Tools.php'
	,'OBX\Core\Tools'					=> 'classes/Tools.php'
	,'OBX\Core\JSLang'					=> 'classes/JSLang.php'
	,'OBX\Core\IMessagePool'			=> 'classes/MessagePool.php'
	,'OBX\Core\IMessagePoolStatic'		=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePool'			=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePoolStatic'		=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePoolDecorator'	=> 'classes/MessagePool.php'
	,'OBX\Core\DBSResult'				=> 'classes/DBSimple.php'
	,'OBX\Core\IDBSimple'				=> 'classes/DBSimple.php'
	,'OBX\Core\IDBSimpleStatic'			=> 'classes/DBSimple.php'
	,'OBX\Core\DBSimple'				=> 'classes/DBSimple.php'
	,'OBX\Core\DBSimpleStatic'			=> 'classes/DBSimple.php'
	,'OBX\Core\VisitorDBS'				=> 'classes/VisitorList.php'
	,'OBX\Core\VisitorList'				=> 'classes/VisitorList.php'
	,'OBX\Core\Visitor'					=> 'classes/Visitor.php'
	,'OBX\Core\VisitorHitDBS'			=> 'classes/VisitorHitList.php'
	,'OBX\Core\VisitorHitList'			=> 'classes/VisitorHitList.php'
	,'OBX\Core\ModuleDependencies'		=> 'classes/ModuleDependencies.php'
	,'OBX\Core\Wizard\ImportIBlock'		=> 'classes/WizardImportIBlock.php'
);
return $arModuleClasses;
