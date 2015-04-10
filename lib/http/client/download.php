<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core\Http\Client;
use OBX\Core\MessagePoolDecorator;
use OBX\Core\Exceptions\Http\DownloadError;
use OBX\Core\Mime;
use OBX\Core\Http\Request;

IncludeModuleLangFile(__FILE__);

/**
 * Class Download
 * @package OBX\Core\Http
 * Класс для пошагового скачивания файлов
 */
class Download extends MessagePoolDecorator {

	const DEF_TIME_LIMIT = 25;
	const DEF_TIME_CONN_OUT = 10;
	const DEF_USER_AGENT = 'OpenBX downloader';
	const DEF_DWN_FOLDER = '/bitrix/tmp/obx.core/downloads';
	const DEF_DWN_EXT = 'dwn';
	const DEF_BUFFER_SIZE = 4194304; // 4Mb

	static protected $_arInstances = array();
	static protected $_bDefaultDwnDirChecked = false;
	static protected $_bInitStatic = false;
	static protected $_bUTF = false;
	static protected $_bMBStringOrig = false;

	protected $_url = null;
	protected $_host = null;
	protected $_port = null;
	protected $_protocol = null;
	protected $_path = null;
	protected $_query = null;


	protected $_timeLimit = null;
	protected $_timeOutConn = null;
	protected $_userAgent = null;

	protected $_bUseProxy = false;
	protected $_proxyAddress = null;
	protected $_proxyPort = null;
	protected $_proxyUser = null;
	protected $_proxyPassword = null;

	protected $_socket = null;
	protected $_socketErrNo = null;
	protected $_socketErrStr = null;
	protected $_dwnName = null;
	protected $_dwnFolder = null;
	protected $_dwnFileHandler = null;
	protected $_dwnFileBaseName = null;
	protected $_dwnFileExt = null;
	protected $_dwnFileOpenMode = 'wb';
	protected $_dwnStateFilePath = null;
	protected $_buffer = null;
	protected $_bufferSize = null;
	protected $_bufferLoaded = 0;
	protected $_fileLoaded = 0;
	protected $_fileExpectedSize = null;
	protected $_contentExpectedSize = null;
	protected $_contentType = null;
	protected $_contentCharset = null;
	protected $_responseStatusCode = null;
	protected $_responseStatusMsg = null;
	protected $_rangeFrom = 0;
	protected $_rangeTo = 0;

	protected $_bComplete = false;
	protected $_bReadyToNewLoad = true;

	protected $_header = null;
	protected $_headerSize = null; // bytes
	protected $_arHeader = null;
	protected $_bCanUseRanges = false;

	protected $_requestHeader = '';

	protected $_htmlStatusTemplate = <<<HTML

HTML;
	protected $_currentProgress = null;


	/**
	 * @param $url
	 * @return Download
	 */
	static public function getInstance($url) {
		if( static::$_bInitStatic === false ) {
			static::_initStatic();
		}
		$urlSign = md5($url);
		if( array_key_exists($urlSign, static::$_arInstances) ) {
			return static::$_arInstances[$urlSign];
		}
		static::$_arInstances[$urlSign] = new self($url);
		return static::$_arInstances[$urlSign];
	}

