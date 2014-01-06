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

class TestErrorExceptions extends TestCase {
	const _DIR_ = __DIR__;

	static public function getClassList() {
		return array(
			array(
				'\OBX\Core\Exceptions\Xml\ParserError',
				'/obx.core/classes/Exceptions/Xml/ParserError.php'
			),
			array(
				'\OBX\Core\Exceptions\Http\DownloadError',
				'/obx.core/classes/Exceptions/Http/DownloadError.php'
			),
			array(
				'\OBX\Core\Exceptions\LogFileError',
				'/obx.core/classes/Exceptions/LogFileError.php'
			),
			array(
				'\OBX\Core\Exceptions\Curl\RequestError',
				'/obx.core/classes/Exceptions/Curl/RequestError.php'
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
			$errCode = $class::LANG_PREFIX.$constantValue;
			$this->assertArrayHasKey($errCode, $arErrMsg);
			$this->assertEquals($arErrMsg[$errCode], $class::getLangMessage($constantValue));
		}
	}
} 