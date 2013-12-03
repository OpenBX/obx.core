<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Test;


use OBX\Core\Xml\Exceptions\ParserError;

class TestErrorExceptions extends TestCase {
	const _DIR_ = __DIR__;

	static public function getClassList() {
		return array(
			array(
				'\OBX\Core\Xml\Exceptions\ParserError',
				'/obx.core/classes/Xml/Exceptions/ParserError.php'
			),
			array(
				'\OBX\Core\Http\Exceptions\DownloadError',
				'/obx.core/classes/Http/Exceptions/DownloadError.php'
			),
			array(
				'\OBX\Core\Exceptions\LogFileError',
				'/obx.core/classes/Exceptions/LogFileError.php'
			),
			array(
				'\OBX\Core\Curl\Exceptions\RequestError',
				'/obx.core/classes/Curl/Exceptions/RequestError.php'
			),
		);
	}

	/**
	 * @param $class
	 * @param $path
	 * @dataProvider getClassList
	 */
	public function testExceptions($class, $path){
		$arErrMsg = IncludeModuleLangFile(static::$_modulesDir.$path, false, true);
		$this->assertTrue(is_array($arErrMsg));
		$this->assertNotEmpty($arErrMsg);
		$Reflection = new \ReflectionClass($class);
		$arConstants = $Reflection->getConstants();
		foreach($arConstants as $constantName => $constantValue) {
			if(substr($constantName, 0, 2) !== 'E_') {
				continue;
			}
			$this->assertArrayHasKey($class::LANG_PREFIX.$constantValue, $arErrMsg);
			$this->assertEquals($arErrMsg[$class::LANG_PREFIX.$constantValue], $class::getLangMessage($constantValue));
		}
	}
} 