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

use OBX\Core\Exceptions\AError;

class TestErrorExceptions extends TestCase {
	const _DIR_ = __DIR__;

	static public function getClassList() {
		return array(
			array(
				'\OBX\Core\Exceptions\Xml\ParserError',
				'/obx.core/lib/exceptions/xml/parsererror.php'
			),
			array(
				'\OBX\Core\Exceptions\Http\Downloaderror',
				'/obx.core/lib/exceptions/http/downloaderror.php'
			),
			array(
				'\OBX\Core\Exceptions\LogFileError',
				'/obx.core/lib/exceptions/logfileerror.php'
			),
			array(
				'\OBX\Core\Exceptions\Curl\RequestError',
				'/obx.core/lib/exceptions/curl/requesterror.php'
			),
		);
	}

	/**
	 * @param AError $class
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
			$errCode = $class::ID.$constantValue;
			$this->assertArrayHasKey($errCode, $arErrMsg);
			/** @noinspection PhpUndefinedMethodInspection */
			$this->assertEquals($arErrMsg[$errCode], $class::getLangMessage($constantValue));
		}
	}
} 