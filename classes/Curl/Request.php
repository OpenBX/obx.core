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
use OBX\Core\Exceptions\Curl\CurlError;
use OBX\Core\Exceptions\Curl\RequestError;
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
	protected $_ID = null;
	protected $_originalName = null;
	protected $_originalExt = null;
	protected $_saveRelPath = null;
	protected $_savePath = null;
	protected $_saveFileName = null;
	protected $_bDownloadSuccess = false;
	protected $_bRequestSuccess = false;

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

	protected $_bCaching = false;
	/**
	 * Информация о том содержиться ли ID Request-а где-л. кроме самого класса
	 * Если мы включаем режим кеширования, то мы не очищаем скачаныне файлы,
	 * однако если программист не задавал ID или не получал его ($this->getID())
	 * то он 100% не знает его, а значит и не сможет вновь обратиться к кешу,
	 * а соответственно и очистить его по завершении всех операций.
	 * В таком  случае кеш надо очищать в любом случае в деструкторе
	 * @var bool
	 */
	protected $_bEncapsulatedID = true;


	public function __construct($url, $requestID = null) {
		RequestError::checkCURL();
		self::_checkDefaultDwnDir();
		$this->_dwnFolder = static::DOWNLOAD_FOLDER;
		$this->_dwnDir = OBX_DOC_ROOT.$this->_dwnFolder;
		$this->_url = $url;
		$this->_bEncapsulatedID = false;
		if(null !== $requestID) {
			$requestID = trim($requestID);
			if(empty($requestID)) {
				$this->_bEncapsulatedID = true;
			}
		}
		else {
			$this->_bEncapsulatedID = true;
		}
		if($this->_bEncapsulatedID === true) {
			$this->_ID = static::generateID();
		}
		$this->_initCURL();
		$this->setTimeout(static::DEFAULT_TIMEOUT);
		$this->setWaiting(static::DEFAULT_WAITING);
	}

	public function __destruct() {
		if($this->_bDownloadSuccess === true) {
			if($this->_dwnFileHandler != null) {
				fclose($this->_dwnFileHandler);
				$this->_dwnFileHandler = null;
			}
		}
		if(is_file($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT)) {
			// см. описание переменной $this->_bEncapsulatedID
			if(false === $this->_bCaching || true === $this->_bEncapsulatedID) {
				@unlink($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT);
			}
		}
		curl_close($this->_curlHandler);
	}
	protected function __clone() {}

	public function getID() {
		if(true === $this->_bCaching && true === $this->_bEncapsulatedID) {
			$this->_bEncapsulatedID = false;
		}
		return $this->_ID;
	}

	protected function _initCURL() {
		if(null === $this->_curlHandler) {
			$this->_curlHandler = curl_init();
			curl_setopt($this->_curlHandler, CURLOPT_URL, $this->_url);
			curl_setopt($this->_curlHandler, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($this->_curlHandler, CURLOPT_MAXREDIRS, $this->_maxRedirects);
		}
	}

	/**
	 * @throws RequestError
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

	protected function _after_exec() {
		$this->_lastCurlErrNo = curl_errno($this->_curlHandler);
		$this->_lastCurlError = curl_error($this->_curlHandler);
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
		if($this->_lastCurlErrNo != CURLE_OK) {
			throw new CurlError($this->_lastCurlError, $this->_lastCurlErrNo);
		}
	}

	public function _initSend(){
		$this->_bRequestSuccess = false;
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
	public function _afterSend(&$response){
		$this->_after_exec();
		$this->_parseResponse($response);
		$this->_arHeader = $this->parseHeader($this->_header);
		if($this->_arHeader['CHARSET'] !== null) {
			$this->_contentCharset = $this->_arHeader['CHARSET'];
		}
		if( !empty($this->_arHeader['Content-Type']['VALUE_MAIN']) ) {
			$this->_contentType = $this->_arHeader['Content-Type']['VALUE_MAIN'];
		}
		if($this->_lastCurlErrNo === CURLE_OK) {
			if($this->getStatus() == 200) {
				$this->_setRequestComplete();
			}
			elseif($this->getStatus() == 404 && $this->_bAllowSave404ToFile) {
				$this->_setRequestComplete();
			}
		}
		if(true === $this->_bRequestSuccess) {
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

	static public function generateID() {
		return md5('OBX\Core\Curl\Request_'.time().'_'.rand(0, 9999));
	}

	public function setDownloadFolder($downloadFolder) {
		$downloadFolder = str_replace(array('\\', '//'), '/', $downloadFolder);
		$downloadFolder = str_replace('../', '', $downloadFolder);
		$downloadFolder = '/'.trim($downloadFolder, '/');
		if($downloadFolder == $this->_dwnFolder) {
			return true;
		}
		if( !CheckDirPath(OBX_DOC_ROOT.$downloadFolder) ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		$this->_dwnDir = OBX_DOC_ROOT.$downloadFolder;
		$this->_dwnFolder = $downloadFolder;
		return true;
	}
	public function getDownloadFolder($bReturnFullPath = false) {
		if($bReturnFullPath !== false) {
			return $this->_dwnDir;
		}
		return $this->_dwnFolder;
	}
	public function getDownloadFilePath($bReturnFullPath = false) {
		if($bReturnFullPath !== false) {
			return $this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT;
		}
		return $this->_dwnFolder.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT;
	}

	public function _initDownload() {
		if($this->_bDownloadSuccess === true) {
			return;
		}
		if(null === $this->_dwnDir) {
			$this->setDownloadFolder(static::DOWNLOAD_FOLDER);
		}
		if(null === $this->_ID) {
			$this->_ID = static::generateID();
		}
		$this->_dwnFileHandler = fopen($this->getDownloadFilePath(true), 'wb');
		if( !$this->_dwnFileHandler ) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
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

	public function _afterDownload() {
		$this->_after_exec();
		if($this->_lastCurlErrNo === CURLE_OK) {
			if($this->getStatus() == 200) {
				$this->_setDownloadComplete();
			}
			elseif($this->getStatus() == 404 && $this->_bAllowSave404ToFile) {
				$this->_setDownloadComplete();
			}
		}
		fclose($this->_dwnFileHandler);
		$this->_dwnFileHandler = null;
		if(true === $this->_bDownloadSuccess) {
			$contentType = $this->getContentType();
			$this->_fillOriginalName($contentType);
		}
	}

	protected function _fillOriginalName(&$contentType) {
		$fileName = static::getFileNameFromUrl($this->_url, $fileExt, $baseName);
		if( empty($fileName) ) {
			$baseName = static::generateID();
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
	 * @throws RequestError
	 */
	public function saveToFile($relPath) {
		$relPath = str_replace(array('\\', '//'), '/', $relPath);
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		if( !CheckDirPath($path) ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		if( $this->_bDownloadSuccess === true ) {
			//fclose($this->_dwnFileHandler);
			//$this->_dwnFileHandler = null;
			curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
			if( !copy($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT, $path) ) {
				throw new RequestError('', RequestError::E_FILE_SAVE_FAILED);
			}
		}
		elseif($this->_bRequestSuccess === true) {
			file_put_contents($path, $this->_body);
		}
		else {
			throw new RequestError('', RequestError::E_FILE_SAVE_NO_RESPONSE);
		}
	}

	/**
	 * @param $relPath
	 * @param int $fileNameMode
	 * @throws RequestError
	 */
	public function saveToDir($relPath, $fileNameMode = self::SAVE_TO_DIR_GENERATE) {
		if( true !== $this->_bDownloadSuccess && true !== $this->_bRequestSuccess ) {
			return;
		}
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
		$relPath = str_replace('../', '', $relPath);
		$relPath = '/'.trim($relPath, '/');
		$path = OBX_DOC_ROOT.$relPath;
		if( !CheckDirPath($path.'/') ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}

		if($fileNameMode === self::SAVE_TO_DIR_GENERATE) {
			$baseName = static::generateID();
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
					$baseName = static::generateID();
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
		if(true === $this->_bDownloadSuccess) {
			copy($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT, $this->_savePath);
			curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
		}
		elseif(true === $this->_bRequestSuccess) {
			file_put_contents($this->_savePath, $this->_body);
		}
		else {
			throw new RequestError('', RequestError::E_FILE_SAVE_NO_RESPONSE);
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
		$this->_bDownloadSuccess = ($bComplete!==false)?true:false;
	}
	protected function _setRequestComplete($bComplete = true) {
		$this->_bRequestSuccess = ($bComplete!==false)?true:false;
	}

	/**
	 * @param $relPath
	 * @return bool
	 */
	public function downloadToFile($relPath) {
		$this->_initDownload();
		curl_exec($this->_curlHandler);
		$this->_afterDownload();
		//if($this->_sta)
		return $this->saveToFile($relPath);
	}

	/**
	 * @param $relPath
	 * @param int $fileNameMode
	 */
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

	public function isDownloadSuccess() {
		return $this->_bDownloadSuccess;
	}

	public function isRequestSuccess() {
		return $this->_bRequestSuccess;
	}

	public function setCaching($bCaching = true) {
		$this->_bCaching = ($bCaching !== false)?true:false;
	}

	public function clearCache() {
		if(is_file($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT)) {
			@unlink($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT);
		}
	}
}