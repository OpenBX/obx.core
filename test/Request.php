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
use OBX\Core\Http\Request;
use OBX\Core\Http\MultiRequest;

class TestRequest extends TestCase {
	static protected $_urlTestFiles = 'http://smokeoffice12.loc/bitrix/modules/obx.core/test/data/dwn_files/';
	static protected $_urlJSON = 'http://smokeoffice12.loc/bitrix/tools/obx.core/test.response.php?XDEBUG_SESSION_START=PHPSTORM';

	static public function getCurDir() {
		return __DIR__;
	}

	public function getFilesList() {
		return array(
			array('favicon.ico'),
			array('favicon.png'),
			array('favicon.jpg'),
			array('favicon.gif'),
			array('favicon.7z'),
			array('favicon.ico.rar'),
			array('favicon.ico.tar.bz2'),
			array('favicon.ico.zip'),
			array('favicon.tar.gz'),
			array('favicon.tar.xz'),
			array('test.html'),
			array('test.txt'),
			array('test.odp'),
			array('test.ods'),
			array('test.odt'),
			array('test.doc'),
			array('test.docx'),
			array('test.ppt'),
			array('test.pptx'),
			array('test.xls'),
			array('test.xlsx'),

		);
	}

	public function testEncodePost() {
		$arPOST = array(
			'key1' => 'val1',
			'arr1' => array(
				'key11' => 'val11',
				'key12' => 'val12'
			),
			'arr2' => array(
				'key21' => 'val21',
				'arr22' => array(
					'key221' => 'val221'
				)
			),
			'key3' => 'val3'
		);
		$expectedQuery = 'key1=val1&arr1[key11]=val11&arr1[key12]=val12&arr2[key21]=val21&arr2[arr22][key221]=val221&key3=val3';
		$actualQuery = Request::arrayToCurlPost($arPOST);
		$this->assertEquals($expectedQuery, $actualQuery);
	}

	public function testGetContent() {
		$Request = new Request(self::$_urlJSON.'&test=testGetContent');
		$Request->setPost(array(
			'key1' => 'val1'
		));
		$body = $Request->send();
		$header = $Request->getHeader();
		$arContentJSON = json_decode($body, true);
		$this->assertTrue(is_array($arContentJSON));
		$this->assertArrayHasKey('get', $arContentJSON);
		$this->assertArrayHasKey('test', $arContentJSON['get']);
		$this->assertEquals('testGetContent', $arContentJSON['get']['test']);
		$this->assertArrayHasKey('post', $arContentJSON);
		$this->assertArrayHasKey('key1', $arContentJSON['post']);
		$this->assertEquals('val1', $arContentJSON['post']['key1']);
	}

	public function testSaveContentToFile() {
		$Request = new Request(self::$_urlJSON.'&test=testSaveContentToFile');
		$body = $Request->send();
		$Request->saveToFile('/upload/obx.core/test/testSaveContentToFile.json');
		$this->assertFileExists(self::$_docRoot.'/upload/obx.core/test/testSaveContentToFile.json');
		$fileContent = file_get_contents(self::$_docRoot.'/upload/obx.core/test/testSaveContentToFile.json');
		$arJSONFileContent = json_decode($fileContent, true);
		$this->assertTrue(is_array($arJSONFileContent));
		$this->assertArrayHasKey('get', $arJSONFileContent);
		$this->assertArrayHasKey('test', $arJSONFileContent['get']);
		$this->assertEquals('testSaveContentToFile', $arJSONFileContent['get']['test']);
		$arContentJSON = json_decode($body, true);
		$this->assertTrue(is_array($arContentJSON));
		$this->assertArrayHasKey('get', $arContentJSON);
		$this->assertArrayHasKey('test', $arContentJSON['get']);
		$this->assertEquals('testSaveContentToFile', $arContentJSON['get']['test']);
	}

