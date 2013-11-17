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

	const DEFAULT_TIMEOUT = 1000;
	const DEFAULT_WAITING = 1000;

	protected $_curlHandler = null;
	protected $_content = null;
	protected $_header = null;
	protected $_arHeader = array();
	protected $_fileHandler = null;


	public function __construct($url, $timeout = self::DEFAULT_TIMEOUT) {
		RequestError::checkCURL();
		$this->_curlHandler = curl_init();
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

	public function setFile($relPath) {
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		$dirPath = dirname($path);
		if( !CheckDirPath($path) ) {
			throw new RequestError('', RequestError::E_WRONG_FILE_PATH);
		}
		$this->_fileHandler = fopen($path, 'w');
		if( !$this->_fileHandler ) {
			throw new RequestError('', RequestError::E_CANT_OPEN_FILE);
		}
	}

	public function getInfo() {

	}

	public function saveToFile($filePath) {
		$this->setFile($filePath);
		$this->send();
	}

	public function saveToBXFile($filePath) {

	}

	public function saveToIBElement($IBLOCK, $target) {


	}

	public function send() {

	}
} 