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
use OBX\Core\CMessagePool;
use OBX\Core\Curl\Exceptions\RequestError;
use OBX\Core\Mime;

IncludeModuleLangFile(__FILE__);

/**
 * Class Request
 * @package OBX\Core\Http
 * Классслужит для загрузки данных черех HTTP
 * Класс обрабатывает одну ссылку.
 * Содержимое возможно сохранить в файл или получить в виде строки
 */
class Request {

	const DEFAULT_TIMEOUT = 20;
	const DEFAULT_WAITING = 10;
	const DOWNLOAD_FILE_EXT = 'dwn';
	const DOWNLOAD_FOLDER = '/bitrix/tmp/obx.core';

	// При сохранении файла в папку имя определяется автоматом.
	// если файл уже существует, то

	/** @const Заменить существующий файл */
	const SAVE_TO_DIR_REPLACE = 1;
	/** @const Сгенерировать новое имя, если уже есть файл с таким именем, добавить к имени счетчик */
	const SAVE_TO_DIR_COUNT = 2;
	/** @const Не определять имя, а только расширение и генерировать имя */
	const SAVE_TO_DIR_GENERATE = 3;
	static $_bDefaultDwnDirChecked = false;

	protected $_url = null;
	protected $_curlHandler = null;

	protected $_header = null;
	protected $_body = null;
	protected $_receivedCode = null;
	protected $_arHeader = array();

	protected $_dwnDir = null;
	protected $_dwnFolder = null;
	protected $_dwnFileHandler = null;
	protected $_dwnName = null;
	protected $_originalName = null;
	protected $_originalExt = null;
	protected $_saveRelPath = null;
	protected $_savePath = null;
	protected $_saveFileName = null;
	protected $_bDownloadComplete = false;
	protected $_bRequestComplete = false;

	protected $_maxRedirects = 5;
	protected $_bApplyServerCookie = false;
	protected $_bAllowSave404ToFile = false;
	protected $_timeout = 0;
	protected $_waiting = 0;

	protected $_lastCurlError = null;
	protected $_lastCurlErrNo = null;
	protected $_contentType = null;
	protected $_contentCharset = null;
	protected $_responseStatus = null;



	public function __construct($url) {
		RequestError::checkCURL();
		self::_checkDefaultDwnDir();
		$this->_dwnFolder = static::DOWNLOAD_FOLDER;
		$this->_dwnDir = $_SERVER['DOCUMENT_ROOT'].$this->_dwnFolder;
		$this->_url = $url;
		$this->_initCURL();
	}

	public function __destruct() {
		if($this->_bDownloadComplete === true) {
			if($this->_dwnFileHandler != null) {
				fclose($this->_dwnFileHandler);
				$this->_dwnFileHandler = null;
			}
			unlink($this->_dwnDir.'/'.$this->_dwnName.'.'.static::DOWNLOAD_FILE_EXT);
			if(is_dir($this->_dwnDir.'/'.$this->_dwnName)) {
				DeleteDirFilesEx($this->_dwnFolder.'/'.$this->_dwnName);
			}
		}
		curl_close($this->_curlHandler);
	}
	protected function __clone() {}

	protected function _initCURL() {
		if(null === $this->_curlHandler) {
			$this->_curlHandler = curl_init();
			$this->setTimeout(static::DEFAULT_TIMEOUT);
			$this->setWaiting(static::DEFAULT_WAITING);
			curl_setopt($this->_curlHandler, CURLOPT_URL, $this->_url);
			curl_setopt($this->_curlHandler, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->_curlHandler, CURLOPT_MAXREDIRS, $this->_maxRedirects);
		}
	}

	/**
	 * @throws Exceptions\RequestError
	 */
	static protected function _checkDefaultDwnDir() {
		if( false === static::$_bDefaultDwnDirChecked ) {
			if( ! ($bSuccess = CheckDirPath($_SERVER['DOCUMENT_ROOT'].static::DOWNLOAD_FOLDER)) ) {
				throw new RequestError('', RequestError::E_NO_ACCESS_DWN_FOLDER);
			}
			static::$_bDefaultDwnDirChecked = true;
		}
	}

	public function checkUrl() {

	}

	public function & getCurlHandler() {
		return $this->_curlHandler;
	}

	/**
	 * Максимально позволенное количество секунд для выполнения cURL-функций.
	 * @param $seconds
	 */
	public function setTimeout($seconds) {
		$seconds = intval($seconds);
		$this->_timeout = $seconds;
	}
	public function getTimeout() {
		return $this->_timeout;
	}

