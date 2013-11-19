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
	protected $_dwnFileHandler = null;
	protected $_dwnFileName = null;
	protected $_dwnRealFileName = null;
	protected $_dwnDir = null;
	protected $_mimeType = null;
	protected $_expectedMime = null;

	const MODE_CONTENT = 1;
	const MODE_DOWNLOAD = 2;
	const MODE_GET_HEADER = 3;
	protected $_mode = self::MODE_CONTENT;


	public function __construct($url) {
		RequestError::checkCURL();
		self::_checkDefaultDwnDir();
		$this->_curlHandler = curl_init();
		$this->setTimeout(static::DEFAULT_TIMEOUT);
		$this->setWaiting(static::DEFAULT_WAITING);
		$this->_dwnDir = $_SERVER['DOCUMENT_ROOT'].static::DOWNLOAD_FOLDER;
		curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curlHandler, CURLOPT_HEADER, true);
		//curl_setopt($this->_curlHandler, CURLOPT_NOBODY, false);
		//curl_setopt($this->_curlHandler, CURLOPT_HTTPHEADER, array('Range: bytes=0-0'));

	}
	public function __destruct() {
		if($this->_mode == self::MODE_DOWNLOAD) {
			if($this->_curlHandler != null) {
				fclose($this->_curlHandler);
				$this->_curlHandler = null;
			}
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



	public function saveToFile($relPath) {
		$relPath = str_replace(array('\\', '//'), '/', $relPath);
		if(substr($relPath, -1) != '/')
		{
			$p = strrpos($relPath, '/');
			$fileName = substr($relPath, $p+1);
			$relPath = substr($relPath, 0, $p);
		}
		$relPath = rtrim($relPath, '/');
	}



	protected function _setMode($mode) {
		switch($mode) {
			case self::MODE_DOWNLOAD:
				if(null === $this->_dwnDir) {
					$this->setDownloadDir(static::DOWNLOAD_FOLDER);
				}
				if(null === $this->_dwnFileName) {
					$this->_dwnFileName = md5(time().'_'.rand(0, 9999));
				}
				$this->_dwnFileHandler = fopen($this->_dwnDir.'/'.$this->_dwnFileName, 'w');
				if( !$this->_dwnFileHandler ) {
					throw new RequestError('', RequestError::E_PERM_DENIED);
				}
				curl_setopt($this->_curlHandler, CURLOPT_FILE, $this->_dwnFileHandler);
				curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, false);
				curl_setopt($this->_curlHandler, CURLOPT_HEADER, false);
				$this->_mode = $mode;
				break;
			case self::MODE_CONTENT:
			case self::MODE_GET_HEADER:
				$this->_dwnDir = null;
				$this->_dwnFileName = null;
				fclose($this->_dwnFileHandler);
				$this->_dwnFileHandler = null;
				curl_setopt($this->_curlHandler, CURLOPT_FILE, $this->_dwnFileHandler);
				curl_setopt($this->_curlHandler, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($this->_curlHandler, CURLOPT_HEADER, true);
				if(self::MODE_GET_HEADER === $mode) {
					curl_setopt($this->_curlHandler, CURLOPT_NOBODY, true);
				}
				else {
					curl_setopt($this->_curlHandler, CURLOPT_NOBODY, false);
				}
				$this->_mode = $mode;
				break;
		}
	}

	public function downloadToDir($relPath) {

	}

	public function downloadToFile() {

	}

	public function saveToBXFile($filePath) {

	}

	public function saveToIBElement($IBLOCK, $target) {

	}

	public function requestHeaders() {

	}

	public function exec() {
		$response = curl_exec($this->_curlHandler);
		curl_getinfo($this->_curlHandler, CURLINFO_HTTP_CODE);
		if( $this->_mode == self::MODE_CONTENT ) {
			$this->_parseResponse($response);
		}
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

	public function send() {

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