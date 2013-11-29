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

	static public function getCurDir() {
		return __DIR__;
	}

	public function testDownload() {
		$iSteps = 0;
		do {
			Download::_clearInstanceCache();
			$Download = Download::getInstance(self::$_urlBigFile);
			$Download->setTimeLimit(5);
			$Download->loadFile();
			$iSteps++;
		}
		while(!$Download->isFinished());
	}

	public function testDownloadOnOneInstance() {
		$iSteps = 0;
		$Download = Download::getInstance(self::$_urlBigFile);
		$Download->setTimeLimit(5);
		do {
			$Download->loadFile();
			$iSteps++;
		}
		while($Download->isFinished());
	}

	public function testLoadTwiceOnOneInstance() {

	}
}
 