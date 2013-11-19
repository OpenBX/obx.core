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

	public function getCurDir(){
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

	/**
	 *
	 */
	public function testDownloadJSONToFile() {
		$Request = new Request(self::$_urlJSON.'&test=testDownloadToFile&download=Y');
		$bSuccess = $Request->downloadToFile('/upload/obx.core/test/testDownloadToFile.json');
	}

	/**
	 * @depends testDownloadJSONToFile
	 */
	public function testDownloadJSONToDir() {
		$Request = new Request(self::$_urlJSON.'&test=testDownloadToDir&download=Y');
		$Request->downloadToDir('/upload/obx.core/test');
	}

	/**
	 * @depends testDownloadJSONToFile
	 * @dataProvider getFilesList
	 */
	public function testDownloadToFile($fileName) {
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

	public function _testGetContent() {
		$Request = new Request(self::$_urlJSON.'&test=testGetContent');
		$body = $Request->send();
		$header = $Request->getHeader(false);
		$arHeader = $Request->getHeader();
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