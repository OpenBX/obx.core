<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Http;
use OBX\Core\CMessagePoolDecorator;
use OBX\Core\Http\Exceptions\DownloadError;
use OBX\Core\Mime;

IncludeModuleLangFile(__FILE__);

/**
 * Class Download
 * @package OBX\Core\Http
 * Класс для пошагового скачивания файлов
 */
class Download extends CMessagePoolDecorator {

	const DEF_TIME_LIMIT = 25;
	const DEF_TIME_CONN_OUT = 10;
	const DEF_USER_AGENT = 'OpenBX downloader';
	const DEF_DWN_FOLDER = '/bitrix/tmp/obx.core/downloads';
	const DEF_DWN_EXT = 'dwn';
	const DEF_BUFFER_SIZE = 4194304; // 4Mb

	static protected $_arInstances = array();
	static protected $_bDefaultDwnDirChecked = false;

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
	protected $_dwnStateFilePath = null;
	protected $_buffer = null;
	protected $_bufferSize = null;
	protected $_bufferLoaded = 0;
	protected $_fileLoaded = 0;
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
		$this->_buildRequestHeader();
	}
	protected function __clone() {}
	function __destruct() {}

	/**
	 * @throws Exceptions\DownloadError
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

	}

	public function setConnectionTimeOut($seconds){

	}

	public function setUserAgent($userAgent) {
		$this->_buildRequestHeader();
	}

	public function setProxy($proxyAddr, $proxyPort, $proxyUserName, $proxyPassword) {
		$this->_buildRequestHeader();
	}

	public function getHeaders() {
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
		$this->_header = $header;
		$this->_arHeader = Request::parseHeader($header);
		if( array_key_exists('Accept-Ranges', $this->_arHeader)
			&& $this->_arHeader['Accept-Ranges']['VALUES'] == 'bytes'
		) {
			$this->_bCanUseRanges = true;
		}
		if( !empty($this->_arHeader['Content-Type']['VALUE_MAIN']) ) {
			$this->_contentType = $this->_arHeader['Content-Type']['VALUE_MAIN'];
			$this->_dwnFileExt = Mime::getFileExt($this->_contentType, static::DEF_DWN_EXT);
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
		$arReplyHeader = array();
		$downloadsize = 0;
		// stream_set_blocking($this->_socket, 0); :)
		$bHeaderRead = false;
		$header = '';
		while( ! ($this->_bComplete = feof($this->_socket)) ) {
			$result = fread($this->_socket, 256*1024);
			if(false === $bHeaderRead) {
				$header .= $result;
				if(strpos($header, "\r\n\r\n") !== false) {
					$posHSplit = strrpos($header, "\r\n\r\n");
					$result = substr($header, $posHSplit+4);
					$header = substr($header, 0, $posHSplit+2);
					$this->_headerSize = strlen($header);
					$this->_readHeaderData($header);
					$bHeaderRead = true;
				}
				else {
					continue;
				}
			}
			$this->_writeResult($result);
		}

		return $this->_bComplete;
	}

	protected function _writeResult(&$result) {
		$iterationSize = strlen($result);
		$this->_fileLoaded += $iterationSize;
		$this->_bufferLoaded += $iterationSize;
		$this->_buffer .= $result;
		if($this->_bufferLoaded >= $this->_bufferSize) {
			if(!$this->_dwnFileHandler) {
				$this->_dwnFileHandler = fopen(
					OBX_DOC_ROOT.$this->_dwnFolder.'/'.$this->_dwnFileBaseName.'.'.$this->_dwnFileExt, 'ab'
				);
				if( !$this->_dwnFileHandler ) {
					$this->throwErrorException(new DownloadError('', DownloadError::E_CANT_OPEN_DWN_FILE));
				}
			}
			$bytesWritten = fwrite($this->_dwnFileHandler, $this->_buffer);
			if(false === $bytesWritten) {
				$this->throwErrorException(new DownloadError('', DownloadError::E_CANT_WRT_2_DWN_FILE));
			}
			$this->_buffer = null;
			$this->_bufferLoaded = 0;
			$this->_saveStepToFile();
		}
	}

	protected function _loadStepFromFile() {

	}

	protected function _saveStepToFile() {
		$arStateJson = array(
			'url' => $this->_url,
			'dwnName' => $this->_dwnName,
			'dwnFolder' => $this->_dwnFolder,
			'dwnFileBaseName' => $this->_dwnFileBaseName,
			'dwnFileExt' => $this->_dwnFileExt,
			'fileLoaded' => $this->_fileLoaded,
			'contentExpectedSize' => $this->_contentExpectedSize
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
}
