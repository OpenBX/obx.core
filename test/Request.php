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
use OBX\Core\Curl\MultiRequest;
use OBX\Core\Exceptions\Curl\CurlError;
use OBX\Core\Exceptions\Curl\RequestError;
use OBX\Core\SimpleBenchMark;

require_once __DIR__.'/_Request.php';

class TestRequest extends _Request {
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
		//$expectedQuery = 'key1=val1&arr1[key11]=val11&arr1[key12]=val12&arr2[key21]=val21&arr2[arr22][key221]=val221&key3=val3';
		$expectedQuery = http_build_query($arPOST);

		$actualQuery = Request::arrayToCurlPost($arPOST);
		$this->assertEquals($expectedQuery, $actualQuery);
	}

	public function testGetContent() {
		$Request = new Request(self::$_urlJSON.'&test=testGetContent');
		$Request->setPost(array(
			'key1' => 'val1'
		));
		$body = $Request->send();
		$Request->getHeader();
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
		$Request->saveToFile('/upload/obx.core/test/Request/toFile/testSaveContentToFile.json');
		$this->assertFileExists(self::$_docRoot.'/upload/obx.core/test/Request/toFile/testSaveContentToFile.json');
		$fileContent = file_get_contents(self::$_docRoot.'/upload/obx.core/test/Request/toFile/testSaveContentToFile.json');
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
		$Request->saveToDir('/upload/obx.core/test/Request/toDir/', Request::SAVE_TO_DIR_REPLACE);
		$this->assertFileExists(self::$_docRoot.'/upload/obx.core/test/Request/toDir/testSaveContentToDir.json');
		$fileContent = file_get_contents(self::$_docRoot.'/upload/obx.core/test/Request/toDir/testSaveContentToDir.json');

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
		$Request->send();
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
		$Request->downloadToFile('/upload/obx.core/test/Request/toFile/testDownloadToFile.json');
		$this->assertEquals('application/json', $Request->getContentType());
		$this->assertEquals('UTF-8', $Request->getCharset());
	}

	public function getFileNameFromUrlData(){
		return array(
			array('https://somedom.ru/some_file.tar.gz', 'some_file.tar.gz', 'tar.gz'),
			array('https://somedom.ru/some_file.tar.xz', 'some_file.tar.xz', 'tar.xz'),
			array('https://somedom.ru/some_file.tar.lzma', 'some_file.tar.lzma', 'tar.lzma'),
			array('https://somedom.ru/some_file.tar.bz2', 'some_file.tar.bz2', 'tar.bz2'),
			array('https://somedom.ru/some_file.tgz', 'some_file.tgz', 'tgz'),
			array('https://somedom.ru/no_ext_file', 'no_ext_file', ''),
			array('https://somedom.ru/some_dir/', 'some_dir', ''),
			array('https://somedom.ru/some_req.tar.gz?somevar=someval', 'some_req.tar.gz', 'tar.gz'),
			array('https://somedom.ru/some_req.tgz?somevar=someval', 'some_req.tgz', 'tgz'),
			array('https://somedom.ru/no_ext_req?somevar=someval', 'no_ext_req', ''),
			array('https://somedom.ru/some_dir_req/?somevar=someval', 'some_dir_req', ''),
			array('https://somedom.ru/?somevar=someval', '', ''),
			array('https://somedom.ru/some%20file%20with%20space?somevar=someval', 'some file with space', ''),
		);
	}
	/** @dataProvider getFileNameFromUrlData */
	public function testGetFileNameFromUrl($url, $expectedFileName, $expectedFileExt) {
		$this->assertEquals($expectedFileName, Request::getFileNameFromUrl($url, $fileExt));
		$this->assertEquals($expectedFileExt, $fileExt);
	}

	/**
	 * @depends testDownloadJSONToFile
	 */
	public function testDownloadJSONToDir() {
		$Request = new Request(self::$_urlJSON.'&test=testDownloadToDir&download=Y');
		$Request->downloadToDir('/upload/obx.core/test/Request/toDir');
	}

	/**
	 * @depends testDownloadJSONToFile
	 * @dataProvider getFilesList
	 */
	public function testDownloadToFile($fileName) {
		$Request = new Request(self::$_urlTestFiles.$fileName);
		$Request->downloadToFile('/upload/obx.core/test/Request/toFile/'.$fileName);
	}

	/**
	 * @depends testDownloadJSONToDir
	 * @dataProvider getFilesList
	 */
	public function testDownloadToDir($fileName) {
		$Request = new Request(self::$_urlTestFiles.$fileName);
		$relPath = '/upload/obx.core/test/Request/download/gen_names/';
		$Request->downloadToDir($relPath);
		$fileFullPath = self::$_docRoot.$relPath.$Request->getSavedFileName();
		$this->assertEquals($fileFullPath, $Request->getSavedFilePath(false));
		$this->assertFileExists($fileFullPath);

		$RequestRealNames = new Request(self::$_urlTestFiles.$fileName);
		$relPath = '/upload/obx.core/test/Request/download/real_names/';
		$RequestRealNames->downloadToDir($relPath, Request::SAVE_TO_DIR_REPLACE);
		$fileFullPath = self::$_docRoot.$relPath.$RequestRealNames->getSavedFileName();
		$this->assertEquals($fileFullPath, $RequestRealNames->getSavedFilePath(false));
		$this->assertFileExists($fileFullPath);
	}

	public function testDownloadUrlToFile() {
		$relFilePath = '/upload/obx.core/test/Request/testDownloadUrlToFile.json';
		Request::downloadUrlToFile(
			self::$_urlJSON.'&test=testDownloadUrlToFile&download=Y',
			$relFilePath
		);
		$this->assertFileExists(self::$_docRoot.$relFilePath);
	}
	public function testDownloadUrlToDir() {
		$relDirPath = '/upload/obx.core/test/Request/';
		Request::downloadUrlToDir(
			self::$_urlJSON.'&test=testDownloadUrlToDir&download=Y',
			$relDirPath,
			Request::SAVE_TO_DIR_REPLACE
		);
		$this->assertFileExists(self::$_docRoot.$relDirPath.'test.response.json');
	}

	public function testLongWaiting() {

	}

	public function testMultiDownload() {
		$this->cleanPHPBuffer();
		$sleep = '';
		//$sleep = '&sleep=5';
		$MultiRequest = new MultiRequest();
		$MultiRequest->setCaching(true);
		for($i=0; $i < 100; $i++) {
			$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload'.$i.'&download=Y'.$sleep, 'testMultiDownload_'.$i);
		}
		while(true) {
			try {
				$MultiRequest->downloadToDir('/upload/obx.core/test/Request/multi_1/', Request::SAVE_TO_DIR_COUNT);
			}
			catch(CurlError $e) {
				if($e->getCode() == CurlError::E_M_TIMEOUT_REACHED) {
					continue;
				}
			}
			break;
		}
		$arRequestList = $MultiRequest->getRequestList();
		/** @var Request $Request */
		$MultiRequest->setCaching(false);
		foreach($arRequestList as $Request) {
			$savedPath = $Request->getSavedFilePath();
			$reqID = $Request->_getID(Request::_FRIEND_CLASS_LINK);
			$this->assertNotNull($savedPath, 'Error on request #'.$reqID);
			$this->assertFileExists($savedPath);
			$cacheFile = $Request->getDownloadFilePath(true);
			$cacheStateFile = $Request->getDownloadStateFilePath(true);
			$Request->__destruct();
			$this->assertFileNotExists($cacheFile);
			$this->assertFileNotExists($cacheStateFile);
		}

	}

	/**
	 * @expectedException \OBX\Core\Exceptions\Curl\CurlError
	 * @expectedExceptionCode \OBX\Core\Exceptions\Curl\CurlError::E_M_TIMEOUT_REACHED
	 */
	public function testMultiDownloadTimeout() {

		//$sleep = '';
		$sleep = '&sleep=5';
		$MultiRequest = new MultiRequest();
		for($i=0; $i < 40; $i++) {
			$MultiRequest->addUrl(self::$_urlJSON.'&test=testMultiDownload'.$i.'&download=Y'.$sleep, 'testMultiDownload_'.$i);
		}
		$MultiRequest->setTimeout(4);
		$MultiRequest->downloadToDir('/upload/obx.core/test/Request/multi_3/', Request::SAVE_TO_DIR_COUNT);
		$arRequestList = $MultiRequest->getRequestList();
		/** @var Request $Request */
		foreach($arRequestList as $Request) {
			$this->assertNull($Request->getSavedFilePath());
		}
	}

	public function testMultiDownloadFiles() {
		$arDownloadFiles = $this->getFilesList();
		$MultiRequest = new MultiRequest();
		foreach($arDownloadFiles as &$dataPropArgs) {
			$fileName = &$dataPropArgs[0];
			for($i=0; $i < 10; $i++) {
				$MultiRequest->addRequest(new Request(self::$_urlTestFiles.$fileName));
			}
		}
		//$MultiRequest->downloadToDir('/upload/obx.core/test/Request/multi_2/', Request::SAVE_TO_DIR_REPLACE);
		$MultiRequest->downloadToDir('/upload/obx.core/test/Request/multi_2/', Request::SAVE_TO_DIR_COUNT);
	}

	public function testGetContent404() {
		try {
			$Request = new Request(static::$_url404);
			$Request->send();
			$Request->saveToFile('/upload/obx.core/test/Request/404/some_404_file.json');
		}
		catch(RequestError $e) {
			$this->assertEquals(RequestError::E_FILE_SAVE_NO_RESPONSE, $e->getCode());
			$this->assertEquals(RequestError::getLangMessage(RequestError::E_FILE_SAVE_NO_RESPONSE), $e->getMessage());
		}
		$this->assertEquals('404', $Request->getStatus());
		$this->assertFileNotExists(self::$_docRoot.'/upload/obx.core/test/Request/404/some_404_file.json');
	}

	public function testDownload404() {
		try {
			$Request = new Request(static::$_url404);
			$Request->downloadToFile('/upload/obx.core/test/Request/404/404.json');
		}
		catch(RequestError $e) {
			$this->assertEquals(RequestError::E_FILE_SAVE_NO_RESPONSE, $e->getCode());
			$this->assertEquals(RequestError::getLangMessage(RequestError::E_FILE_SAVE_NO_RESPONSE), $e->getMessage());
		}
		$this->assertEquals('404', $Request->getStatus());
		$this->assertFileNotExists(self::$_docRoot.'/upload/obx.core/test/Request/404/404.json');

		$RequestAllowSave404 = new Request(static::$_url404);
		$RequestAllowSave404->setAllowSave404ToFile();
		$RequestAllowSave404->downloadToDir('/upload/obx.core/test/Request/404/', Request::SAVE_TO_DIR_COUNT);
		$this->assertEquals('404', $RequestAllowSave404->getStatus());
		$this->assertFileExists(self::$_docRoot.'/upload/obx.core/test/Request/404/test.response.json');
	}

	/**
	 * @dataProvider getFilesList
	 */
	public function testRequestCache($fileName) {
		$Request = new Request(self::$_urlTestFiles.$fileName);
		$Request->download();
		$fullFilePath = $Request->getDownloadFilePath(true);
		$this->assertFileExists($fullFilePath);
		// destructor
		unset($Request);
		$this->assertFileNotExists($fullFilePath);

		// Тут тестируем хитрую ситуацию. Включаем режим кеширования,
		// не не задаем и не получаем ID. Соответственно ID инкапулирован в класс
		// кеш очистится :)
		$Request = new Request(self::$_urlTestFiles.$fileName);
		$Request->setCaching(true);
		$Request->download();
		$fullFilePath = $Request->getDownloadFilePath(true);
		$this->assertFileExists($fullFilePath);
		// destructor
		unset($Request);
		$this->assertFileNotExists($fullFilePath);

		// А тут получим ИД, соответственно ID передан из класса и где-то известен
		// кеш очищать нельзя
		$Request = new Request(self::$_urlTestFiles.$fileName);
		$Request->setCaching(true);
		$Request->getID();
		$Request->download();
		$fullFilePath = $Request->getDownloadFilePath(true);
		$fullStateFilePath = $Request->getDownloadStateFilePath(true);
		$this->assertFileExists($fullFilePath);
		// destructor
		unset($Request);
		$this->assertFileExists($fullFilePath);
		$this->assertTrue(unlink($fullFilePath));
		$this->assertTrue(unlink($fullStateFilePath));
	}

	public function testBigFile() {
		$requestID = 'TestRequest::testBigFile';
		$Request = new Request(self::$_urlBigFile, $requestID);
		$contentExpectedSize = null;
		while(true) {
			try {
				$Request->setCaching(true, true);
				$Request->setTimeout(1);
				$Request->download();
			}
			catch(CurlError $e) {
				$this->assertEquals(CurlError::E_OPERATION_TIMEDOUT, $e->getCode());
				$downloadFilePath = $Request->getDownloadFilePath(true);
				$contentExpectedSize = $Request->getContentExpectedSize();
				unset($Request);
				$this->assertFileExists($downloadFilePath);
				$Request = new Request(self::$_urlBigFile, $requestID);
				continue;
			}
			$this->assertTrue(
				$Request->isDownloadSuccess(),
				'Error: Request::download method must throw Exception or return success'
			);
			break;
		}

		$fullFilePath = $Request->getDownloadFilePath(true);
		$fullStatePath =$Request->getDownloadStateFilePath(true);
		$this->assertFileExists($fullFilePath);
		// destructor
		unset($Request);
		$this->assertFileExists($fullFilePath);
		$this->assertTrue(unlink($fullFilePath));
		$this->assertTrue(unlink($fullStatePath));
	}

	public function testMultiBigFile() {
		$MultiRequest = new MultiRequest();
		$MultiRequest->addUrl(self::$_urlBigFile, 'testMultiBigFile');
		$MultiRequest->addUrl(self::$_urlBigFile300, 'testMultiBigFile300');
		$MultiRequest->setCaching(true);
		$MultiRequest->setTimeout(10);
		SimpleBenchMark::start('t_download');
		SimpleBenchMark::start('i_download');
		while( true ) {
			try {
				$MultiRequest->download();
			}
			catch(CurlError $e) {
				if($e->getCode() == CurlError::E_M_TIMEOUT_REACHED) {
					continue;
				}
			}
			break;
		}
		$MultiRequest->setCaching(false);
		foreach($MultiRequest->getRequestList() as $Request) {
			/** @var Request $Request */
			$filePath = $Request->getDownloadFilePath(true);
			$stateFilePath = $Request->getDownloadStateFilePath(true);
			$this->assertFileExists($filePath);
			$this->assertFileExists($stateFilePath);
			$Request->_readStateFile($urlFromState, $contentTypeFromState, $charsetFromState, $fileSizeFromState, $expectedSizeFromState);
			$this->assertEquals($fileSizeFromState, $expectedSizeFromState);
			$this->assertEquals($Request->getUrl(), $urlFromState);
			$this->assertEquals($Request->getContentType(), $contentTypeFromState);
			$this->assertEquals(intval(filesize($Request->getDownloadFilePath(true))), $fileSizeFromState);
			$Request->__destruct();
			$this->assertFileNotExists($filePath);
			$this->assertFileNotExists($stateFilePath);
		}
	}
	public function testDeleteTempData() {
		DeleteDirFilesEx(self::$_docRoot.'/upload/obx.core/test/Request');
	}
}