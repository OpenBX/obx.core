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
use OBX\Core\Exceptions\Curl\CurlError;
use OBX\Core\Exceptions\Curl\RequestError;
use OBX\Core\Exceptions\LogFileError;
use OBX\Core\LogFile;
use OBX\Core\SimpleBenchMark;

class MultiRequest extends CMessagePoolDecorator {

	protected $_curlMulti = null;
	protected $_arRequestList = array();
	protected $_iRequest = 0;
	protected $_iRequestsSuccess = 0;
	protected $_timeout = 0;
	protected $_timeoutRequestsMax = 0;
	protected $_waiting = 0;
	protected $_bDownloadsComplete = false;
	protected $_bRequestsComplete = false;
	protected $_bCaching = false;
	protected $_bCachingCheckFileSize = false;
	const GET_DEFAULT_REQUEST_ID = -9354817;
								  //DEFAULT
	const _FRIEND_CLASS_LINK = 521389614;
							 //FRIENDCLA(SS)

	public function __construct() {
		$this->_curlMulti = curl_multi_init();
	}
	protected function __clone() {}

	public function __destruct() {
		foreach($this->_arRequestList as $reqID => &$Request) {
			/** @var Request $Request */
			$Request->_disconnectMultiHandler($this->_curlMulti);
			unset($Request);
			$this->_arRequestList[$reqID] = null;
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
		$this->_arRequestList[$Request->_getID(self::_FRIEND_CLASS_LINK)] = $Request;
		$Request->_connectMultiHandler($this->_curlMulti);
		$Request->setCaching($this->_bCaching, $this->_bCachingCheckFileSize);
		if($this->_timeout > 0) {
			$Request->setTimeout($this->_timeout);
		}
		else {
			$requestTimeOut = $Request->getTimeout();
			if($requestTimeOut > $this->_timeoutRequestsMax) {
				$this->_timeoutRequestsMax = $Request->getTimeout();
			}
		}
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
		$this->_timeoutRequestsMax = $seconds;
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
			return true;
		}
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			try {
				$bCanDoExec = $Request->_initDownload(self::_FRIEND_CLASS_LINK);
			}
			catch(RequestError $e) {
				$bCanDoExec = false;
				$this->getMessagePool()->addErrorException($e);
			}
			if(true === $bCanDoExec) {
				$Request->_connectMultiHandler($this->_curlMulti);
			}
			else {
				$Request->_disconnectMultiHandler();
			}
		}

