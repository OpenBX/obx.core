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
use OBX\Core\Http\Exceptions\RequestError;


/**
 * Class Request
 * @package OBX\Core\Http
 * Классслужит для загрузки данных черех HTTP
 * Класс обрабатывает одну ссылку.
 * Содержимое возможно сохранить в файл или получить в виде строки
 */
class Request {

	const DEFAULT_TIMEOUT = 10000;
	const DEFAULT_WAITING = 10000;
	const DOWNLOAD_FILE_EXT = '.dwn';
	const DOWNLOAD_FOLDER = '/bitrix/tmp/obx.core';
	static $_bDefaultDwnDirChecked = false;

	protected $_url = null;
	protected $_curlHandler = null;

	protected $_header = null;
	protected $_body = null;
	protected $_receivedCode = null;
	protected $_arHeader = array();

	protected $_dwnDir = null;
	protected $_dwnFileHandler = null;
	protected $_dwnName = null;
	protected $_saveRelPath = null;
	protected $_savePath = null;
	protected $_bDownloadComplete = false;
	protected $_bRequestComplete = false;

	protected $_maxRedirects = 5;
	protected $_bApplyServerCookie = false;

	protected $_lastCurlError = null;
	protected $_lastCurlErrNo = null;
	protected $_contentType = null;
	protected $_contentCharset = null;


	static protected $_arMimeExt = array(
		// images
		'image/x-icon' => 'ico',
		'image/png' => 'png',
		'image/jpeg' => 'jpg',
		'image/gif' => 'gif',
		'image/x-tiff' => 'tiff',
		'image/tiff' => 'tiff',
		'image/svg+xml' => 'svg',
		'application/pcx' => 'pcx',
		'image/x-bmp' => 'bmp',
		'image/x-MS-bmp' => 'bmp',
		'image/x-ms-bmp' => 'bmp',

		//compressed types
		'application/x-rar-compressed' => 'rar',
		'application/x-rar' => 'rar',
		'application/x-tar' => 'tar',
		'application/x-bzip2' => 'bz2',
		'application/x-bzip-compressed-tar' => 'tar.bz2',
		'application/x-bzip2-compressed-tar' => 'tar.bz2',
		'application/zip' => 'zip',
		'application/x-gzip' => 'gz',
		'application/x-gzip-compressed-tar' => 'tar.gz',
		'application/x-xz' => 'xz',

		// text
		'application/json' => 'json',
		'text/html' => 'html',
		'text/plain' => 'txt',

		//doc
		//open docs
		'application/vnd.oasis.opendocument.text' => 'odt',
		'application/vnd.oasis.opendocument.spreadsheet' => 'pds',
		'application/vnd.oasis.opendocument.presentation' => 'odp',
		'application/vnd.oasis.opendocument.graphics' => 'odg',
		'application/vnd.oasis.opendocument.chart' => 'odc',
		'application/vnd.oasis.opendocument.formula' => 'odf',
		'application/vnd.oasis.opendocument.image' => 'odi',
		'application/vnd.oasis.opendocument.text-master' => 'odm',
		'application/vnd.oasis.opendocument.text-template' => 'ott',
		'application/vnd.oasis.opendocument.spreadsheet-template' => 'ots',
		'application/vnd.oasis.opendocument.presentation-template' => 'otp',
		'application/vnd.oasis.opendocument.graphics-template' => 'otg',
		'application/vnd.oasis.opendocument.chart-template' => 'otc',
		'application/vnd.oasis.opendocument.formula-template' => 'otf',
		'application/vnd.oasis.opendocument.image-template' => 'oti',
		'application/vnd.oasis.opendocument.text-web' => 'oth',
		//prop docs
		'application/rtf' => 'rtf',
		'application/pdf' => 'pdf',
		'application/postscript' => 'ps',
		'application/x-dvi' => 'dvi',
		'application/msword' => 'doc',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
		'application/vnd.ms-powerpoint' => 'ppt',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		'application/vnd.ms-excel' => 'xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',

		//Video
		'video/mpeg' => 'mpg',
		'video/x-mpeg' => 'mpg',
		'video/sgi-movie' => 'movi',
		'video/x-sgi-movie' => 'movi',
		'video/msvideo' => 'avi',
		'video/x-msvideo' => 'avi',
		'video/fli' => 'fli',
		'video/x-fli' => 'fli',
		'video/quicktime' => 'mov',
		'video/x-quicktime' => 'mov',
		'application/x-shockwave-flash' => 'swf',
		'video/x-ms-wmv' => 'wmv',
		'video/x-ms-asf' => 'asf',

		//Audio
		'audio/midi' => 'midi',
		'audio/x-midi' => 'midi',
		'audio/mod' => 'mod',
		'audio/x-mod' => 'mod',
		'audio/mpeg3' => 'mp3',
		'audio/x-mpeg3' => 'mp3',
		'audio/mpeg-url' => 'mp3',
		'audio/x-mpeg-url' => 'mp3',
		'audio/mpeg2' => 'mp2',
		'audio/x-mpeg2' => 'mp2',
		'audio/mpeg' => 'mpa',
		'audio/x-mpeg' => 'mpa',
		'audio/wav' => 'wav',
		'audio/x-wav' => 'wav',
		'audio/flac' => 'flac',
		'audio/x-ogg' => 'ogg'
	);

