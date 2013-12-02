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
use OBX\Core\Http\Download;

class TestDownloadBigFile extends TestCase {

	static protected $_urlBigFile = 'http://smokeoffice12.loc:80/bitrix/modules/obx.core/test/data/dwn_files/Pirates.Of.Silicon.Valley.rus.Lostfilm.TV.avi';
	//static protected $_urlBigFile = 'http://smokeoffice12.loc:80/bitrix/modules/obx.core/test/data/dwn_files/zero_file_300M';

	static public function getCurDir() {
		return __DIR__;
	}

	public function testDownload() {
		$iSteps = 0;
		//$progressPrint = null;
		$this->cleanPHPBuffer();
		$progress = 0;
		do {
			Download::_clearInstanceCache();
			$Download = Download::getInstance(self::$_urlBigFile);
			$Download->setTimeLimit(1);
			$Download->loadFile();
			$this->assertGreaterThan($progress, $Download->getProgress(2));
			$progress = $Download->getProgress(2);
			$this->assertLessThanOrEqual(100, $progress);
			//if($progressPrint !== null) {
			//	echo str_repeat(chr(8), strlen($progressPrint));
			//}
			//$progressPrint = $progress.'%';
			//echo $progressPrint;
			$iSteps++;
		}
		//echo "\n";
		while(!$Download->isFinished());
		$this->assertFileExists(OBX_DOC_ROOT.$Download->getDownloadFolder());
		$this->assertFileExists(OBX_DOC_ROOT.$Download->getFilePath());

		$this->assertEquals(
			$Download->getFileExpectedSize(),
			$Download->getFileLoaded()
		);
		$this->assertEquals(
			$Download->getFileLoaded(),
			filesize(OBX_DOC_ROOT.$Download->getFilePath())
		);
		$Download->saveFile('/upload/obx.core/test/Download');
		$this->assertFileExists(OBX_DOC_ROOT.'/upload/obx.core/test/Download/'.$Download->getFileName());
		$this->assertFileNotExists(OBX_DOC_ROOT.$Download->getFilePath());
		$this->assertFileNotExists(OBX_DOC_ROOT.$Download->getDownloadFolder());
	}

	public function _testDownloadOnOneInstance() {
		$iSteps = 0;
		$Download = Download::getInstance(self::$_urlBigFile);
		$Download->setTimeLimit(5);
		do {
			$Download->loadFile();
			$iSteps++;
		}
		while($Download->isFinished());
	}

	public function _testLoadTwiceOnOneInstance() {

	}
}
 