	/**
	 * Метод для тестирования класса
	 */
	static public function _clearInstanceCache() {
		static::$_arInstances = array();
	}
	static protected function _initStatic() {
		if( defined('BX_UTF') ) {
			static::$_bUTF = true;
			if( function_exists('mb_orig_strpos')
				&& function_exists('mb_orig_strlen')
				&& function_exists('mb_orig_substr')
			) {
				static::$_bMBStringOrig = true;
			}
			else {
				static::$_bMBStringOrig = false;
			}
		}
	}
	protected function __construct($url, $dwnName = null) {
		$this->_timeLimit = static::DEF_TIME_LIMIT;
		$this->_userAgent = static::DEF_USER_AGENT;
		static::_checkDefaultDwnDir();
		$arUrl = parse_url($url);
		switch($arUrl['scheme']) {
			case 'http':
				if(empty($arUrl['port'])) {
					$arUrl['port'] = 80;
				}
				break;
			case 'https':
				if(empty($arUrl['port'])) {
					$arUrl['port'] = 443;
				}
				// пока выбрасываем тут исключение, пока не поддерживается https
				throw new DownloadError('', DownloadError::E_WRONG_PROTOCOL);
				break;
			default:
				throw new DownloadError('', DownloadError::E_WRONG_PROTOCOL);
		}
		$this->_url = $url;
		$this->_protocol = $arUrl['scheme'];
		$this->_host = $arUrl['host'];
		$this->_port = $arUrl['port'];
		$this->_path = ($arUrl['path'])?$arUrl['path']:'/';
		if($dwnName === null) {
			$this->_dwnName = md5($url);
		}

		if( !CheckDirPath(OBX_DOC_ROOT.static::DEF_DWN_FOLDER.'/'.$this->_dwnName.'/') ) {
			throw new DownloadError('', DownloadError::E_NO_ACCESS_DWN_FOLDER);
		}
		$this->_dwnFolder = static::DEF_DWN_FOLDER.'/'.$this->_dwnName;
		if(empty($this->_dwnFileExt)) {
			$this->_dwnFileExt = static::DEF_DWN_EXT;
		}
		$this->_dwnStateFilePath = OBX_DOC_ROOT.$this->_dwnFolder.'/download.state.json';
		$this->_timeLimit = static::DEF_TIME_LIMIT;
		$this->_timeOutConn = static::DEF_TIME_CONN_OUT;
		$this->_bufferSize = static::DEF_BUFFER_SIZE;
		$bStateLoaded = $this->_loadStepFromFile();
		$this->_dwnFileOpenMode = ($bStateLoaded===true)?'ab':'wb';
		$this->_buildRequestHeader();
	}
	protected function __clone() {}
	public function __destruct() {
		fclose($this->_dwnFileHandler);
		fclose($this->_socket);
	}

	/**
	 * @throws DownloadError
	 */
	static protected function _checkDefaultDwnDir() {
		if( false === static::$_bDefaultDwnDirChecked ) {
			$bSuccess = CheckDirPath(OBX_DOC_ROOT.static::DEF_DWN_FOLDER);
			if( ! $bSuccess ) {
				throw new DownloadError('', DownloadError::E_NO_ACCESS_DWN_FOLDER);
			}
			static::$_bDefaultDwnDirChecked = true;
		}
	}

	public function setTimeLimit($seconds) {
		$seconds = intval($seconds);
		$this->_timeLimit = $seconds;
	}

	public function setConnectionTimeOut($seconds){
		$seconds = intval($seconds);
		$this->_timeOutConn = $seconds;
	}

	public function setUserAgent($userAgent) {
		$this->_buildRequestHeader();
	}

	public function setProxy($proxyAddr, $proxyPort, $proxyUserName = null, $proxyPassword = null) {
		$this->_proxyAddress = $proxyAddr;
		$this->_proxyPort = $proxyPort;
		$this->_proxyUser = $proxyUserName;
		$this->_proxyPassword = $proxyPassword;
		$this->_bUseProxy = true;
		$this->_buildRequestHeader();
	}

	public function unsetProxy() {
		$this->_bUseProxy = false;
		$this->_proxyAddress = null;
		$this->_proxyPort = null;
		$this->_proxyUser = null;
		$this->_proxyPassword = null;
	}

	public function requestHeaders() {
		$requestHeader = '';
		if($this->_bUseProxy) {
			$requestHeader .= 'HEAD '.$this->_url." HTTP/1.0\r\n";
			$requestHeader .= 'Host: '.$this->_proxyAddress."\r\n";
			if ($this->_proxyUser)
				$requestHeader .= 'Proxy-Authorization: Basic '
					.base64_encode($this->_proxyUser.':'.$this->_proxyPassword)
					."\r\n";
		}
		else {
			$requestHeader .= "HEAD ".$this->_path.($this->_query ? '?'.$this->_query : '')
				." HTTP/1.0\r\n";
			$requestHeader .= "Host: $this->_host\r\n";
		}
		if($this->_userAgent !== null) {
			$requestHeader .= 'User-Agent: '.$this->_userAgent."\r\n";
		}
		$requestHeader .= "\r\n";
		$socket = fsockopen($this->_host, $this->_port, $errNo, $errStr, $this->_timeOutConn);
		fwrite($socket, $requestHeader);
		while (($result = fgets($socket, 4096)) && $result!="\r\n") {
			$arReplyHeader[] = $result;
		}
		fclose($socket);
		$arHeader = Request::parseHeader($arReplyHeader);
		return $arHeader;
	}

