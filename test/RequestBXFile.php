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
		$fileID = $Request->saveToBXFile('/upload//obx.core/test/RequestBXFile');
		$this->assertTrue(($fileID > 0));
		$arFile = \CFile::GetFileArray($fileID);
		$this->assertEquals('favicon.png', $arFile['ORIGINAL_NAME']);
		$this->assertEquals('image/png', $arFile['CONTENT_TYPE']);
		$this->assertFileExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
		\CFile::DoDelete($arFile['ID']);
		$this->assertFileNotExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
	}

	public function testSaveToBXFileAfterGetContent() {
		$Request = new RequestBXFile(self::$_urlTestFiles.'/favicon.png');
		$Request->send();
		$fileID = $Request->saveToBXFile('/upload//obx.core/test/RequestBXFile');
		$this->assertTrue(($fileID > 0));
		$arFile = \CFile::GetFileArray($fileID);
		$this->assertEquals('favicon.png', $arFile['ORIGINAL_NAME']);
		$this->assertEquals('image/png', $arFile['CONTENT_TYPE']);
		$this->assertFileExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
		\CFile::DoDelete($arFile['ID']);
		$this->assertFileNotExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
	}

	public function testSaveToIBElement() {

	}

	public function testSaveToIBProp() {
		
	}

}
 