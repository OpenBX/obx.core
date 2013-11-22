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
use OBX\Core\LogFile;
use OBX\Core\Exceptions\LogFileError;
use OBX\Core\CMessagePool;

class TestLogFile extends TestCase {
	static public function getCurDir() {
		return __DIR__;
	}

	public function testCanTOpenFile() {
		$bSuccess = CheckDirPath(self::$_docRoot.'/bitrix/tmp/obx.core/', BX_DIR_PERMISSIONS);
		//umask(0777^BX_DIR_PERMISSIONS);
		rmdir(self::$_docRoot.'/bitrix/tmp/obx.core/testPrivateDir');
		$bSuccess = mkdir(self::$_docRoot.'/bitrix/tmp/obx.core/testPrivateDir', 0000);
		$this->assertTrue($bSuccess);
		$exceptionCode = null;
		try {
			new LogFile('UnitTest testPermDenied()', '/bitrix/tmp/obx.core/testPrivateDir/test_log_file.log');
		}
		catch(LogFileError $e) {
			$exceptionCode = $e->getCode();
		}
		$this->assertEquals(LogFileError::E_CANT_OPEN, $exceptionCode);
		chmod(self::$_docRoot.'/bitrix/tmp/obx.core/testPrivateDir', 0777);
		rmdir(self::$_docRoot.'/bitrix/tmp/obx.core/testPrivateDir');
	}

	/**
	 * @expectedException \OBX\Core\Exceptions\LogFileError
	 * @expectedExceptionCode \OBX\Core\Exceptions\LogFileError::E_SENDER_IS_EMPTY
	 */
	public function testSenderIsEmpty() {
		new LogFile('', '/bitrix/tmp/obx.core/test_log_file.log');
	}

	public function testCheckLogMessages() {
		$fileRelPath = '/bitrix/tmp/obx.core/test_log_file.log';
		$filePath = self::$_docRoot.'/bitrix/tmp/obx.core/test_log_file.log';
		@unlink($filePath);
		$LogFile = new LogFile(__METHOD__, $fileRelPath, 'w');
		for($i=0; $i <= 20; $i++) {
			if($i==7) $LogFile->setDefaultMessageType(LogFile::MSG_TYPE_WARNING);
			if($i==14) $LogFile->setDefaultMessageType(LogFile::MSG_TYPE_NOTE);
			$LogFile->logMessage('test message marker#'.$i);
		}
		$this->assertFileExists($filePath);
		$fileContent = file_get_contents($filePath);
		for($i=0; $i <= 20; $i++) {
			$prefix = '';
			if($i<14) $prefix = 'Warning: ';
			if($i<7) $prefix = 'Error: ';
			$this->assertTrue((false !== strpos($fileContent, $prefix.'test message marker#'.$i)));
		}
		@unlink($filePath);
	}

	public function testInMessagePool() {
		$MPool = new CMessagePool();
		$MPool->registerLogFile(new LogFile(__METHOD__, '/bitrix/tmp/obx.core/message_pool.log', LogFile::F_APPEND));
		for($i=0; $i <= 20; $i++) {
			$MPool->addError('test error', 'test_code');
			$MPool->addWarning('test warning', 'test_code');
			$MPool->addMessage('test message', 'test_code');
		}
		$this->assertFileExists(self::$_docRoot.'/bitrix/tmp/obx.core/message_pool.log');
		unlink(self::$_docRoot.'/bitrix/tmp/obx.core/message_pool.log');
	}
}
 