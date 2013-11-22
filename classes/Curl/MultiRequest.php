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

class MultiRequest extends CMessagePoolDecorator {

	protected $_curlMulti = null;
	protected $_arRequestList = array();
	protected $_iRequest = 0;

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

	public function setTimeout($seconds) {
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->setTimeout($seconds);
		}
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
			$Request->_after_download();

		}
	}

	public function downloadToDir($relPath, $fileNameMode = Request::SAVE_TO_DIR_GEN_ALL) {
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->_initDownload();
		}
		$this->_exec();
		foreach($this->_arRequestList as $Request) {
			$Request->_after_download();
			$Request->saveToDir($relPath, $fileNameMode);
		}
	}

	protected function _exec() {
		$countRunning = 0;
		$resE = curl_multi_exec($this->_curlMulti, $countRunning);
		do {
			$resE = curl_multi_exec($this->_curlMulti, $countRunning);
			usleep(1000);
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
			$Request->_resetCURL();
		}

		$this->_exec();

		foreach($this->_arRequestList as $reqNo => &$Request) {
			/** @var Request $Request */
			$response = curl_multi_getcontent($Request->getCurlHandler());
			$Request->_after_send($response);
			if( $bReturnResponse !== false ) {
				$arResponseList[$reqNo] = $response;
			}
		}
		return $arResponseList;
	}



	public function saveToDir($relPath) {
		foreach($this->_arRequestList as $reqNo => &$Request) {
			/** @var Request $Request */
			try {
				$Request->saveToDir($relPath);
			}
			catch(RequestError $e) {
				$this->addError($e->getMessage(), $e->getCode());
			}
		}
	}

}
