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

/**
 * @global string $DBType
 * @global string $DB
 */
global $DBType, $DB;
$DBType = strtolower($DB->type);

define('OBX_MAGIC_WORD', '__I_KNOW_WHAT_I_DO__');
define('I_KNOW_WHAT_I_DO', '__I_KNOW_WHAT_I_DO__');

$arModuleClasses = array(
	 'OBX\Core\Tools'						=> 'classes/Tools.php'
	,'OBX_Tools'							=> 'classes/Tools.php'
	,'OBX\Core\EventD'						=> 'classes/EventD.php'
	,'OBX\Core\JSMessages'					=> 'classes/JSMessages.php'
	,'OBX\Core\IMessagePool'				=> 'classes/MessagePool.php'
	,'OBX\Core\IMessagePoolStatic'			=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePool'				=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePoolStatic'			=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePoolDecorator'		=> 'classes/MessagePool.php'
);
if($DBType === 'mysql') {
	$arModuleClasses['OBX\Core\DBSResult']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\IDBSimple']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\IDBSimpleStatic']			= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\DBSimple']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\DBSimpleStatic']				= 'classes/DBSimple.php';
	//$arModuleClasses['OBX\Core\ModuleDependencies']			= 'classes/ModuleDependencies.php';
	$arModuleClasses['OBX\Core\Xml\ParserDB']				= 'classes/Xml/ParserDB.'.$DBType.'.php';
	$arModuleClasses['OBX\Core\Xml\Parser']					= 'classes/Xml/Parser.php';
	$arModuleClasses['OBX\Core\Xml\Exceptions\ParserError']	= 'classes/Xml/Exceptions/Parser.php';
}
$arModuleClasses['OBX\Core\Wizard\ImportIBlock']		= 'classes/WizardImportIBlock.php';
$arModuleClasses['OBX\Core\Settings\ISettings']			= 'classes/Settings.php';
$arModuleClasses['OBX\Core\Settings\Settings']			= 'classes/Settings.php';
$arModuleClasses['OBX\Core\Settings\ITab']				= 'classes/Settings.php';
$arModuleClasses['OBX\Core\Settings\ATab']				= 'classes/Settings.php';
$arModuleClasses['OBX\Core\Settings\Tab']				= 'classes/Settings.php';
$arModuleClasses['OBX\Core\Settings\IAdminPage']		= 'classes/Settings.php';
$arModuleClasses['OBX\Core\Settings\AdminPage']			= 'classes/Settings.php';
$arModuleClasses['OBX\Core\Test\TestCase']				= 'classes/TestCase.php';


$arStaticIncludeSkip = array(
	'OBX\Core\Test\TestCase'
);

return $arModuleClasses;
