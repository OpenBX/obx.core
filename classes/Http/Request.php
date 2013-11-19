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
	protected $_dwnFileName = null;
	protected $_bDownloadComplete = false;
	protected $_bRequestComplete = false;


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
	}

	protected function _initCURL() {
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
			unlink($this->_dwnDir.'/'.$this->_dwnFileName);
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

	public function _parseHeader($header) {

	}

	public function send() {
		$this->_initCURL();
		curl_setopt($this->_curlHandler, CURLOPT_NOBODY, false);
		$this->_parseResponse($this->_exec());
		$this->_arHeader = $this->_parseHeader($this->_header);
		$this->_setRequestComplete();
	}

	public function requestHeader($bReturnRawHeader = false) {
		$this->_initCURL();
		curl_setopt($this->_curlHandler, CURLOPT_NOBODY, true);
		$this->_header = $this->_exec();
		$this->_arHeader = $this->_parseHeader($this->_header);
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
		if(null === $this->_dwnFileName) {
			$this->_dwnFileName = md5(time().'_'.rand(0, 9999)).'.dwn';
		}
		$this->_dwnFileHandler = fopen($this->_dwnDir.'/'.$this->_dwnFileName, 'w');
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
			$contentType = $this->getContentType();
			copy($this->_dwnDir.'/'.$this->_dwnFileName, $path);
		}
		elseif($this->_bRequestComplete === true) {
			file_put_contents($path, $this->_body);
		}
	}

	public function saveToDir($relPath){
		$relPath = str_replace(array('\\', '//'), '/', $relPath);
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		if( !CheckDirPath($path.'/') ) {
			throw new RequestError('', RequestError::E_WRONG_PATH);
		}
		if( $this->_bDownloadComplete === true ) {
			fclose($this->_dwnFileHandler);
			$this->_dwnFileHandler = null;
			$contentType = $this->getContentType();
			curl_setopt($this->_curlHandler, CURLOPT_FILE, STDOUT);
		}
		elseif($this->_bRequestComplete === true) {
			file_put_contents($path, $this->_body);
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
		$contentType = curl_getinfo($this->_curlHandler, CURLINFO_CONTENT_TYPE);
		$header = curl_getinfo($this->_curlHandler, CURLINFO_HEADER_OUT);
		list($contentType, $fake) = explode(';', $contentType);
		$contentType = trim($contentType);
		return $contentType;
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