	/**
	 * Количество секунд ожидания при попытке соединения. Используйте 0 для бесконечного ожидания.
	 * @param $seconds
	 */
	public function setWaiting($seconds) {
		$seconds = intval($seconds);
		$this->_waiting = $seconds;
	}
	public function getWaiting() {
		return $this->_waiting;
	}

	public function setMaxRedirects($times) {
		$times = intval($times);
		if($times<=0) {
			curl_setopt($this->_curlHandler, CURLOPT_FOLLOWLOCATION, false);
			$this->_maxRedirects = 0;
		}
		else {
			$this->_maxRedirects = $times;
			curl_setopt($this->_curlHandler, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($this->_curlHandler, CURLOPT_MAXREDIRS, $this->_maxRedirects);
		}
	}
	public function getMaxRedirects() {
		return $this->_maxRedirects;
	}

	public function getDownloadDir() {
		return $this->_dwnDir;
	}

	public function getDownloadFolder() {
		return $this->_dwnFolder;
	}
	public function setDownloadDir($downloadFolder) {
		$downloadFolder = rtrim(str_replace(array('\\', '//'), '/', $downloadFolder), '/');
		if($downloadFolder == $this->_dwnFolder) {
			return false;
		}
		if( !CheckDirPath($_SERVER['DOCUMENT_ROOT'].$downloadFolder) ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		$this->_dwnDir = $_SERVER['DOCUMENT_ROOT'].$downloadFolder;
		$this->_dwnFolder = $downloadFolder;
		return true;
	}

	public function setPost($arPOST) {
		curl_setopt($this->_curlHandler, CURLOPT_POST, true);
		//$postQuery = self::arrayToCurlPost($arPOST);
		$postQuery = http_build_query($arPOST);
		curl_setopt($this->_curlHandler, CURLOPT_POSTFIELDS, $postQuery);
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param int|null $expire
	 * @param string|null $path,
	 * @param string|null $domain
	 * @param bool|null $secure
	 * @param bool|null $bHttpOnly
	 * @return bool
	 * TODO: OBX\Core\Http\Request::setCookie: Разработать, если понадобится
	 */
	public function setCookie($name, $value=null, $expire=null, $path=null, $domain=null, $secure=null, $bHttpOnly=null) {
		return true;
	}

	/**
	 * Утсанавливать cookie пришедшие в ответе сервера
	 * Требуется только для выполнения второго запрса
	 * Не работает между редиректами
	 * Если редирект CURL сам перейдет по нему не возвращая управление классу
	 * Не работает в режиме Download
	 * TODO: OBX\Core\Http\Request::setServerCookieApply: Разработать, если понадобится
	 * @param bool $bApply
	 */
	public function setServerCookieApply($bApply = true) {
		$this->_bApplyServerCookie = (true === $bApply)?true:false;
	}

	public function setAllowSave404ToFile($bAllow = true) {
		$this->_bAllowSave404ToFile = ($bAllow !== false)?true:false;
	}

	/**
	 * @param array $arPOST
	 * @param null|string $nested
	 * @return string
	 */
	static public function arrayToCurlPost(array &$arPOST, $nested = null) {
		$postQuery = '';
		$bFirst = true;
		foreach($arPOST as $field => &$value) {
			if($nested !== null) {
				$field = $nested.'['.$field.']';
			}
			if( is_array($value) ) {
				$postQuery .= (($bFirst)?'':'&').self::arrayToCurlPost($value, $field);
			}
			else {
				$postQuery .= (($bFirst)?'':'&').urlencode($field).'='.urlencode($value);
			}
			$bFirst = false;
		}
		return $postQuery;
	}


	/**
	 * @param $response
	 * @access protected
	 */
	public function _parseResponse(&$response) {
		$header_size = curl_getinfo($this->_curlHandler, CURLINFO_HEADER_SIZE);
		$this->_header = substr($response, 0, $header_size);
		$this->_body = substr($response, $header_size);
	}

	static public function parseHeader(&$header) {
		$arHeader = array(
			'COOKIES' => null,
			'CHARSET' => null
		);
		$arHeaderLinesRaw = explode("\n", $header);
		if(strpos($arHeaderLinesRaw[0], 'HTTP')) {
			$http = trim(array_shift($arHeaderLinesRaw), " \r");
		}
		$arCookiesList = array();
		foreach($arHeaderLinesRaw as &$hedaerLine) {
			$mainHeaderValue = null;
			$headerLine = trim($hedaerLine, " \r");
			$valKeyDelimPos = strpos($headerLine, ':');
			$headerKey = trim(substr($headerLine, 0, $valKeyDelimPos));
			$headerValue = trim(substr($headerLine, $valKeyDelimPos+1));
			if($headerKey == '') {
				continue;
			}
			//Если есть символ ";" значит скорее всего значение разделено на подзначения
			$arValueOptions = array();
			$bOptionsExists = false;
			if($headerKey == 'Set-Cookie') {
				if(strpos($headerValue, ';') !== false ) {
					$bOptionsExists = true;
					$arValueOptRaw = explode(';', $headerValue);
					$arCookie = array(
						'name' => '',
						'value' => '',
						'expires' => '',
						'path' => '/',
						'domain' => '',
						'secure' => '',
						'httponly' => ''
					);
					list($arCookie['name'], $arCookie['value']) = explode('=', array_shift($arValueOptRaw));
					foreach($arValueOptRaw as &$optionValueRaw) {
						list($optionKey, $optionValue) = explode('=', $optionValueRaw);
						$optionKey = trim($optionKey);
						$optionValue = trim($optionValue);
						if(array_key_exists($optionKey, $arCookie)) {
							$arCookie[$optionKey] = $optionValue;
						}
						$arCookiesList[$arCookie['name']] = $arCookie;
					}
					continue;
				}
			}
			else {
				if(strpos($headerValue, ';') !== false ) {
					$bOptionsExists = true;
					$arValueOptRaw = explode(';', $headerValue);
					$bFirstValueOption = true;
					foreach($arValueOptRaw as &$optionValueRaw) {
						list($optionKey, $optionValue) = explode('=', $optionValueRaw);
						$optionKey = trim($optionKey);
						$optionValue = trim($optionValue);
						if(true === $bFirstValueOption && $optionValue == '') {
							$mainHeaderValue = $optionKey;
						}
						else {
							$arValueOptions[$optionKey] = $optionValue;
						}
						$bFirstValueOption = false;
					}
				}
				if($headerKey == 'Content-Type') {
					if(
						true === $bOptionsExists
						&& array_key_exists('charset', $arValueOptions)
						&& strlen($arValueOptions['charset'])>0
					) {
						$arHeader['CHARSET'] = $arValueOptions['charset'];
					}
					else {
						$mainHeaderValue = $headerValue;
					}
				}
			}

			if($bOptionsExists) {
				$arHeader[$headerKey] = array(
					'VALUE' => $headerValue,
					'OPTIONS' => $arValueOptions
				);
			}
			else {
				$arHeader[$headerKey] = array(
					'VALUE' => $headerValue,
				);
			}
			if($mainHeaderValue !== null) {
				$arHeader[$headerKey]['VALUE_MAIN'] = $mainHeaderValue;
			}
			if( !empty($arCookiesList) ) {
				$arHeader['COOKIES'] = $arCookiesList;
			}
		}
		return $arHeader;
	}

	protected function _after_exec(CMessagePool $MessagePool = null) {
		$this->_lastCurlErrNo = curl_errno($this->_curlHandler);
		$this->_lastCurlError = curl_error($this->_curlHandler);
		// TODO: Реализовать русские сообщения curl_error
		if($this->_lastCurlErrNo == CURLE_OK) {
			if( !empty($this->_lastCurlError)
				&& strpos($this->_lastCurlError, 'timed out')
				&& strpos($this->_lastCurlError, 'millisec')
			) {
				if(!defined('CURLE_OPERATION_TIMEDOUT')) {
					define('CURLE_OPERATION_TIMEDOUT', 28);
				}
				$this->_lastCurlErrNo = CURLE_OPERATION_TIMEDOUT;
			}
		}
		if($this->_lastCurlErrNo == CURLE_OK) {
			switch($this->_lastCurlErrNo) {
				case CURLE_UNSUPPORTED_PROTOCOL:
					// The URL you passed to libcurl used a protocol that this libcurl does not support.
					// The support might be a compile-time option that you didn't use,
					// it can be a misspelled protocol string or just a protocol libcurl has no code for
					break;
				case CURLE_FAILED_INIT:
					// Very early initialization code failed.
					// This is likely to be an internal error or problem,
					// or a resource problem where something fundamental couldn't get done at init time.
					break;
				case CURLE_URL_MALFORMAT:
					//The URL was not properly formatted.
					break;
				case CURLE_NOT_BUILT_IN:
					// A requested feature, protocol or option was not found built-in in this libcurl
					// due to a build-time decision. This means that a feature or option was not enabled or explicitly
					// disabled when libcurl was built and in order to get it to function you have to get a rebuilt libcurl.
					break;
				case CURLE_COULDNT_RESOLVE_PROXY:
					// Couldn't resolve proxy. The given proxy host could not be resolved.
					break;
				case CURLE_COULDNT_RESOLVE_HOST:
					// Couldn't resolve host. The given remote host was not resolved.
					break;
				case CURLE_COULDNT_CONNECT:
					// Failed to connect() to host or proxy.
					break;
				case CURLE_FTP_WEIRD_SERVER_REPLY:
					// After connecting to a FTP server,
					// libcurl expects to get a certain reply back. This error code implies
					// that it got a strange or bad reply. The given remote server is probably not an OK FTP server.
					break;
				case CURLE_REMOTE_ACCESS_DENIED:
					// We were denied access to the resource given in the URL. For FTP,
					// this occurs while trying to change to the remote directory.
					break;
				case CURLE_FTP_ACCEPT_FAILED:
					// While waiting for the server to connect back when an active FTP session is used,
					// an error code was sent over the control connection or similar.
					break;
				case CURLE_FTP_WEIRD_PASS_REPLY:
					// After having sent the FTP password to the server, libcurl expects a proper reply.
					// This error code indicates that an unexpected code was returned.
					break;
				case CURLE_FTP_ACCEPT_TIMEOUT:
					// During an active FTP session while waiting for the server to connect,
					// the CURLOPT_ACCEPTTIMOUT_MS (or the internal default) timeout expired.
					break;
				case CURLE_FTP_WEIRD_PASV_REPLY:
					//libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.
					break;
				case CURLE_FTP_WEIRD_227_FORMAT:
					//FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line, this return code is passed back.
					break;
				case CURLE_FTP_CANT_GET_HOST:
					//An internal failure to lookup the host used for the new connection.
					break;
				case CURLE_FTP_COULDNT_SET_TYPE:
					//Received an error when trying to set the transfer mode to binary or ASCII.
					break;
				case CURLE_PARTIAL_FILE:
					//A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size, and then delivers data that doesn't match the previously given size.
					break;
				case CURLE_FTP_COULDNT_RETR_FILE:
					//This was either a weird reply to a 'RETR' command or a zero byte transfer complete.
					break;
				case CURLE_QUOTE_ERROR:
					//When sending custom "QUOTE" commands to the remote server, one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.
					break;
				case CURLE_HTTP_RETURNED_ERROR:
					//This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.
					break;
				case CURLE_WRITE_ERROR:
					//An error occurred when writing received data to a local file, or an error was returned to libcurl from a write callback.
					break;
				case CURLE_UPLOAD_FAILED:
					//Failed starting the upload. For FTP, the server typically denied the STOR command. The error buffer usually contains the server's explanation for this.
					break;
				case CURLE_READ_ERROR:
					//There was a problem reading a local file or an error returned by the read callback.
					break;
				case CURLE_OUT_OF_MEMORY:
					//A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.
					break;
				case CURLE_OPERATION_TIMEDOUT:
					//Operation timeout. The specified time-out period was reached according to the conditions.
					break;
				case CURLE_FTP_PORT_FAILED:
					//The FTP PORT command returned error. This mostly happens when you haven't specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.
					break;
				case CURLE_FTP_COULDNT_USE_REST:
					//The FTP REST command returned error. This should never happen if the server is sane.
					break;
				case CURLE_RANGE_ERROR:
					//The server does not support or accept range requests.
					break;
				case CURLE_HTTP_POST_ERROR:
					//This is an odd error that mainly occurs due to internal confusion.
					break;
				case CURLE_SSL_CONNECT_ERROR:
					// A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats, paths, permissions), passwords, and others.
					break;
				case CURLE_BAD_DOWNLOAD_RESUME:
					//The download could not be resumed because the specified offset was out of the file boundary.
					break;
				case CURLE_FILE_COULDNT_READ_FILE:
					//A file given with FILE:// couldn't be opened. Most likely because the file path doesn't identify an existing file. Did you check file permissions?
					break;
				case CURLE_LDAP_CANNOT_BIND:
					//LDAP cannot bind. LDAP bind operation failed.
					break;
				case CURLE_LDAP_SEARCH_FAILED:
					//LDAP search failed.
					break;
				case CURLE_FUNCTION_NOT_FOUND:
					// Function not found. A required zlib function was not found.
					break;
				case CURLE_ABORTED_BY_CALLBACK:
					// Aborted by callback. A callback returned "abort" to libcurl.
					break;
				case CURLE_BAD_FUNCTION_ARGUMENT:
					// Internal error. A function was called with a bad parameter.
					break;
				case CURLE_INTERFACE_FAILED:
					//Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections' source IP address with CURLOPT_INTERFACE.
					break;
				case CURLE_TOO_MANY_REDIRECTS:
					//Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.
					break;
				case CURLE_UNKNOWN_OPTION:
					//An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.
					break;
				case CURLE_TELNET_OPTION_SYNTAX:
					//A telnet option string was Illegally formatted.
					break;
				case CURLE_PEER_FAILED_VERIFICATION:
					//The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.
					break;
				case CURLE_GOT_NOTHING:
					//Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.
					break;
				case CURLE_SSL_ENGINE_NOTFOUND:
					//The specified crypto engine wasn't found.
					break;
				case CURLE_SSL_ENGINE_SETFAILED:
					//Failed setting the selected SSL crypto engine as default!
					break;
				case CURLE_SEND_ERROR:
					//Failed sending network data.
					break;
				case CURLE_RECV_ERROR:
					//Failure with receiving network data.
					break;
				case CURLE_SSL_CERTPROBLEM:
					//problem with the local client certificate.
					break;
				case CURLE_SSL_CIPHER:
					//Couldn't use specified cipher.
					break;
				case CURLE_SSL_CACERT:
					//Peer certificate cannot be authenticated with known CA certificates.
					break;
				case CURLE_BAD_CONTENT_ENCODING:
					//Unrecognized transfer encoding.
					break;
				case CURLE_LDAP_INVALID_URL:
					//Invalid LDAP URL.
					break;
				case CURLE_FILESIZE_EXCEEDED:
					//Maximum file size exceeded.
					break;
				case CURLE_USE_SSL_FAILED:
					//Requested FTP SSL level failed.
					break;
				case CURLE_SEND_FAIL_REWIND:
					// When doing a send operation curl had to rewind the data to retransmit, but the rewinding operation failed.
					break;
				case CURLE_SSL_ENGINE_INITFAILED:
					//Initiating the SSL Engine failed.
					break;
				case CURLE_LOGIN_DENIED:
					//The remote server denied curl to login (Added in 7.13.1)
					break;
				case CURLE_TFTP_NOTFOUND:
					//File not found on TFTP server.
					break;
				case CURLE_TFTP_PERM:
					//Permission problem on TFTP server.
					break;
				case CURLE_REMOTE_DISK_FULL:
					//Out of disk space on the server.
					break;
				case CURLE_TFTP_ILLEGAL:
					//Illegal TFTP operation.
					break;
				case CURLE_TFTP_UNKNOWNID:
					//Unknown TFTP transfer ID.
					break;
				case CURLE_REMOTE_FILE_EXISTS:
					//File already exists and will not be overwritten.
					break;
				case CURLE_TFTP_NOSUCHUSER:
					//This error should never be returned by a properly functioning TFTP server.
					break;
				case CURLE_CONV_FAILED:
					//Character conversion failed.
					break;
				case CURLE_CONV_REQD:
					//Caller must register conversion callbacks.
					break;
				case CURLE_SSL_CACERT_BADFILE:
					//Problem with reading the SSL CA cert (path? access rights?)
					break;
				case CURLE_REMOTE_FILE_NOT_FOUND:
					//The resource referenced in the URL does not exist.
					break;
				case CURLE_SSH:
					//An unspecified error occurred during the SSH session.
					break;
				case CURLE_SSL_SHUTDOWN_FAILED:
					//Failed to shut down the SSL connection.
					break;
				case CURLE_AGAIN:
					//Socket is not ready for send/recv wait till it's ready and try again. This return code is only returned from curl_easy_recv(3) and curl_easy_send(3) (Added in 7.18.2)
					break;
				case CURLE_SSL_CRL_BADFILE:
					//Failed to load CRL file (Added in 7.19.0)
					break;
				case CURLE_SSL_ISSUER_ERROR:
					//Issuer check failed (Added in 7.19.0)
					break;
				case CURLE_FTP_PRET_FAILED:
					//The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST, a custom LIST command will be sent with PRET CMD before PASV as well. (Added in 7.20.0)
					break;
				case CURLE_RTSP_CSEQ_ERROR:
					//Mismatch of RTSP CSeq numbers.
					break;
				case CURLE_RTSP_SESSION_ERROR:
					//Mismatch of RTSP Session Identifiers.
					break;
				case CURLE_FTP_BAD_FILE_LIST:
					//Unable to parse FTP file list (during FTP wildcard downloading).
					break;
				case CURLE_CHUNK_FAILED:
					//Chunk callback reported error.
					break;
				case CURLE_NO_CONNECTION_AVAILABLE:
					//(For internal use only, will never be returned by libcurl) No connection available, the session will be queued. (added in 7.30.0)
					break;
				case CURLE_OBSOLETE:
					//These error codes will never be returned. They were used in an old libcurl version and are currently unused.
					break;
			}
		}
		if( $MessagePool instanceof CMEssagePool ) {
			$MessagePool->addError($this->_lastCurlError, 'curl_'.$this->_lastCurlErrNo);
		}
	}

	public function _initSend(){
		$this->_bRequestComplete = false;
		$this->_header = null;
		$this->_arHeader = array();
		$this->_body = null;
		$this->_receivedCode = null;
		$this->_initCURL();
		curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, true);
		curl_setopt($this->_curlHandler, CURLOPT_NOBODY, false);
		curl_setopt($this->_curlHandler, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($this->_curlHandler, CURLOPT_CONNECTTIMEOUT, $this->_waiting);
	}

	public function send() {
		$this->_initSend();
		$response = curl_exec($this->_curlHandler);
		$this->_afterSend($response);
		return $this->_body;
	}
	public function _afterSend(&$response, CMessagePool $MessagePool = null){
		$this->_after_exec($MessagePool);
		$this->_parseResponse($response);
		$this->_arHeader = $this->parseHeader($this->_header);
		if($this->_arHeader['CHARSET'] !== null) {
			$this->_contentCharset = $this->_arHeader['CHARSET'];
		}
		if( !empty($this->_arHeader['Content-Type']['VALUE_MAIN']) ) {
			$this->_contentType = $this->_arHeader['Content-Type']['VALUE_MAIN'];
		}
		if($this->_lastCurlErrNo === CURLE_OK) {
			if($this->_bAllowSave404ToFile) {
				$this->_setRequestComplete();
			}
			elseif($this->getStatus() == 200) {
				$this->_setRequestComplete();
			}
		}
		if(true === $this->_bRequestComplete) {
			//Определим имя файла
			if( array_key_exists('Content-Disposition', $this->_arHeader)
				&& array_key_exists('OPTIONS', $this->_arHeader['Content-Disposition'])
				&& array_key_exists('filename', $this->_arHeader['Content-Disposition']['OPTIONS'])
				&& !empty($this->_arHeader['Content-Disposition']['OPTIONS']['filename'])
			) {
				$fileName = $this->_arHeader['Content-Disposition']['OPTIONS']['filename'];
				$dotPos = strrpos($fileName, '.');
				$this->_originalExt = '';
				if($dotPos !== false ) {
					$this->_originalExt = substr($fileName, $dotPos+1);
				}
				$this->_originalName = substr($fileName, 0, $dotPos);
			}
			else {
				$this->_fillOriginalName($this->_contentType);
			}
		}
	}

	public function getHeader($bReturnRawHeader = false) {
		if($bReturnRawHeader === false) {
			return $this->_arHeader;
		}
		return $this->_header;
	}

	public function getBody() {
		return $this->_body;
	}

	/**
	 * Отдельный запрос заголовков
	 * @param bool $bReturnRawHeader
	 * TODO: написать метод OBX\Core\Http\Request$->requestHeader()
	 */
	public function requestHeader($bReturnRawHeader = false) {

	}

	static public function generateDownloadName() {
		return md5(time().'_'.rand(0, 9999));
	}

	/**
	 * @return bool
	 * @throws Exceptions\RequestError
	 */
	public function _initDownload() {
		if($this->_bDownloadComplete === true) {
			return true;
		}
		if(null === $this->_dwnDir) {
			$this->setDownloadDir(static::DOWNLOAD_FOLDER);
		}
		if(null === $this->_dwnName) {
			$this->_dwnName = static::generateDownloadName();
		}
		$this->_dwnFileHandler = fopen($this->_dwnDir.'/'.$this->_dwnName.'.'.static::DOWNLOAD_FILE_EXT, 'wb');
		if( !$this->_dwnFileHandler ) {
			throw new RequestError(GetMessage('OBX\Core\Curl\Request::E_OPEN_DWN_FAILED'), RequestError::E_PERM_DENIED);
		}
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, false);
		curl_setopt($this->_curlHandler, CURLOPT_FILE, $this->_dwnFileHandler);
		curl_setopt($this->_curlHandler, CURLOPT_TIMEOUT, $this->_timeout);
		curl_setopt($this->_curlHandler, CURLOPT_CONNECTTIMEOUT, $this->_waiting);
	}
	public function download() {
		$this->_initDownload();
		curl_exec($this->_curlHandler);
		$this->_afterDownload();
	}

	public function _afterDownload(CMessagePool $MessagePool = null) {
		$this->_after_exec($MessagePool);
		if($this->_lastCurlErrNo === CURLE_OK) {
			if($this->_bAllowSave404ToFile) {
				$this->_setDownloadComplete();
			}
			elseif($this->getStatus() == 200) {
				$this->_setDownloadComplete();
			}
		}
		if(true === $this->_bDownloadComplete) {
			fclose($this->_dwnFileHandler);
			$this->_dwnFileHandler = null;
			$contentType = $this->getContentType();
			$this->_fillOriginalName($contentType);
		}
	}

	protected function _fillOriginalName(&$contentType) {
		$fileName = static::getFileNameFromUrl($this->_url, $fileExt, $baseName);
		if( empty($fileName) ) {
			$baseName = static::generateDownloadName();
		}
		if(empty($fileExt)) {
			$fileExt = Mime::getFileExt($contentType, static::DOWNLOAD_FILE_EXT);
		}
		else {
			switch($fileExt) {
				case 'php':
				case 'asp':
				case 'aspx':
				case 'jsp':
					$fileExt = Mime::getFileExt($contentType, static::DOWNLOAD_FILE_EXT);
			}
		}
		$this->_originalName = $baseName;
		$this->_originalExt = $fileExt;
	}

	/**
	 * @param $relPath
	 * @throws Exceptions\RequestError
	 */
	public function saveToFile($relPath) {
		$relPath = str_replace(array('\\', '//'), '/', $relPath);
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		if( !CheckDirPath($path) ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		if( $this->_bDownloadComplete === true ) {
			//fclose($this->_dwnFileHandler);
			//$this->_dwnFileHandler = null;
			curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
			if( !copy($this->_dwnDir.'/'.$this->_dwnName.'.'.static::DOWNLOAD_FILE_EXT, $path) ) {
				throw new RequestError('', RequestError::E_FILE_SAVE_FAILED);
			}
		}
		elseif($this->_bRequestComplete === true) {
			file_put_contents($path, $this->_body);
		}
	}

	/**
	 * @param $relPath
	 * @param int $fileNameMode
	 * @throws Exceptions\RequestError
	 */
	public function saveToDir($relPath, $fileNameMode = self::SAVE_TO_DIR_GENERATE){
		switch($fileNameMode) {
			case self::SAVE_TO_DIR_GENERATE:
			case self::SAVE_TO_DIR_COUNT:
			case self::SAVE_TO_DIR_REPLACE:
				break;
			default:
				$fileNameMode = self::SAVE_TO_DIR_GENERATE;
				break;
		}
		$relPath = str_replace(array('\\', '//'), '/', $relPath);
		$relPath = rtrim($relPath, '/');
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		if( !CheckDirPath($path.'/') ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		if( $this->_bDownloadComplete !== true && $this->_bRequestComplete !== true ) {
			return;
		}
		if($fileNameMode === self::SAVE_TO_DIR_GENERATE) {
			$baseName = static::generateDownloadName();
			$fileExt = $this->_originalExt;
			$fileName = $baseName.'.'.$fileExt;
		}
		else {
			$baseName = $this->_originalName;
			$fileExt = $this->_originalExt;
			$fileName = $baseName.'.'.$fileExt;
		}
		if( $fileNameMode === self::SAVE_TO_DIR_COUNT
			&& file_exists($path.'/'.$fileName)
		) {
			$arExistFiles = glob($path.'/'.$baseName.'.[0-9]*.'.$fileExt);
			if( empty($arExistFiles) ) {
				if(file_exists($path.'/'.$baseName.'.1.'.$fileExt)) {
					$baseName = static::generateDownloadName();
					$fileExt = $this->_originalExt;
					$fileName = $baseName.'.'.$fileExt;
				}
				else {
					$baseName = $baseName.'.1';
					$fileName = $baseName.'.'.$fileExt;
				}
			}
			else {
				usort($arExistFiles, 'strnatcmp');
				$lastFileName = $arExistFiles[count($arExistFiles)-1];
				$lastFileNum = substr($lastFileName, strlen($path.'/'.$baseName)+1, strrpos($lastFileName, '.'.$fileExt));
				$lastFileNum = intval($lastFileNum);
				if($lastFileNum>0) {
					$fileName = $baseName.'.'.($lastFileNum+1).'.'.$fileExt;
				}
				unset($arExistFiles);
			}
		}
		static::fixFileName($fileName);
		$this->_saveFileName = $fileName;
		$this->_saveRelPath = $relPath.'/'.$fileName;
		$this->_savePath = $path.'/'.$fileName;
		if(true === $this->_bDownloadComplete) {
			copy($this->_dwnDir.'/'.$this->_dwnName.'.'.static::DOWNLOAD_FILE_EXT, $this->_savePath);
			curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
		}
		elseif(true === $this->_bRequestComplete) {
			file_put_contents($this->_savePath, $this->_body);
		}
	}

	static protected function fixFileName(&$fileName) {
		$fileName = str_replace(array(
			'\\', '/', ':', '*', '?', '<', '>', '|', '"', "\n", "\r"
		), '', $fileName);
	}

	public function getSavedFilePath($bRelative = false) {
		if(false !== $bRelative) {
			return $this->_saveRelPath;
		}
		return $this->_savePath;
	}

	public function getSavedFileName() {
		return $this->_saveFileName;
	}

	static public function getFileNameFromUrl($url, &$fileExt = null, &$baseName = null) {
		$arUrl = parse_url($url);
		$fileName = trim(urldecode(basename($arUrl['path'])));
		static::fixFileName($fileName);
		$fileExt = '';
		$dotPos = strrpos($fileName, '.');
		if( $dotPos !== false) {
			$fileExt = strtolower(substr($fileName, $dotPos+1));
			$baseName = substr($fileName, 0, $dotPos);
			switch($fileExt) {
				case 'gz':
				case 'bz2':
				case 'bz':
				case 'xz':
				case 'lzma':
				case '7z':
					$possibleArchDotPos = strrpos(strtolower($fileName), '.tar.'.$fileExt);
					if( $possibleArchDotPos === (strlen($fileName)-strlen('.tar.'.$fileExt)) ) {
						 $fileExt = 'tar.'.$fileExt;
						$baseName = substr($fileName, 0, $possibleArchDotPos);
					}
					break;
			}
		}
		return $fileName;
	}

	protected function _setDownloadComplete($bComplete = true) {
		$this->_bDownloadComplete = ($bComplete!==false)?true:false;
	}
	protected function _setRequestComplete($bComplete = true) {
		$this->_bRequestComplete = ($bComplete!==false)?true:false;
	}

	public function downloadToFile($relPath) {
		$this->_initDownload();
		curl_exec($this->_curlHandler);
		$this->_afterDownload();
		//if($this->_sta)
		$this->saveToFile($relPath);
	}

	public function downloadToDir($relPath, $fileNameMode = self::SAVE_TO_DIR_GENERATE) {
		$this->_initDownload();
		curl_exec($this->_curlHandler);
		$this->_afterDownload();
		$this->saveToDir($relPath, $fileNameMode);
	}

	public function getContentType() {
		if($this->_contentType === null) {
			$header = curl_getinfo($this->_curlHandler, CURLINFO_CONTENT_TYPE);
			if(!empty($header)) {
				$header = 'Content-Type: '.$header."\n";
				$arHeader = self::parseHeader($header);
				if( !empty($arHeader['Content-Type']['VALUE_MAIN']) ) {
					$this->_contentType = $arHeader['Content-Type']['VALUE_MAIN'];
				}
				if( $this->_contentCharset === null
					&& array_key_exists('CHARSET', $arHeader)
					&& $arHeader['CHARSET'] != null
				) {
					$this->_contentCharset = $arHeader['CHARSET'];
				}
			}
		}
		return $this->_contentType;
	}

	public function getCharset() {
		if($this->_contentCharset === null) {
			$header = curl_getinfo($this->_curlHandler, CURLINFO_CONTENT_TYPE);
			if(!empty($header)) {
				$header = 'Content-Type: '.$header."\n";
				$arHeader = self::parseHeader($header);
				if( array_key_exists('CHARSET', $arHeader)
					&& $arHeader['CHARSET'] != null
				) {
					$this->_contentCharset = $arHeader['CHARSET'];
				}
				if( $this->_contentType === null
					&& !empty($arHeader['Content-Type']['VALUE_MAIN'])
				) {
					$this->_contentType = $arHeader['Content-Type']['VALUE_MAIN'];
				}
			}
		}
		return $this->_contentCharset;
	}

	public function getStatus() {
		if( null === $this->_responseStatus ) {
			$this->_responseStatus = curl_getinfo($this->_curlHandler, CURLINFO_HTTP_CODE);
		}
		return $this->_responseStatus;
	}

	public function getInfo($curlOpt = null){
		return curl_getinfo($this->_curlHandler, $curlOpt);
	}

	public function getCurlLastError() {
		return $this->_lastCurlError;
	}
	public function getCurlLastErrorCode() {
		return $this->_lastCurlErrNo;
	}

	static public function downloadUrlToFile($url, $fileRelPath) {
		$Request = new self($url);
		$Request->downloadToFile($fileRelPath);
	}

	static public function downloadUrlToDir($url, $dirRelPath, $fileNameMode = self::SAVE_TO_DIR_GENERATE) {
		$Request = new self($url);
		$Request->downloadToDir($dirRelPath, $fileNameMode);
	}
} 