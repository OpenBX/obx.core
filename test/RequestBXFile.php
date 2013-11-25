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
use OBX\Core\Curl\Request;
use OBX\Core\Curl\RequestBXFile;

require_once __DIR__.'/_Request.php';

class TestRequestBXFile extends _Request {

	public function testSaveToBXFile() {
		$Request = new RequestBXFile(self::$_urlTestFiles.'/favicon.png');
		$Request->download();
		$Request->saveToBXFile('/upload//obx.core/test/RequestBXFile');
	}

	public function testSaveToIBElement() {

	}

	public function testSaveToIBProp() {
		
	}

}
 