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
	const DOWNLOAD_STATE_FILE_EXT = 'state';
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
	protected $_curlMultiHandler = null;

	protected $_header = null;
	protected $_body = null;
	protected $_arHeader = array();

	protected $_dwnDir = null;
	protected $_dwnFolder = null;
	protected $_dwnFileHandler = null;
	protected $_dwnFileSize = 0;
	protected $_dwnIterationSize = null;
	protected $_dwnResumeFrom = 0;
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
	protected $_contentExpectedSize = null;
	protected $_responseStatus = null;

	protected $_bCaching = false;
	protected $_bCachingCheckFileSize = false;
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

	const _FRIEND_CLASS_LINK = 521389614;
							 //FRIENDCLA(SS)

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
			static::fixFileName($requestID);
			$this->_ID = $requestID;
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
		// см. описание переменной $this->_bEncapsulatedID
		if(false === $this->_bCaching || true === $this->_bEncapsulatedID) {
			$dwnFilePath = $this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT;
			$dwnStateFilePath = $dwnFilePath.'.'.static::DOWNLOAD_STATE_FILE_EXT;
			if(is_file($dwnFilePath)) {
				@unlink($dwnFilePath);
			}
			if(is_file($dwnStateFilePath)) {
				@unlink($dwnStateFilePath);
			}
			unset($dwnFilePath, $dwnStateFilePath);
		}
		curl_close($this->_curlHandler);
	}
	protected function __clone() {}

	public function getID() {
		$this->_bEncapsulatedID = false;
		return $this->_ID;
	}
	public function _getID($_friendClass = null) {
		if($_friendClass !== self::_FRIEND_CLASS_LINK) throw new \ErrorException('Method '.__METHOD__.' can be called only from friend class');
		return $this->_ID;
	}

	public function getUrl() {
		return $this->_url;
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
			if( ! ($bSuccess = CheckDirPath(OBX_DOC_ROOT.static::DOWNLOAD_FOLDER.'/')) ) {
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
		if($seconds > 0) {
			$this->_timeout = $seconds;
			curl_setopt($this->_curlHandler, CURLOPT_TIMEOUT, $this->_timeout);
		}
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
		if($seconds > 0) {
			$this->_waiting = $seconds;
			curl_setopt($this->_curlHandler, CURLOPT_CONNECTTIMEOUT, $this->_waiting);
		}

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

	public function setPost($post) {
		curl_setopt($this->_curlHandler, CURLOPT_POST, true);
		if (is_array($post)) {
			//$post = self::arrayToCurlPost($post);
			$post = http_build_query($post);
		} else {
			$post = trim($post);
		}
		curl_setopt($this->_curlHandler, CURLOPT_POSTFIELDS, $post);
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
	 *
	 */
	public function _parseResponse(&$response, $_friendClass = null) {
		if($_friendClass !== self::_FRIEND_CLASS_LINK) throw new \ErrorException('Method '.__METHOD__.' can be called only from friend class');
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
		$this->_reconnectMultiHandler();
		if($this->_lastCurlErrNo == CURLE_OK && !empty($this->_lastCurlError) ) {
			// если кривой curl не выдает errno
			$this->_lastCurlErrNo = CurlError::getCurlErrorNumberByText($this->_lastCurlError);
		}
		if($this->_lastCurlErrNo != CURLE_OK) {
			throw new CurlError('cURL Error on requestID='.$this->_ID.': '.$this->_lastCurlError, $this->_lastCurlErrNo);
		}
	}

	public function _initSend($_friendClass = null){
		if($_friendClass !== self::_FRIEND_CLASS_LINK) throw new \ErrorException('Method '.__METHOD__.' can be called only from friend class');
		$this->_bRequestSuccess = false;
		$this->_header = null;
		$this->_arHeader = array();
		$this->_body = null;
		$this->_initCURL();
		curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, true);
		curl_setopt($this->_curlHandler, CURLOPT_NOBODY, false);
	}

	public function send() {
		$this->_initSend(self::_FRIEND_CLASS_LINK);
		$response = curl_exec($this->_curlHandler);
		$this->_afterSend($response, self::_FRIEND_CLASS_LINK);
		return $this->_body;
	}
	public function _afterSend(&$response, $_friendClass = null) {
		if($_friendClass !== self::_FRIEND_CLASS_LINK) throw new \ErrorException('Method '.__METHOD__.' can be called only from friend class');
		$this->_after_exec();
		$this->_parseResponse($response, self::_FRIEND_CLASS_LINK);
		$this->_arHeader = $this->parseHeader($this->_header);
		if($this->_arHeader['CHARSET'] !== null) {
			$this->_contentCharset = $this->_arHeader['CHARSET'];
		}
		if( !empty($this->_arHeader['Content-Type']['VALUE_MAIN']) ) {
			$this->_contentType = $this->_arHeader['Content-Type']['VALUE_MAIN'];
		}
		if($this->_lastCurlErrNo === CURLE_OK) {
			if($this->getStatus() == 200) {
				$this->_bRequestSuccess = true;
			}
			elseif($this->getStatus() == 404 && $this->_bAllowSave404ToFile) {
				$this->_bRequestSuccess = true;
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

	public function signUrl() {
		return md5($this->_url);
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
	public function getDownloadStateFilePath($bReturnFullPath = false) {
		if($bReturnFullPath !== false) {
			return $this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT.'.'.static::DOWNLOAD_STATE_FILE_EXT;
		}
		return $this->_dwnFolder.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT.'.'.static::DOWNLOAD_STATE_FILE_EXT;
	}


	/**
	 * @return bool - Can resume file downloading
	 */
	protected function _checkResumeDownload() {
		$filePath = $this->getDownloadFilePath(true);
		$stateFilePath = $this->getDownloadFilePath(true).'.'.static::DOWNLOAD_STATE_FILE_EXT;
		if( file_exists($filePath) && file_exists($stateFilePath) ) {
			$this->_readStateFile($urlFromState, $contentType, $charset, $fileSizeFromState, $contentExpectedSizeFromState);
			if($urlFromState != $this->_url) {
				return false;
			}
			// ф-ия filesize кеширует результат, потому в рамках нескольких итераций в одном
			// скрипте надо очищать кеш http://www.php.net/manual/ru/function.clearstatcache.php
			if($this->_bCachingCheckFileSize === true) {
				clearstatcache();
				$this->_dwnFileSize = intval(filesize($filePath));
			}
			else {
				$this->_dwnFileSize = $fileSizeFromState;
			}
			if($this->_dwnFileSize>0 && $this->_dwnFileSize == $fileSizeFromState) {
				if($this->_dwnFileSize === $contentExpectedSizeFromState) {
					$this->_contentType = $contentType;
					$this->_contentCharset = $charset;
					$this->_bDownloadSuccess = true;
					return false;
				}
				$this->_contentExpectedSize = $contentExpectedSizeFromState;
				$this->_dwnResumeFrom = $this->_dwnFileSize;
				curl_setopt($this->_curlHandler, CURLOPT_RESUME_FROM, $this->_dwnResumeFrom);
				return true;
			}
			else {
				$this->_dwnFileSize = 0;
				return false;
			}
		}
		return false;
	}

	/**
	 * @param null $_friendClass
	 * @throws \OBX\Core\Exceptions\Curl\RequestError
	 * @throws RequestError | \ErrorException
	 * @return bool - Can do exec
	 */
	public function _initDownload($_friendClass = null) {
		if($_friendClass !== self::_FRIEND_CLASS_LINK) throw new \ErrorException('Method '.__METHOD__.' can be called only from friend class');
		if(true === $this->_bDownloadSuccess) {
			return false;
		}
		$this->_dwnIterationSize = null;
		$this->_dwnFileSize = 0;
		$openMode = 'wb';
		if( true === $this->_bCaching ) {
			if(true === $this->_checkResumeDownload($openMode) ) {
				$openMode = 'ab';
			}
			if(true === $this->_bDownloadSuccess) {
				return false;
			}
		}

		$this->_dwnFileHandler = fopen($this->getDownloadFilePath(true), $openMode);
		if( !$this->_dwnFileHandler ) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, false);
		curl_setopt($this->_curlHandler, CURLOPT_FILE, $this->_dwnFileHandler);
		return true;
	}

	public function download() {
		$bCanDoExec = $this->_initDownload(self::_FRIEND_CLASS_LINK);
		if(true === $bCanDoExec) {
			curl_exec($this->_curlHandler);
			$this->_afterDownload(self::_FRIEND_CLASS_LINK);
		}
	}

	public function _afterDownload($_friendClass = null) {
		if($_friendClass !== self::_FRIEND_CLASS_LINK) throw new \ErrorException('Method '.__METHOD__.' can be called only from friend class');
		if(true === $this->_bDownloadSuccess) {
			return ;
		}
		fclose($this->_dwnFileHandler);
		$this->_dwnFileHandler = null;
		if( true === $this->_bCaching ) {
			$this->_saveStateFile();
		}
		$this->_after_exec();
		$httpStatus = $this->getStatus();
		if( $httpStatus == 200
			||
			($this->_dwnResumeFrom > 0 && $httpStatus == 206)
			||
			(true === $this->_bAllowSave404ToFile && $httpStatus == 404)
		) {
			if( $this->_contentExpectedSize > 0 ) {
				if( $this->_dwnFileSize == $this->_contentExpectedSize ) {
					$this->_bDownloadSuccess = true;
				}
			}
			else {
				$this->_bDownloadSuccess = true;
			}
			$contentType = $this->getContentType();
			$this->_fillOriginalName($contentType);
		}
	}

	public function _readStateFile(&$url, &$contentType, &$charset, &$fileSize, &$expectedSize) {
		$stateContent = file_get_contents($this->getDownloadFilePath(true).'.'.static::DOWNLOAD_STATE_FILE_EXT);
		list($url, $originalFileName, $contentTypeNCharset, $sizes) = explode("\n", $stateContent);
		list($originalName, $originalExt) = explode('|', $originalFileName);
		if( null === $this->_originalName && !empty($originalFileName)
		) {
			$this->_originalName = $originalName;
			$this->_originalExt = $originalExt;
		}
		list($contentType, $charset) = explode('|', $contentTypeNCharset);
		list($fileSize, $expectedSize) = explode('|', $sizes);
		$fileSize = intval($fileSize);
		$expectedSize = intval($expectedSize);

	}
	protected function _saveStateFile() {
		$this->getInfo(null, true);
		$stateContent = $this->_url."\n";
		$stateContent .= $this->_originalName.'|'.$this->_originalExt."\n";
		$stateContent .= $this->_contentType.'|'.$this->_contentCharset."\n";
		$stateContent .= $this->_dwnFileSize.'|'.$this->getContentExpectedSize();
		$stateContent .= "\n";
		file_put_contents(
			$this->getDownloadFilePath(true).'.'.static::DOWNLOAD_STATE_FILE_EXT,
			$stateContent
		);
	}

	protected function _fillOriginalName(&$contentType) {
		$Mime = Mime::getInstance();
		$fileName = static::getFileNameFromUrl($this->_url, $fileExt, $baseName);
		if( empty($fileName) ) {
			$baseName = static::generateID();
		}
		if(empty($fileExt)) {
			$fileExt = $Mime->getFileExt($contentType, static::DOWNLOAD_FILE_EXT);
		}
		else {
			switch($fileExt) {
				case 'php':
				case 'asp':
				case 'aspx':
				case 'jsp':
					$fileExt = $Mime->getFileExt($contentType, static::DOWNLOAD_FILE_EXT);
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
		$relPath = str_replace('../', '', $relPath);
		$relPath = '/'.trim($relPath, '/');
		$path = OBX_DOC_ROOT.$relPath;
		if( !CheckDirPath($path) ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		if( $this->_bDownloadSuccess === true ) {
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
		$this->_saveFileName = substr($relPath, strrpos($relPath, '/')+1);
		$this->_saveRelPath = $relPath;
		$this->_savePath = $path;
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

	/**
	 * @param $relPath
	 * @throws RequestError
	 */
	public function downloadToFile($relPath) {
		$this->_initDownload(self::_FRIEND_CLASS_LINK);
		curl_exec($this->_curlHandler);
		$this->_afterDownload(self::_FRIEND_CLASS_LINK);
		//if($this->_sta)
		$this->saveToFile($relPath);
	}

	/**
	 * @param $relPath
	 * @param int $fileNameMode
	 */
	public function downloadToDir($relPath, $fileNameMode = self::SAVE_TO_DIR_GENERATE) {
		$this->_initDownload(self::_FRIEND_CLASS_LINK);
		curl_exec($this->_curlHandler);
		$this->_afterDownload(self::_FRIEND_CLASS_LINK);
		$this->saveToDir($relPath, $fileNameMode);
	}

	public function getContentType() {
		if($this->_contentType === null) {
			$this->getInfo(null, true);
		}
		return $this->_contentType;
	}

	public function getCharset() {
		if($this->_contentCharset === null) {
			$this->getInfo(null, true);
		}
		return $this->_contentCharset;
	}

	public function getStatus() {
		if( null === $this->_responseStatus ) {
			$this->_responseStatus = curl_getinfo($this->_curlHandler, CURLINFO_HTTP_CODE);
			// START: гребаный курл достал своими глюками
			if($this->_responseStatus == 0) {
				if(null === $this->_lastCurlErrNo) {
					$this->_lastCurlErrNo = curl_errno($this->_curlHandler);
				}
				if($this->_lastCurlErrNo == CURLE_OK) {
					$this->_responseStatus = 200;
				}
			}
			// END: гребаный курл достал своими глюками
		}
		return $this->_responseStatus;
	}

	public function getContentExpectedSize() {
		if( null === $this->_contentExpectedSize ) {
			$this->getInfo(null, true);
		}
		return $this->_contentExpectedSize;
	}

	public function getDownloadProgress() {

	}

	public function getInfo($curlOpt = null, $bFillVars = false){
		if($curlOpt === null) {
			$info = curl_getinfo($this->_curlHandler);
			if($bFillVars === true ) {
				$header = 'Content-Type: '.$info['content_type']."\n";
				$arHeader = static::parseHeader($header);
				if( null === $this->_contentType
					&& !empty($arHeader['Content-Type']['VALUE_MAIN'])
				) {
					$this->_contentType = $arHeader['Content-Type']['VALUE_MAIN'];
				}
				if( null === $this->_contentCharset
					&& array_key_exists('CHARSET', $arHeader)
					&& $arHeader['CHARSET'] != null
				) {
					$this->_contentCharset = $arHeader['CHARSET'];
				}
				$info['download_content_length'] = intval($info['download_content_length']);
				if( null === $this->_contentExpectedSize
					&& $info['download_content_length']>0
				) {
					$this->_contentExpectedSize = $info['download_content_length'] + $this->_dwnResumeFrom;
				}
				$info['size_download'] = intval($info['size_download']);
				if( null === $this->_dwnIterationSize  && $info['size_download']>0 ) {
					$this->_dwnIterationSize = $info['size_download'];
					$this->_dwnFileSize += $this->_dwnIterationSize;
				}
				if(null === $this->_originalName) {
					$this->_fillOriginalName($this->_contentType);
				}
			}
		}
		else {
			$info = curl_getinfo($this->_curlHandler, $curlOpt);
		}
		return $info;
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

	public function setCaching($bCaching = true, $bCheckFileSize = false) {
		$this->_bCaching = ($bCaching !== false)?true:false;
		$this->_bCachingCheckFileSize = (true === $bCheckFileSize)?true:false;
	}

	public function isCached() {
		return $this->_bCaching;
	}

	public function clearCache() {
		if(is_file($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT)) {
			@unlink($this->_dwnDir.'/'.$this->_ID.'.'.static::DOWNLOAD_FILE_EXT);
		}
	}

	public function _connectMultiHandler($curlMultiHandler) {
		if( null === $this->_curlMultiHandler) {
			$this->_curlMultiHandler = $curlMultiHandler;
			curl_multi_add_handle($curlMultiHandler, $this->_curlHandler);
		}
	}

	public function _disconnectMultiHandler() {
		if( null !== $this->_curlMultiHandler ) {
			curl_multi_remove_handle($this->_curlMultiHandler, $this->_curlHandler);
			$this->_curlMultiHandler = null;
		}
	}

	public function _reconnectMultiHandler() {
		if( null !== $this->_curlMultiHandler ) {
			@curl_multi_remove_handle($this->_curlMultiHandler, $this->_curlHandler);
			curl_multi_add_handle($this->_curlMultiHandler, $this->_curlHandler);
		}
	}

	public function _isMultiHandlerConnected() {
		return (null === $this->_curlMultiHandler)?false:true;
	}
}