		$bTimeOutReached = !$this->_exec();
		$this->_iRequestsSuccess = 0;
		foreach($this->_arRequestList as $Request) {
			try {
				if( true === $Request->_isMultiHandlerConnected() ) {
					$Request->_afterDownload(self::_FRIEND_CLASS_LINK);
				}
			}
			catch(RequestError $e) {
				if($e->getCode() == CurlError::E_OPERATION_TIMEDOUT) {
					$bTimeOutReached = true;
				}
				$this->getMessagePool()->addErrorException($e);
			}
			if($Request->isDownloadSuccess()) {
				$this->_iRequestsSuccess++;
			}
		}
		if(true === $bTimeOutReached) {
			throw new CurlError('', CurlError::E_M_TIMEOUT_REACHED);
			//return false;
		}
		$this->_bDownloadsComplete = true;
		return true;
	}

	public function downloadToDir($relPath, $fileNameMode = Request::SAVE_TO_DIR_GENERATE) {
		if(true === $this->_bDownloadsComplete) {
			return true;
		}
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			try {
				$bCanDoExec = $Request->_initDownload(self::_FRIEND_CLASS_LINK);
			}
			catch(RequestError $e) {
				$bCanDoExec = false;
				$this->getMessagePool()->addErrorException($e);
			}
			if(true === $bCanDoExec) {
				$Request->_connectMultiHandler($this->_curlMulti);
			}
			else {
				$Request->_disconnectMultiHandler();
			}
		}

		$bTimeOutReached = !$this->_exec();
		$this->_iRequestsSuccess = 0;
		foreach($this->_arRequestList as $Request) {
			try {
				if( true === $Request->_isMultiHandlerConnected() ) {
					$Request->_afterDownload(self::_FRIEND_CLASS_LINK);
				}
			}
			catch(RequestError $e) {
				if($e->getCode() == CurlError::E_OPERATION_TIMEDOUT) {
					$bTimeOutReached = true;
				}
				$this->getMessagePool()->addErrorException($e);
			}
			if($Request->isDownloadSuccess()) {
				$this->_iRequestsSuccess++;
				try {
					$Request->saveToDir($relPath, $fileNameMode);
				}
				catch(RequestError $e) {
					$this->addErrorException($e);
				}
			}
		}
		if(true === $bTimeOutReached) {
			throw new CurlError('', CurlError::E_M_TIMEOUT_REACHED);
			//return false;
		}
		$this->_bDownloadsComplete = true;
		return true;
	}

	/**
	 * @return bool - true при успешном завершении, false - при таймауте
	 * @throws CurlError
	 */
	protected function _exec() {
		if($this->_timeout < 1 && $this->_timeoutRequestsMax > 0) {
			$this->setTimeout($this->_timeoutRequestsMax);
		}
		if($this->_timeout > 0) {
			$endTime = time() + $this->_timeout;
			do {
				$mrc = curl_multi_exec($this->_curlMulti, $countRunning);
			} while ($mrc === CURLM_CALL_MULTI_PERFORM);
			$i = 0;
			$maxSelectErrors = 1000;
			$iSelectErrorCount = 0;
			while ($countRunning>0) {
				$mrs = curl_multi_select($this->_curlMulti, 0.1);
				if ($mrs !== -1) {
					$iSelectErrorCount = 0;
					do {
						$mrc = curl_multi_exec($this->_curlMulti, $countRunning);
					} while ($mrc === CURLM_CALL_MULTI_PERFORM);
				}
				else {
					//http://www.php.net/manual/ru/function.curl-multi-select.php
					//Alex Palmer
					//On php 5.3.18+ be aware that curl_multi_select() may return -1 forever until you call curl_multi_exec().
					//See https://bugs.php.net/bug.php?id=63411 for more information.
					$iSelectErrorCount++;
					if($iSelectErrorCount >= $maxSelectErrors ) {
						throw new CurlError('', CurlError::E_M_SELECT_ERROR);
					}
					$mrc = curl_multi_exec($this->_curlMulti, $countRunning);
				}
				usleep(10); // малость уменьшим потребление процессора
				$i++;
				if(false && $i%100 == 0 ) {$i=0; if( time() >= $endTime) {
					return false;
				}}
			}
		}
		else {
			do {
				$mrc = curl_multi_exec($this->_curlMulti, $countRunning);
			} while ($mrc === CURLM_CALL_MULTI_PERFORM);
			$maxSelectErrors = 100;
			$iSelectErrorCount = 0;
			while ($countRunning>0) {
				$mrs = curl_multi_select($this->_curlMulti, 0.1);
				if ($mrs !== -1) {
					$iSelectErrorCount = 0;
					do {
						$mrc = curl_multi_exec($this->_curlMulti, $countRunning);
					} while ($mrc === CURLM_CALL_MULTI_PERFORM);
				}
				else {
					//http://www.php.net/manual/ru/function.curl-multi-select.php
					//Alex Palmer
					//On php 5.3.18+ be aware that curl_multi_select() may return -1 forever until you call curl_multi_exec().
					//See https://bugs.php.net/bug.php?id=63411 for more information.
					$iSelectErrorCount++;
					if($iSelectErrorCount >= $maxSelectErrors ) {
						throw new CurlError('', CurlError::E_M_SELECT_ERROR);
					}
					$mrc = curl_multi_exec($this->_curlMulti, $countRunning);
				}
				usleep(10); // малость уменьшим потребление процессора
			}
		}
		return true;
	}

	/**
	 * @param bool $bReturnResponse
	 * @return array
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

	public function setCaching($bCaching = true, $bCheckFileSize = false) {
		$this->_bCaching = ($bCaching !== false)?true:false;
		$this->_bCachingCheckFileSize = (true === $bCheckFileSize)?true:false;
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			$Request->setCaching($this->_bCaching, $this->_bCachingCheckFileSize);
		}
	}
}