	protected function _buildRequestHeader() {
		$this->_requestHeader = '';
		if($this->_bUseProxy) {
			$this->_requestHeader .= 'GET '.$this->_path.($this->_query?$this->_query:'')." HTTP/1.0\r\n";
			$this->_requestHeader .= 'Host: '.$this->_proxyAddress."\r\n";
			if ($this->_proxyUser)
				$this->_requestHeader .= 'Proxy-Authorization: Basic '
					.base64_encode($this->_proxyUser.':'.$this->_proxyPassword)
					."\r\n";
		}
		else {
			$this->_requestHeader .= "GET ".$this->_url." HTTP/1.0\r\n";
			$this->_requestHeader .= "Host: $this->_host\r\n";
		}
		if($this->_userAgent !== null) {
			$this->_requestHeader .= 'User-Agent: '.$this->_userAgent."\r\n";
		}
		if($this->_rangeFrom > 0) {
			if($this->_rangeTo > 0) {
				$this->_requestHeader .= 'Range: bytes='.$this->_rangeFrom.'-'.$this->_rangeTo."\r\n";
			}
			else {
				$this->_requestHeader .= 'Range: bytes='.$this->_rangeFrom."-\r\n";
			}
		}
		elseif($this->_rangeTo > 0) {
			$this->_requestHeader .= 'Range: bytes=0-'.$this->_rangeTo."\r\n";
		}
		$this->_requestHeader .= "\r\n";
	}

	protected function _openConnection() {
		if(null === $this->_socket) {
			$this->_socket = fsockopen(
				$this->_host, $this->_port,
				$this->_socketErrNo, $this->_socketErrStr,
				$this->_timeOutConn);
			if(!$this->_socket) {
				$this->_socket = null;
				$this->throwErrorException(new DownloadError('', DownloadError::E_CONN_FAIL));
			}
		}
	}

	protected function _readHeaderData(&$header) {
		$Mime = Mime::getInstance();
		$this->_header = $header;
		$this->_arHeader = Request::parseHeader($header);
		if( array_key_exists('Accept-Ranges', $this->_arHeader)
			&& $this->_arHeader['Accept-Ranges']['VALUES'] == 'bytes'
		) {
			$this->_bCanUseRanges = true;
		}
		if( !empty($this->_arHeader['Content-Type']['VALUE_MAIN']) ) {
			$this->_contentType = $this->_arHeader['Content-Type']['VALUE_MAIN'];
			$this->_dwnFileExt = $Mime->getFileExt($this->_contentType, static::DEF_DWN_EXT);
			if( array_key_exists('CHARSET', $this->_arHeader) && $this->_arHeader['CHARSET'] != null ) {
				$this->_contentCharset = $this->_arHeader['CHARSET'];
			}
		}
		if( array_key_exists('Content-Disposition', $this->_arHeader)
			&& array_key_exists('OPTIONS', $this->_arHeader['Content-Disposition'])
			&& array_key_exists('filename', $this->_arHeader['Content-Disposition']['OPTIONS'])
			&& !empty($this->_arHeader['Content-Disposition']['OPTIONS']['filename'])
		) {
			$fileName = $this->_arHeader['Content-Disposition']['OPTIONS']['filename'];
			$dotPos = strrpos($fileName, '.');
			$this->_dwnFileExt = '';
			if($dotPos !== false ) {
				$this->_dwnFileExt = substr($fileName, $dotPos+1);
			}
			$this->_dwnFileBaseName = substr($fileName, 0, $dotPos);
			Request::fixFileName($this->_dwnFileBaseName);
		}
		else {
			if($this->_dwnFileBaseName === null) {
				Request::getFileNameFromUrl($this->_url, $fileExtFromUrl, $baseNameFromUrl);
				if($this->_dwnFileExt === null && !empty($fileExtFromUrl)) {
					$this->_dwnFileExt = $fileExtFromUrl;
				}
				if( !empty($baseNameFromUrl) ) {
					$this->_dwnFileBaseName = $baseNameFromUrl;
				}
				else {
					$this->_dwnFileBaseName = $this->_dwnName;
				}
			}
		}
		if(array_key_exists('Content-Length', $this->_arHeader)) {
			$this->_contentExpectedSize = $this->_arHeader['Content-Length']['VALUE'];
			if($this->_rangeFrom < 1) {
				$this->_fileExpectedSize = $this->_contentExpectedSize;
			}
		}
		$this->_responseStatusCode = $this->_arHeader['STATUS']['CODE'];
		$this->_responseStatusMsg = $this->_arHeader['STATUS']['MESSAGE'];
	}