	public function testSaveContentToDir() {
		$Request = new Request(self::$_urlJSON.'&test=testSaveContentToDir&download=Y');
		$body = $Request->send();
		$Request->saveToDir('/upload/obx.core/test/');
		$this->assertFileExists(self::$_docRoot.'/upload/obx.core/test/testSaveContentToDir.json');
		$fileContent = file_get_contents(self::$_docRoot.'/upload/obx.core/test/testSaveContentToDir.json');

		$arJSONFileContent = json_decode($fileContent, true);
		$this->assertTrue(is_array($arJSONFileContent));
		$this->assertArrayHasKey('get', $arJSONFileContent);
		$this->assertArrayHasKey('test', $arJSONFileContent['get']);
		$this->assertEquals('testSaveContentToDir', $arJSONFileContent['get']['test']);
		$arContentJSON = json_decode($body, true);
		$this->assertTrue(is_array($arContentJSON));
		$this->assertArrayHasKey('get', $arContentJSON);
		$this->assertArrayHasKey('test', $arContentJSON['get']);
		$this->assertEquals('testSaveContentToDir', $arContentJSON['get']['test']);
	}

	public function testParseHeader() {
		$Request = new Request(self::$_urlJSON.'&test=testSaveContentToFile&download=Y');
		$body = $Request->send();
		$arHeader = $Request->getHeader();
		$this->assertTrue(is_array($arHeader));
		$this->assertArrayHasKey('CHARSET', $arHeader);
		$this->assertArrayHasKey('COOKIES', $arHeader);
		$this->assertEquals('UTF-8', $Request->getCharset());
		$this->assertEquals('application/json', $Request->getContentType());
	}

	/**
	 *
	 */
	public function testDownloadJSONToFile() {
		$Request = new Request(self::$_urlJSON.'&test=testDownloadToFile&download=Y');
		$bSuccess = $Request->downloadToFile('/upload/obx.core/test/testDownloadToFile.json');
		$this->assertEquals('application/json', $Request->getContentType());
		$this->assertEquals('UTF-8', $Request->getCharset());
	}

	/**
	 * @depends testDownloadJSONToFile
	 */
	public function _testDownloadJSONToDir() {
		$Request = new Request(self::$_urlJSON.'&test=testDownloadToDir&download=Y');
		$Request->downloadToDir('/upload/obx.core/test');
	}

	/**
	 * @depends testDownloadJSONToFile
	 * @dataProvider getFilesList
	 */
	public function _testDownloadToFile($fileName) {
		$Request = new Request(self::$_urlTestFiles.$fileName);
		$Request->downloadToFile('/upload/obx.core/test/'.$fileName);
	}

	/**
	 * @depends testDownloadJSONToDir
	 * @dataProvider getFilesList
	 */
	public function _testDownloadToDir($fileName) {
		$Request = new Request(self::$_urlTestFiles.$fileName);
		$Request->downloadToDir('/upload/obx.core/test');
	}



	/**
	 * @depends testDownloadToFile
	 */
	public function _download404() {

	}


	public function _testDownloadUrlToFile() {
		$bSuccess = Request::downloadUrlToFile(
			self::$_urlJSON.'&test=testDownloadUrlToFile&download=Y',
			'/upload/obx.core/test/testDownloadUrlToFile.json'
		);
	}
	public function _testDownloadUrlToDir() {
		$bSuccess = Request::downloadUrlToDir(
			self::$_urlJSON.'&test=testDownloadUrlToFile&download=Y',
			'/upload/obx.core/test'
		);
	}

	public function _testSaveContentToFile() {

	}

	public function _testSaveContentToDir() {

	}

	public function _testMultiDownload() {
		$MultiRequest = new MultiRequest();
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=1');
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=2');
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=3');
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=4');
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=5');
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=6');
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=7');
		$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload&download=Y&req=8');
		$MultiRequest->downloadToDir('/upload/obx.core/test');
	}

	public function _testMultiRequestAdd() {
		$MultiRequest = new MultiRequest();
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=1'));
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=2'));
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=3'));
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=4'));
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=5'));
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=6'));
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=7'));
		$MultiRequest->addRequest(new Request(self::$_urlJSON.'&test=testMultiRequestAdd&req=8'));
		$arRequestList = $MultiRequest->getRequestList();
		$MultiRequest->exec();
		/** @var REquest $Request */
		foreach($arRequestList as $Request) {
			$header = $Request->getHeader(false);
			$arHeader = $Request->getHeader();
			$body = $Request->getBody();
		}
	}
}