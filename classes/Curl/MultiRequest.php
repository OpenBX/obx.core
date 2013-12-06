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
use OBX\Core\Exceptions\Curl\RequestError;
use OBX\Core\Exceptions\LogFileError;
use OBX\Core\LogFile;

class MultiRequest extends CMessagePoolDecorator {

	protected $_curlMulti = null;
	protected $_arRequestList = array();
	protected $_iRequest = 0;
	protected $_iRequestsSuccess = 0;
	protected $_timeout = 0;
	protected $_waiting = 0;
	protected $_bDownloadsComplete = false;
	protected $_bRequestsComplete = false;
	const GET_DEFAULT_REQUEST_ID = -9354817;
								  //DEFAULT
	const _FRIEND_CLASS_LINK = 521389614;
							 //FRIENDCLA(SS)

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

	public function addUrl($url, $requestID = null) {
		try {
			$Request = new Request($url, $requestID);
			$bSuccess = $this->addRequest($Request);
		}
		catch(RequestError $e) {
			$this->addErrorException($e);
			$bSuccess = false;
		}
		return $bSuccess;
	}

	public function addRequest(Request $Request) {
		if( !($Request instanceof Request) ) {
			return false;
		}
		$this->_arRequestList[$Request->getID()] = $Request;
		curl_multi_add_handle($this->_curlMulti, $Request->getCurlHandler());
		$this->_iRequest++;
		return true;
	}

	public function setWaiting($seconds) {
		$this->_waiting = intval($seconds);
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->setWaiting($this->_waiting);
		}
	}
	public function getWaiting() {
		return $this->_waiting;
	}

	public function setTimeout($seconds) {
		/** @var Request $Request */
		$this->_timeout = intval($seconds);
		foreach($this->_arRequestList as $Request) {
			$Request->setTimeout($this->_timeout);
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
		if(true === $this->_bDownloadsComplete) {
			return;
		}
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			try {$Request->_initDownload(self::_FRIEND_CLASS_LINK);}
			catch(RequestError $e) {
				$this->getMessagePool()->addErrorException($e);
			}
		}
		$this->_exec();
		foreach($this->_arRequestList as $Request) {
			try { $Request->_afterDownload(self::_FRIEND_CLASS_LINK); }
			catch(RequestError $e) {
				$this->getMessagePool()->addErrorException($e);
			}
			if($Request->isDownloadSuccess()) {
				$this->_iRequestsSuccess++;
			}
		}
		$this->_bDownloadsComplete = true;
	}

	public function downloadToDir($relPath, $fileNameMode = Request::SAVE_TO_DIR_GENERATE) {
		if(true === $this->_bDownloadsComplete) {
			return;
		}
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			try {$Request->_initDownload(self::_FRIEND_CLASS_LINK);}
			catch(RequestError $e) {
				$this->getMessagePool()->addErrorException($e);
			}
		}
		$this->_exec();
		foreach($this->_arRequestList as $Request) {
			try {
				$Request->_afterDownload(self::_FRIEND_CLASS_LINK);
				$Request->saveToDir($relPath, $fileNameMode);
			}
			catch(RequestError $e) {
				$this->getMessagePool()->addErrorException($e);
			}
			if($Request->isDownloadSuccess()) {
				$this->_iRequestsSuccess++;
			}
		}
		$this->_bDownloadsComplete = true;
	}

	protected function _exec() {
		$countRunning = 0;
		$endTime = time()+$this->_timeout;
		$i = 0;
		curl_multi_exec($this->_curlMulti, $countRunning);
		if( $this->_timeout != 0) {
			do {
				$i++;
				if($i%100==0) {
					if(time() >= $endTime){
						break;
					}
					$i=0;
				}
				curl_multi_exec($this->_curlMulti, $countRunning);
				//usleep(50);
			} while($countRunning>0);
		}
		else {
			do {
				curl_multi_exec($this->_curlMulti, $countRunning);
				//usleep(10);
			} while($countRunning>0);
		}
		$debug=1;
	}

	/**
	 * @param bool $bReturnResponse
	 * @return array|null
	 */
	public function send($bReturnResponse = false) {
		$arResponseList = array();
		if(true === $this->_bRequestsComplete) {
			if( $bReturnResponse !== false ) {
				/** @var Request $Request */
				foreach($this->_arRequestList as $reqID => $Request) {
					$arResponseList[$reqID] = $Request->getBody();
				}
			}
			return $arResponseList;
		}
		foreach($this->_arRequestList as $reqID => &$Request) {
			/** @var Request $Request */
			$Request->_initSend(self::_FRIEND_CLASS_LINK);
		}

		$this->_exec();

		foreach($this->_arRequestList as $reqID => &$Request) {
			/** @var Request $Request */
			$response = curl_multi_getcontent($Request->getCurlHandler());
			$Request->_afterSend($response, self::_FRIEND_CLASS_LINK);
			if( $bReturnResponse !== false ) {
				$arResponseList[$reqID] = $response;
			}
			if($Request->isRequestSuccess()) {
				$this->_iRequestsSuccess++;
			}
		}
		$this->_bRequestsComplete = true;
		return $arResponseList;
	}

	public function saveToDir($relPath, $saveMode = Request::SAVE_TO_DIR_GENERATE) {
		foreach($this->_arRequestList as $reqID => &$Request) {
			/** @var Request $Request */
			try {
				$Request->saveToDir($relPath, $saveMode);
			}
			catch(RequestError $e) {
				$this->addWarning($e->getMessage(), $e->getCode());
			}
		}
	}

	public function getRequestsCount() {
		return $this->_iRequest;
	}

	public function getSuccessRequestsCount() {
		return $this->_iRequestsSuccess;
	}

	public function getResponseList() {
		/** @var Request $Request */
		$arResponseList = array();
		foreach($this->_arRequestList as $reqNo => $Request) {
			$arResponseList[$reqNo] = $Request->getBody();
		}
		return $arResponseList;
	}
}