	/**
	 * @return bool
	 */
	public function loadFile() {
		$this->_openConnection();
		fwrite($this->_socket, $this->_requestHeader);
		// stream_set_blocking($this->_socket, 0); :)
		$bHeaderRead = false;
		$header = '';
		$startTime = getmicrotime();
		while( ! ($this->_bComplete = (
					feof($this->_socket)
					|| (
						$this->_rangeTo > 0
						&& $this->_fileLoaded >= $this->_rangeTo
					)
				))
		) {
			if ($this->_timeLimit>0 && (getmicrotime()-$startTime)>$this->_timeLimit) {
				break;
			}
			$result = fread($this->_socket, 256*1024);
			if(false === $bHeaderRead) {
				$header .= $result;
				if(static::$_bMBStringOrig) {
					$posHSplit = mb_orig_strpos($header, "\r\n\r\n");
				}
				else {
					$posHSplit = strpos($header, "\r\n\r\n");
				}
				if($posHSplit !== false) {
					if(static::$_bMBStringOrig) {
						$result = mb_orig_substr($header, $posHSplit+4);
						$header = mb_orig_substr($header, 0, $posHSplit+2);
						$this->_headerSize = mb_orig_strlen($header);
					}
					else {
						$result = substr($header, $posHSplit+4);
						$header = substr($header, 0, $posHSplit+2);
						$this->_headerSize = strlen($header);
					}
					$this->_readHeaderData($header);
					$bHeaderRead = true;
				}
				else {
					continue;
				}
			}
			$this->_writeResult($result);
		}
		if($this->_bufferLoaded > 0) {
			$result = null;
			$this->_writeResult($result, true);
		}
		if( true === $this->_bComplete ) {
			@unlink($this->_dwnStateFilePath);
		}
		else {
			$this->_saveStepToFile();
		}
		fclose($this->_socket); $this->_socket = null;
		fclose($this->_dwnFileHandler); $this->_dwnFileHandler = null;
		return $this->_bComplete;
	}



	protected function _writeResult(&$result, $bForceWrite = false) {
		$this->_buffer .= $result;
		if(static::$_bMBStringOrig) {
			$this->_bufferLoaded += mb_orig_strlen($result);
		}
		else {
			$this->_bufferLoaded += strlen($result);
		}

		if(true === $bForceWrite || $this->_bufferLoaded >= $this->_bufferSize || $this->_bComplete) {
			if(!$this->_dwnFileHandler) {
				$this->_dwnFileHandler = fopen(
					OBX_DOC_ROOT.$this->_dwnFolder.'/'.$this->_dwnFileBaseName.'.'.$this->_dwnFileExt,
					$this->_dwnFileOpenMode
				);
				if( !$this->_dwnFileHandler ) {
					$this->throwErrorException(new DownloadError('', DownloadError::E_CANT_OPEN_DWN_FILE));
				}
			}
			$bytesWritten = fwrite($this->_dwnFileHandler, $this->_buffer);
			if(false === $bytesWritten || $this->_bufferLoaded !== $bytesWritten ) {
				$this->throwErrorException(new DownloadError('', DownloadError::E_CANT_WRT_2_DWN_FILE));
			}
			$this->_fileLoaded += $this->_bufferLoaded;
			$this->_buffer = null;
			$this->_bufferLoaded = 0;
		}
	}