	public function __construct($url) {
		RequestError::checkCURL();
		self::_checkDefaultDwnDir();
		$this->_curlHandler = curl_init();
		$this->setTimeout(static::DEFAULT_TIMEOUT);
		$this->setWaiting(static::DEFAULT_WAITING);
		$this->_dwnDir = $_SERVER['DOCUMENT_ROOT'].static::DOWNLOAD_FOLDER;
		$this->_url = $url;
		curl_setopt($this->_curlHandler, CURLOPT_URL, $this->_url);
		curl_setopt($this->_curlHandler, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->_curlHandler, CURLOPT_MAXREDIRS, $this->_maxRedirects);
	}

	protected function _resetCURL() {
		curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, true);
		//curl_setopt($this->_curlHandler, CURLOPT_HTTPHEADER, array('Range: bytes=0-0'));
	}
	public function __destruct() {
		if($this->_bDownloadComplete === true) {
			if($this->_dwnFileHandler != null) {
				fclose($this->_dwnFileHandler);
				$this->_dwnFileHandler = null;
			}
			unlink($this->_dwnDir.'/'.$this->_dwnName.static::DOWNLOAD_FILE_EXT);
		}
		curl_close($this->_curlHandler);
	}
	protected function __clone() {}

	static protected function _checkDefaultDwnDir() {
		if( false === static::$_bDefaultDwnDirChecked ) {
			if( !CheckDirPath($_SERVER['DOCUMENT_ROOT'].static::DOWNLOAD_FOLDER) ) {
				throw new RequestError('', RequestError::E_NO_ACCESS_DWN_FOLDER);
			}
			static::$_bDefaultDwnDirChecked = true;
		}
	}

	public function checkUrl() {

	}

	public function & getHandler() {
		return $this->_curlHandler;
	}

	public function setTimeout($milliseconds) {
		$milliseconds = intval($milliseconds);
		curl_setopt($this->_curlHandler, CURLOPT_CONNECTTIMEOUT_MS, $milliseconds);
	}

	public function setWaiting($milliseconds) {
		$milliseconds = intval($milliseconds);
		curl_setopt($this->_curlHandler, CURLOPT_TIMEOUT_MS, $milliseconds);
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
	public function setDownloadDir($downloadFolder) {
		$downloadFolder = rtrim(str_replace(array('\\', '//'), '/', $downloadFolder), '/');
		if($downloadFolder != static::DOWNLOAD_FOLDER) {
			return false;
		}
		if( !CheckDirPath($_SERVER['DOCUMENT_ROOT'].$downloadFolder) ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		$this->_dwnDir = $_SERVER['DOCUMENT_ROOT'].$downloadFolder;
		return true;
	}

	public function setPost($arPOST) {
		curl_setopt($this->_curlHandler, CURLOPT_POST, true);
		$postQuery = self::arrayToCurlPost($arPOST);
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
				$postQuery .= (($bFirst)?'':'&').$field.'='.urlencode($value);
			}
			$bFirst = false;
		}
		return $postQuery;
	}

	protected function _exec() {
		$response = curl_exec($this->_curlHandler);
		return $response;
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

	public function send() {
		$this->_resetCURL();
		curl_setopt($this->_curlHandler, CURLOPT_NOBODY, false);
		$response = $this->_exec();
		$this->_parseResponse($response);
		$this->_arHeader = $this->parseHeader($this->_header);
		if($this->_arHeader['CHARSET'] !== null) {
			$this->_contentCharset = $this->_arHeader['CHARSET'];
		}
		if( !empty($this->_arHeader['Content-Type']['VALUE_MAIN']) ) {
			$this->_contentType = $this->_arHeader['Content-Type']['VALUE_MAIN'];
		}
		$this->_setRequestComplete();
		return $this->_body;
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

	public function requestHeader($bReturnRawHeader = false) {
		$this->_resetCURL();
		curl_setopt($this->_curlHandler, CURLOPT_NOBODY, true);
		$this->_header = $this->_exec();
		$this->_arHeader = self::parseHeader($this->_header);
		if(true === $bReturnRawHeader) {
			return $this->_header;
		}
		else {
			return $this->_arHeader;
		}
	}

	public function _initDownload() {
		if($this->_bDownloadComplete === true) {
			return true;
		}
		if(null === $this->_dwnDir) {
			$this->setDownloadDir(static::DOWNLOAD_FOLDER);
		}
		if(null === $this->_dwnName) {
			$this->_dwnName = md5(time().'_'.rand(0, 9999));
		}
		$this->_dwnFileHandler = fopen($this->_dwnDir.'/'.$this->_dwnName.static::DOWNLOAD_FILE_EXT, 'w');
		if( !$this->_dwnFileHandler ) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, false);
		curl_setopt($this->_curlHandler, CURLOPT_FILE, $this->_dwnFileHandler);
	}
	public function download() {
		$this->_initDownload();
		$this->_exec();
		$this->_setDownloadComplete();
	}

	public function saveToFile($relPath) {
		$relPath = str_replace(array('\\', '//'), '/', $relPath);
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		if( !CheckDirPath($path) ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		if( $this->_bDownloadComplete === true ) {
			fclose($this->_dwnFileHandler);
			$this->_dwnFileHandler = null;
			curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
			copy($this->_dwnDir.'/'.$this->_dwnName.static::DOWNLOAD_FILE_EXT, $path);
		}
		elseif($this->_bRequestComplete === true) {
			file_put_contents($path, $this->_body);
		}
	}

	public function saveToDir($relPath){
		$relPath = str_replace(array('\\', '//'), '/', $relPath);
		$relPath = rtrim($relPath, '/');
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		if( !CheckDirPath($path.'/') ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		if( $this->_bDownloadComplete === true ) {
			fclose($this->_dwnFileHandler);
			$this->_dwnFileHandler = null;
			//определяем расширение имени файла
			$contentType = $this->getContentType();
			if(array_key_exists($contentType, static::$_arMimeExt)) {
				$fileName = $this->_dwnName.'.'.static::$_arMimeExt[$contentType];
			}
			else {
				$fileName = $this->_dwnName.static::DOWNLOAD_FILE_EXT;
			}
			$this->_saveRelPath = $relPath.'/'.$fileName;
			$this->_savePath = $path.'/'.$fileName;
			copy($this->_dwnDir.'/'.$this->_dwnName.static::DOWNLOAD_FILE_EXT, $this->_savePath);
			curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
		}
		elseif($this->_bRequestComplete === true) {
			$arHeader = $this->getHeader();
			$contentType = $this->getContentType();
			//Определим имя файла
			if( array_key_exists('Content-Disposition', $arHeader)
				&& array_key_exists('OPTIONS', $arHeader['Content-Disposition'])
				&& array_key_exists('filename', $arHeader['Content-Disposition']['OPTIONS'])
				&& !empty($arHeader['Content-Disposition']['OPTIONS']['filename'])
			) {
				$fileName = $arHeader['Content-Disposition']['OPTIONS']['filename'];
			}
			else {
				if(array_key_exists($arHeader, static::$_arMimeExt)) {
					$fileName = $this->_dwnName.'.'.static::$_arMimeExt[$contentType];
				}
				else {
					$fileName = $this->_dwnName.static::DOWNLOAD_FILE_EXT;
				}
			}
			$this->_saveRelPath = $relPath.'/'.$fileName;
			$this->_savePath = $path.'/'.$fileName;
			file_put_contents($this->_savePath, $this->_body);
		}
	}
	public function _setDownloadComplete($bComplete = true) {
		$this->_bDownloadComplete = ($bComplete!==false)?true:false;
	}
	public function _setRequestComplete($bComplete = true) {
		$this->_bRequestComplete = ($bComplete!==false)?true:false;
	}

	public function downloadToFile($relPath) {
		$this->_initDownload();
		$this->_exec();
		$this->_setDownloadComplete(true);
		$this->saveToFile($relPath);
	}

	public function downloadToDir($relPath) {
		$this->_initDownload();
		$this->_exec();
		$this->_setDownloadComplete(true);
		$this->saveToDir($relPath);
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
					&& array_key_exists('CHARSET', $arHeader['Content-Type'])
					&& $arHeader['Content-Type']['CHARSET'] != null
				) {
					$this->_contentCharset = $arHeader['Content-Type']['CHARSET'];
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

	public function getInfo($curlOpt = null){
		return curl_getinfo($this->_curlHandler, $curlOpt);
	}

	public function saveToBXFile($filePath) {

	}

	public function saveToIBElement($IBLOCK, $target) {

	}



	static public function getMimeExtList() {
		return array(
			 'image/jpeg'		=> 'jpg'
			,'image/png'		=> 'png'
			,'image/gif'		=> 'gif'
			,'text/html'		=> 'html'
			,'text/xml'			=> 'xml'
			,'application/json'	=> 'json'
			,'text/json'		=> 'json'
		);
	}
} 