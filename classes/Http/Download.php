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

/**
 * Class Download
 * @package OBX\Core\Http
 * Класс для пошагового скачивания файлов
 */
class Download extends CMessagePoolDecorator {

	const DEFAULT_TIME_LIMIT = 25;

	static protected $_arInstances = array();

	protected $_currentStatus = null;
	protected $_timeLimit = self::DEFAULT_TIME_LIMIT;

	static public function getInstance($url) {
		$urlSign = md5($url);
		if( array_key_exists($urlSign, static::$_arInstances) ) {
			return static::$_arInstances[$urlSign];
		}
		$Download = new self($url);
		return $Download;
	}

	protected function __construct($url) {

	}
	protected function __clone() {}
	function __destruct() {}

	public function setTimeLimit($seconds) {

	}
} 