	protected function _loadStepFromFile() {
		if( is_file($this->_dwnStateFilePath) ) {
			$stateJson = file_get_contents($this->_dwnStateFilePath);
			if(false === $stateJson) {
				return false;
			}
			$arStateJson = json_decode($stateJson, true);
			if(empty($arStateJson)) {
				return false;
			}
			if(empty($arStateJson['fileLoaded'])) {
				return false;
			}
			$arStateJson['fileLoaded'] = intval($arStateJson['fileLoaded']);
			if($arStateJson['fileLoaded'] < 1 ) {
				return false;
			}
			if(empty($arStateJson['dwnFileBaseName'])) {
				return false;
			}
			if(empty($arStateJson['dwnFileExt'])) {
				return false;
			}
			if( !is_file(OBX_DOC_ROOT.$this->_dwnFolder.'/'.$arStateJson['dwnFileBaseName'].'.'.$arStateJson['dwnFileExt']) ) {
				@unlink($this->_dwnStateFilePath);
				return false;
			}
			$this->_dwnFileBaseName = $arStateJson['dwnFileBaseName'];
			$this->_dwnFileExt = $arStateJson['dwnFileExt'];
			$this->_fileLoaded = $arStateJson['fileLoaded'];

			// filesize имеет проблемы с определением размера файла более 2Гб на 32-битных системах
			// потому этой ф-ией пользоваться не будем и проверять размер не будем
			// доверимся информации полученной из state-файла

			$this->_rangeFrom = $arStateJson['fileLoaded'];
			if(!empty($arStateJson['fileExpectedSize'])) {
				$this->_fileExpectedSize = $arStateJson['fileExpectedSize'];
			}
			return true;
		}
		return false;
	}

	protected function _saveStepToFile() {
		$arStateJson = array(
			'url' => $this->_url,
			'dwnName' => $this->_dwnName,
			'dwnFolder' => $this->_dwnFolder,
			'dwnFileBaseName' => $this->_dwnFileBaseName,
			'dwnFileExt' => $this->_dwnFileExt,
			'fileLoaded' => $this->_fileLoaded,
			'contentExpectedSize' => $this->_contentExpectedSize,
			'fileExpectedSize' => $this->_fileExpectedSize
		);
		$bytesWritten = file_put_contents($this->_dwnStateFilePath, json_encode($arStateJson));
		return ($bytesWritten !== false)?true:false;
	}

	/**
	 * @return bool
	 */
	public function isFinished() {
		return $this->_bComplete;
	}

	/**
	 * @param int $precision
	 * @return float|int|null
	 */
	public function getProgress($precision = 0) {
		if($this->_fileExpectedSize === null) {
			return null;
		}
		$precision = intval($precision);

		if($precision == 0) {
			$percent = intval(($this->_fileLoaded / $this->_fileExpectedSize) * 100);
		}
		else {
			$percent = round(($this->_fileLoaded / $this->_fileExpectedSize) * 100, $precision);
		}
		return $percent;
	}

	public function getFileExpectedSize() {
		return $this->_fileExpectedSize;
	}

	public function getContentExpectedSize() {
		return $this->_contentExpectedSize;
	}

	public function getFileLoaded() {
		return $this->_fileLoaded;
	}

	public function getFileBaseName() {
		return $this->_dwnFileBaseName;
	}

	public function getFileExt() {
		return $this->_dwnFileExt;
	}

	public function getFileName() {
		return $this->_dwnFileBaseName.'.'.$this->_dwnFileExt;
	}

	public function getFilePath() {
		return $this->_dwnFolder.'/'.$this->_dwnFileBaseName.'.'.$this->_dwnFileExt;
	}

	public function getDownloadFolder() {
		return $this->_dwnFolder;
	}

	public function saveFile($dirRelPath, $fileName = null, $bForceSave = false) {
		if( $this->_bComplete !== true && $bForceSave !== false) {
			$this->throwErrorException(new DownloadError('', DownloadError::E_CANT_SAVE_NOT_FINISHED));
			return false;
		}
		if($fileName === null) {
			$fileName = $this->getFileName();
		}
		else {
			Request::fixFileName($fileName);
		}
		if( !CheckDirPath(OBX_DOC_ROOT.$dirRelPath.'/') ) {
			$this->throwErrorException(new DownloadError('', DownloadError::E_CANT_SAVE_TO_FOLDER));
		}
		rename(
			OBX_DOC_ROOT.$this->_dwnFolder.'/'.$this->_dwnFileBaseName.'.'.$this->_dwnFileExt,
			OBX_DOC_ROOT.$dirRelPath.'/'.$fileName
		);
		DeleteDirFilesEx($this->_dwnFolder);
	}
}
