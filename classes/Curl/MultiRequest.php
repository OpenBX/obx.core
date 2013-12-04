<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Curl;
use OBX\Core\CMessagePoolDecorator;
use OBX\Core\Curl\Exceptions\RequestError;
use OBX\Core\Exceptions\LogFileError;
use OBX\Core\LogFile;

class MultiRequest extends CMessagePoolDecorator {

	protected $_curlMulti = null;
	protected $_arRequestList = array();
	protected $_iRequest = 0;
	protected $_timeout = 0;
	protected $_waiting = 0;
	protected $_bAllRequestsSuccess = false;
	protected $_bAllDownloadsSuccess = false;
	protected $_bDownloadsComplete = false;
	protected $_bRequestsComplete = false;

	public function __construct() {
		$this->_curlMulti = curl_multi_init();
	}
	protected function __clone() {}

	public function __destruct() {
		$this->_arRequestList = array();
		foreach($this->_arRequestList as $Request) {
			/** @var Request $Request */
			$curlHandler = &$Request->getCurlHandler();
			curl_multi_remove_handle($this->_curlMulti, $curlHandler);
		}
		$this->_arRequestList = null;
		curl_multi_close($this->_curlMulti);
	}

	static public function generateMultiDownloadName() {
		return md5(__CLASS__.time().'_'.rand(0, 9999));
	}

	public function addUrl($url) {
		try {
			$Request = new Request($url);
			$bSuccess = $this->addRequest($Request);
		}
		catch(RequestError $e) {
			$this->addError($e->getMessage(), $e->getCode());
			return false;
		}
		return $bSuccess;
	}

	public function addRequest(Request $Request) {
		if( !($Request instanceof Request) ) {
			return false;
		}
		$this->_arRequestList[$this->_iRequest] = $Request;
		curl_multi_add_handle($this->_curlMulti, $Request->getCurlHandler());
		$this->_iRequest++;
		return true;
	}

	public function setWaiting($seconds) {
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->setWaiting($seconds);
		}
	}
	public function getWaiting() {
		return $this->_waiting;
	}

	public function setTimeout($seconds) {
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->setTimeout($seconds);
		}
	}
	public function getTimeout() {
		return $this->_timeout;
	}

	/**
	 * @param LogFile $LogFile
	 * @return bool
	 */
	public function setLogFile(LogFile $LogFile) {
		return $this->getMessagePool()->registerLogFile($LogFile);
	}

	public function setLogFilePath($relFilePath) {
		try {
			$LogFile = new LogFile('Multi Request Client (cURL)', $relFilePath);
			return $this->getMessagePool()->registerLogFile($LogFile);
		}
		catch(LogFileError $e) {
			$this->addError($e->getMessage(), $e->getCode());
		}
		return false;
	}

	public function getRequestList() {
		return $this->_arRequestList;
	}

	public function download() {
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->_initDownload();
		}
		$this->_exec();
		foreach($this->_arRequestList as $Request) {
			$Request->_afterDownload($this->getMessagePool());
			//$this->_bAllDownloadsSuccess = $Request->isDownloadSuccess() && $this->_bAllDownloadsSuccess;
		}
		$this->_bDownloadsComplete = true;
	}

	public function downloadToDir($relPath, $fileNameMode = Request::SAVE_TO_DIR_GENERATE) {
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->_initDownload();
		}
		$this->_exec();
		foreach($this->_arRequestList as $Request) {
			$Request->_afterDownload($this->getMessagePool());
			//$this->_bAllDownloadsSuccess = $Request->isDownloadSuccess() && $this->_bAllDownloadsSuccess;
			$Request->saveToDir($relPath, $fileNameMode);
		}
		$this->_bDownloadsComplete = true;
	}

	protected function _exec() {
		$countRunning = 0;
		curl_multi_exec($this->_curlMulti, $countRunning);
		do {
			curl_multi_exec($this->_curlMulti, $countRunning);
			usleep(50);
		} while($countRunning>0);
	}

	/**
	 * @param bool $bReturnResponse
	 * @return array
	 */
	public function send($bReturnResponse = false) {
		$arResponseList = array();
		foreach($this->_arRequestList as $reqNo => &$Request) {
			/** @var Request $Request */
			$Request->_initSend();
		}

		$this->_exec();

		foreach($this->_arRequestList as $reqNo => &$Request) {
			/** @var Request $Request */
			$response = curl_multi_getcontent($Request->getCurlHandler());
			$Request->_afterSend($response, $this->getMessagePool());
			if( $bReturnResponse !== false ) {
				$arResponseList[$reqNo] = $response;
			}
			//$this->_bAllRequestsSuccess = $Request->isRequestSuccess() && $this->_bAllRequestsSuccess;
		}
		$this->_bRequestsComplete = true;
		return $arResponseList;
	}

	public function saveToDir($relPath, $saveMode = Request::SAVE_TO_DIR_GENERATE) {
		foreach($this->_arRequestList as $reqNo => &$Request) {
			/** @var Request $Request */
			try {
				$Request->saveToDir($relPath, $saveMode);
			}
			catch(RequestError $e) {
				$this->addError($e->getMessage(), $e->getCode());
			}
		}
	}

}
