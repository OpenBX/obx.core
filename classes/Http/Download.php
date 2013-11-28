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
use OBx\Core\Http\Exceptions\DownloadError;

IncludeModuleLangFile(__FILE__);

/**
 * Class Download
 * @package OBX\Core\Http
 * Класс для пошагового скачивания файлов
 */
class Download extends CMessagePoolDecorator {

	const DEF_TIME_LIMIT = 25;
	const DEF_USER_AGENT = 'OpenBX downloader';
	const DEF_DWN_FOLDER = '/bitrix/tmp/obx.core/downloads';

	static protected $_arInstances = array();
	static protected $_bDefaultDwnDirChecked = false;

	protected $_currentStatus = null;
	protected $_htmlStatusTemplate = <<<HTML

HTML;

	protected $_timeLimit = self::DEF_TIME_LIMIT;
	protected $_userAgent = self::DEF_USER_AGENT;

	protected $_bUseProxy = false;
	protected $_proxyAddress = null;
	protected $_proxyPort = null;
	protected $_proxyUser = null;
	protected $_proxyPassword = null;

	protected $_socket = null;
	protected $_dwnFile = null;
	protected $_dwnFolder = null;
	protected $_dwnName = null;
	protected $_dwnExt = null;


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
	 * Для нужд тестирования
	 */
	static public function _clearInstanceCache() {
		static::$_arInstances = array();
	}

	protected function __construct($url) {
		$this->_timeLimit = static::DEF_TIME_LIMIT;
		$this->_userAgent = static::DEF_USER_AGENT;
		static::_checkDefaultDwnDir();

	}
	protected function __clone() {}
	function __destruct() {}

	/**
	 * @throws Exceptions\DownloadError
	 */
	static protected function _checkDefaultDwnDir() {
		if( false === static::$_bDefaultDwnDirChecked ) {
			$bSuccess = CheckDirPath($_SERVER['DOCUMENT_ROOT'].static::DEF_DWN_FOLDER);
			if( ! $bSuccess ) {
				throw new DownloadError('', DownloadError::E_NO_ACCESS_DWN_FOLDER);
			}
			static::$_bDefaultDwnDirChecked = true;
		}
	}

	public function setTimeLimit($seconds) {

	}

	public function setUserAgent($userAgent) {

	}

	public function setProxy($proxyAddr, $proxyPort, $proxyUserName, $proxyPassword) {

	}

	/**
	 * @return bool
	 */
	public function loadFile() {
		// Это сочетание имволов отделяют header от body "\r\n\r\n"
		return false;
	}

	protected function _saveStepToFile() {

	}
	protected function _loadStepFromFile() {

	}

	/**
	 * @return bool
	 */
	public function isFinished() {
		return false;
	}

	public function saveToDir() {

	}
}
