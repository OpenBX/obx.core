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

$arModuleClasses = array(
	 'OBX\Core\Tools'							=> 'classes/Tools.php'
	,'OBX_Tools'								=> 'classes/Tools.php'
	,'OBX\Core\Components\Parameters'			=> 'classes/Components/Parameters.php'
	,'OBX\Core\EventD'							=> 'classes/EventD.php'
	,'OBX\Core\JSMessages'						=> 'classes/JSMessages.php'
	,'OBX\Core\IMessagePool'					=> 'classes/MessagePool.php'
	,'OBX\Core\IMessagePoolStatic'				=> 'classes/MessagePool.php'
	,'OBX\Core\MessagePool'						=> 'classes/MessagePool.php'
	,'OBX\Core\MessagePoolStatic'				=> 'classes/MessagePool.php'
	,'OBX\Core\MessagePoolDecorator'			=> 'classes/MessagePool.php'
	,'OBX\Core\CMessagePool'					=> 'classes/MessagePool.deprecated.php'
	,'OBX\Core\CMessagePoolStatic'				=> 'classes/MessagePool.deprecated.php'
	,'OBX\Core\CMessagePoolDecorator'			=> 'classes/MessagePool.deprecated.php'
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
	,'OBX\Core\LessCss\Connector'				=> 'classes/LessCss/Connector.php'
	,'OBX\Core\FlatTreeObject'					=> 'classes/FlatTreeObject.php'
	,'OBX\Core\Exceptions\AError'				=> 'classes/Exceptions/AError.php'
	,'OBX\Core\Exceptions\LogFileError'			=> 'classes/Exceptions/LogFileError.php'
	,'OBX\Core\Exceptions\Curl\RequestError'	=> 'classes/Exceptions/Curl/RequestError.php'
	,'OBX\Core\Exceptions\Curl\CurlError'		=> 'classes/Exceptions/Curl/CurlError.php'
	,'OBX\Core\Exceptions\Http\DownloadError'	=> 'classes/Exceptions/Http/DownloadError.php'
	,'OBX\Core\Exceptions\LessCss\LessCssError'	=> 'classes/Exceptions/LessCss/LessCssError.php'
);
if($DBType === 'mysql') {
	$arModuleClasses['OBX\Core\IDBSimple']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\DBSimple']					= 'classes/DBSimple.php';
	$arModuleClasses['OBX\Core\IDBSimpleStatic']			= 'classes/DBSimpleStatic.php';
	$arModuleClasses['OBX\Core\DBSimpleStatic']				= 'classes/DBSimpleStatic.php';
	$arModuleClasses['OBX\Core\DBSResult']					= 'classes/DBSResult.php';
	$arModuleClasses['OBX\Core\ActiveRecord']				= 'classes/ActiveRecord.php';
	$arModuleClasses['OBX\Core\Xml\ParserDB']				= 'classes/Xml/ParserDB.'.$DBType.'.php';
	$arModuleClasses['OBX\Core\Xml\Parser']					= 'classes/Xml/Parser.php';
	$arModuleClasses['OBX\Core\Exceptions\Xml\ParserError']	= 'classes/Exceptions/Xml/ParserError.php';
}



$arStaticIncludeSkip = array(
	'OBX\Core\Test\TestCase'
);

return $arModuleClasses;
