<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

/**
 * @global string $DBType
 * @global string $DB
 */
global $DBType, $DB;
$DBType = strtolower($DB->type);

$arModuleClasses = array(
	 'OBX\Core\Tools'							=> 'classes/Tools.php'
	,'OBX_Tools'								=> 'classes/Tools.php'
	,'OBX\Core\EventD'							=> 'classes/EventD.php'
	,'OBX\Core\JSMessages'						=> 'classes/JSMessages.php'
	,'OBX\Core\IMessagePool'					=> 'classes/MessagePool.php'
	,'OBX\Core\IMessagePoolStatic'				=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePool'					=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePoolStatic'				=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePoolDecorator'			=> 'classes/MessagePool.php'
	,'OBX\Core\LogFile'							=> 'classes/LogFile.php'
	,'OBX\Core\Mime'							=> 'classes/Mime.php'
	,'OBX\Core\Curl\Request'					=> 'classes/Curl/Request.php'
	,'OBX\Core\Curl\RequestBXFile'				=> 'classes/Curl/RequestBXFile.php'
	,'OBX\Core\Curl\MultiRequest'				=> 'classes/Curl/MultiRequest.php'
	,'OBX\Core\Curl\MultiRequestBXFile'			=> 'classes/Curl/MultiRequestBXFile.php'
	,'OBX\Core\Http\Download'					=> 'classes/Http/Download.php'
	,'OBX\Core\Http\Request'					=> 'classes/Http/Request.php'
	,'OBX\Core\Wizard\ImportIBlock'				=> 'classes/WizardImportIBlock.php'
	,'OBX\Core\Settings\ISettings'				=> 'classes/Settings.php'
	,'OBX\Core\Settings\Settings'				=> 'classes/Settings.php'
	,'OBX\Core\Settings\ITab'					=> 'classes/Settings.php'
	,'OBX\Core\Settings\ATab'					=> 'classes/Settings.php'
	,'OBX\Core\Settings\Tab'					=> 'classes/Settings.php'
	,'OBX\Core\Settings\IAdminPage'				=> 'classes/Settings.php'
	,'OBX\Core\Settings\AdminPage'				=> 'classes/Settings.php'
	,'OBX\Core\Test\TestCase'					=> 'classes/TestCase.php'
	,'OBX\Core\SimpleBenchMark'					=> 'classes/SimpleBenchMark.php'
	,'OBX\Core\LessCSS'							=> 'classes/LessCSS.php'
	,'OBX\Core\Exceptions\AError'				=> 'classes/Exceptions/AError.php'
	,'OBX\Core\Exceptions\LogFileError'			=> 'classes/Exceptions/LogFileError.php'
	,'OBX\Core\Exceptions\Curl\RequestError'	=> 'classes/Exceptions/Curl/RequestError.php'
	,'OBX\Core\Exceptions\Curl\CurlError'		=> 'classes/Exceptions/Curl/CurlError.php'
	,'OBX\Core\Exceptions\Http\DownloadError'	=> 'classes/Exceptions/Http/DownloadError.php'
	,'OBX\Core\Exceptions\LessCSSError'			=> 'classes/Exceptions/LessCSSError'

);
if($DBType === 'mysql') {
	$arModuleClasses['OBX\Core\DBSResult']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\IDBSimple']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\IDBSimpleStatic']			= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\DBSimple']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\DBSimpleStatic']				= 'classes/DBSimple.php';
	//$arModuleClasses['OBX\Core\ModuleDependencies']		= 'classes/ModuleDependencies.php';
	$arModuleClasses['OBX\Core\Xml\ParserDB']				= 'classes/Xml/ParserDB.'.$DBType.'.php';
	$arModuleClasses['OBX\Core\Xml\Parser']					= 'classes/Xml/Parser.php';
	$arModuleClasses['OBX\Core\Exceptions\Xml\ParserError']	= 'classes/Exceptions/Xml/ParserError.php';
}



$arStaticIncludeSkip = array(
	'OBX\Core\Test\TestCase'
);

return $arModuleClasses;
