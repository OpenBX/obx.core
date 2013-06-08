<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core;

interface IMessagePool
{
	function addMessage($text, $code = 0);
	function addWarning($text, $code = 0);
	function addError($text, $code = 0);

	function getLastMessage($return = 'TEXT');
	function getLastWarning($return = 'TEXT');
	function getLastError($return = 'TEXT');

	function popLastMessage($return = 'TEXT');
	function popLastWarning($return = 'TEXT');
	function popLastError($return = 'TEXT');

	function getMessages();
	function getWarnings();
	function getErrors();
	function getMessagePoolData();

	function countMessages();
	function countWarnings();
	function countErrors();
	function countMessagePoolData();

	function clearMessages();
	function clearWarnings();
	function clearErrors();
	function clearMessagePool();
}

interface IMessagePoolStatic
{
	static function addMessage($text, $code = 0);
	static function addWarning($text, $code = 0);
	static function addError($text, $code = 0);

	static function getLastMessage($return = 'TEXT');
	static function getLastWarning($return = 'TEXT');
	static function getLastError($return = 'TEXT');

	static function popLastMessage($return = 'TEXT');
	static function popLastWarning($return = 'TEXT');
	static function popLastError($return = 'TEXT');

	static function getMessages();
	static function getWarnings();
	static function getErrors();
	static function getMessagePoolData();

	static function countMessages();
	static function countWarnings();
	static function countErrors();
	static function countMessagePoolData();

	static function clearMessages();
	static function clearWarnings();
	static function clearErrors();
	static function clearMessagePool();
}

class CMessagePool implements IMessagePool
{
	protected $_arMessages = array();
	protected $_countMessages = 0;
	protected $_arErrors = array();
	protected $_countErrors = 0;
	protected $_arWarnings = array();
	protected $_countWarnings = 0;
	protected $_arCommonMessagePool = array();
	protected $_countCommonMessages = 0;

	public function addMessage($text, $code = 0) {
		$arMessage = array(
			"TEXT" => $text,
			"CODE" => $code,
			"TYPE" => "M"
		);
		$this->_arMessages[$this->_countMessages] = $arMessage;
		$this->_arCommonMessagePool[$this->_countCommonMessages] = $arMessage;
		$this->_countMessages++;
		$this->_countCommonMessages++;
	}
	public function addWarning($text, $code = 0) {
		$arMessage = array(
			"TEXT" => $text,
			"CODE" => $code,
			"TYPE" => "W"
		);
		$this->_arWarnings[$this->_countWarnings] = $arMessage;
		$this->_arCommonMessagePool[$this->_countCommonMessages] = $arMessage;
		$this->_countWarnings++;
		$this->_countCommonMessages++;
	}
	public function addError($text, $code = 0) {
		$arMessage = array(
			"TEXT" => $text,
			"CODE" => $code,
			"TYPE" => "E"
		);
		$this->_arErrors[$this->_countErrors] = $arMessage;
		$this->_arCommonMessagePool[$this->_countCommonMessages] = $arMessage;
		$this->_countErrors++;
		$this->_countCommonMessages++;
	}




	public function getLastMessage($return = 'TEXT') {
		$arLastMessage = $this->_arMessages[$this->_countMessages-1];
		switch($return) {
			case 'TEXT':
			case 'CODE':
			case 'TYPE':
				break;
			case 'ALL':
			case 'ARRAY':
				return $arLastMessage;
				break;
			default:
				$return = 'TEXT';
				break;
		}
		return $arLastMessage[$return];
	}
	public function getLastWarning($return = 'TEXT') {
		$arLastWarning = $this->_arWarnings[$this->_countWarnings-1];
		switch($return) {
			case 'TEXT':
			case 'CODE':
			case 'TYPE':
				break;
			case 'ALL':
			case 'ARRAY':
				return $arLastWarning;
				break;
			default:
				$return = 'TEXT';
				break;
		}
		return $arLastWarning[$return];
	}
	public function getLastError($return = 'TEXT') {
		$arLastError = $this->_arErrors[$this->_countErrors-1];
		switch($return) {
			case 'TEXT':
			case 'CODE':
			case 'TYPE':
				break;
			case 'ALL':
			case 'ARRAY':
				return $arLastError;
				break;
			default:
				$return = 'TEXT';
				break;
		}
		return $arLastError[$return];
	}

	public function popLastMessage($return = 'TEXT') {
		$arLastMessage = $this->getLastMessage($return);
		if($this->_countMessages > 0) {
			unset($this->_arMessages[$this->_countMessages-1]);
			$this->_countMessages--;
		}
		return $arLastMessage;
	}
	public function popLastWarning($return = 'TEXT') {
		$arLastWarning = $this->getLastWarning($return);
		if($this->_countWarnings > 0) {
			unset($this->_arWarnings[$this->_countWarnings-1]);
			$this->_countWarnings--;
		}
		return $arLastWarning;
	}
	public function popLastError($return = 'TEXT') {
		$arLastError = $this->getLastError($return);
		if($this->_countErrors > 0) {
			unset($this->_arErrors[$this->_countErrors-1]);
			$this->_countErrors--;
		}
		return $arLastError;
	}

	public function getMessages() {
		return $this->_arMessages;
	}
	public function getWarnings() {
		return $this->_arWarnings;
	}
	public function getErrors() {
		return $this->_arErrors;
	}
	public function getMessagePoolData() {
		return $this->_arCommonMessagePool;
	}

	public function countMessages() {
		return $this->_countMessages;
	}
	public function countWarnings() {
		return $this->_countWarnings;
	}
	public function countErrors() {
		return $this->_countErrors;
	}
	public function countMessagePoolData() {
		return $this->_countCommonMessages;
	}

	public function clearMessages() {
		$this->_arErrors = array();
		foreach($this->_arCommonMessagePool as $key => &$arr) {
			if($arr["TYPE"]=="M") {
				unset($this->_arCommonMessagePool[$key]);
			}
		}
		// update keys
		//$this->_arCommonMessagePool = array_values($this->_arCommonMessagePool);
	}
	public function clearWarnings() {
		$this->_arErrors = array();
		foreach($this->_arCommonMessagePool as $key => &$arr) {
			if($arr["TYPE"]=="W") {
				unset($this->_arCommonMessagePool[$key]);
			}
		}
		// update keys
		//$this->_arCommonMessagePool = array_values($this->_arCommonMessagePool);
	}
	public function clearErrors() {
		$this->_arErrors = array();
		foreach($this->_arCommonMessagePool as $key => &$arr) {
			if($arr["TYPE"]=="E") {
				unset($this->_arCommonMessagePool[$key]);
			}
		}
		// update keys
		//$this->_arCommonMessagePool = array_values($this->_arCommonMessagePool);
	}
	public function clearMessagePool() {
		$this->_arMessages = array();
		$this->_arErrors = array();
		$this->_arWarnings = array();
		$this->_arCommonMessagePool = array();
	}
}


/**
 * @package OBX\Core
 */
class CMessagePoolStatic implements IMessagePoolStatic {
	static protected $MessagePool = array();

	/**
	 * @return CMessagePool
	 */
	final static public function getMessagePool() {
		$className = get_called_class();
		if( !isset(self::$MessagePool[$className]) ) {
			self::$MessagePool[$className] = new CMessagePool;
		}
		return self::$MessagePool[$className];
	}
	final static public function setMessagePool($MessPool) {
		$className = get_called_class();
		if($MessPool instanceof CMessagePool) {
			self::$MessagePool[$className] = $MessPool;
		}
	}

	static public function addMessage($text, $code = 0) {
		return self::getMessagePool()->addMessage($text, $code);
	}
	static public function addError($text, $code = 0) {
		return self::getMessagePool()->addError($text, $code);
	}
	static public function addWarning($text, $code = 0) {
		return self::getMessagePool()->addWarning($text, $code);
	}
	static public function getLastError($return = 'TEXT') {
		return self::getMessagePool()->getLastError($return);
	}
	static public function getLastWarning($return = 'TEXT') {
		return self::getMessagePool()->getLastWarning($return);
	}
	static public function getLastMessage($return = 'TEXT') {
		return self::getMessagePool()->getLastMessage($return);
	}
	static public function popLastError($return = 'TEXT') {
		return self::getMessagePool()->popLastError($return);
	}
	static public function popLastWarning($return = 'TEXT') {
		return self::getMessagePool()->popLastWarning($return);
	}
	static public function popLastMessage($return = 'TEXT') {
		return self::getMessagePool()->popLastMessage($return);
	}
	static public function getMessages() {
		return self::getMessagePool()->getMessages();
	}
	static public function getErrors() {
		return self::getMessagePool()->getErrors();
	}
	static public function getWarnings() {
		return self::getMessagePool()->getWarnings();
	}
	static public function getMessagePoolData() {
		return self::getMessagePool()->getMessagePoolData();
	}
	static public function countMessages() {
		return self::getMessagePool()->countMessages();
	}
	static public function countWarnings() {
		return self::getMessagePool()->countWarnings();
	}
	static public function countErrors() {
		return self::getMessagePool()->countErrors();
	}
	static public function countMessagePoolData() {
		return self::getMessagePool()->countMessagePoolData();
	}
	static public function clearMessages() {
		return self::getMessagePool()->clearMessages();
	}
	static public function clearErrors() {
		return self::getMessagePool()->clearErrors();
	}
	static public function clearWarnings() {
		return self::getMessagePool()->clearWarnings();
	}
	static public function clearMessagePool() {
		return self::getMessagePool()->clearMessagePool();
	}
}

class CMessagePoolDecorator implements IMessagePool {
	protected $MessagePool = null;

	/**
	 * @return CMessagePool
	 */
	public function getMessagePool() {
		if($this->MessagePool == null) {
			$this->MessagePool = new CMessagePool;
		}
		return $this->MessagePool;
	}
	public function setMessagePool($MessPool) {
		if($MessPool instanceof CMessagePool) {
			$this->MessagePool = $MessPool;
		}
	}

	public function addMessage($text, $code = 0) {
		return $this->getMessagePool()->addMessage($text, $code);
	}
	public function addError($text, $code = 0) {
		return $this->getMessagePool()->addError($text, $code);
	}
	public function addWarning($text, $code = 0) {
		return $this->getMessagePool()->addWarning($text, $code);
	}
	public function getLastError($return = 'TEXT') {
		return $this->getMessagePool()->getLastError($return);
	}
	public function getLastWarning($return = 'TEXT') {
		return $this->getMessagePool()->getLastWarning($return);
	}
	public function getLastMessage($return = 'TEXT') {
		return $this->getMessagePool()->getLastMessage($return);
	}
	public function popLastError($return = 'TEXT') {
		return $this->getMessagePool()->popLastError($return);
	}
	public function popLastWarning($return = 'TEXT') {
		return $this->getMessagePool()->popLastWarning($return);
	}
	public function popLastMessage($return = 'TEXT') {
		return $this->getMessagePool()->popLastMessage($return);
	}
	public function getMessages() {
		return $this->getMessagePool()->getMessages();
	}
	public function getErrors() {
		return $this->getMessagePool()->getErrors();
	}
	public function getWarnings() {
		return $this->getMessagePool()->getWarnings();
	}
	public function getMessagePoolData() {
		return $this->getMessagePool()->getMessagePoolData();
	}
	public function countMessages() {
		return $this->getMessagePool()->countMessages();
	}
	public function countWarnings() {
		return $this->getMessagePool()->countWarnings();
	}
	public function countErrors() {
		return $this->getMessagePool()->countErrors();
	}
	public function countMessagePoolData() {
		return $this->getMessagePool()->countMessagePoolData();
	}
	public function clearMessages() {
		return $this->getMessagePool()->clearMessages();
	}
	public function clearErrors() {
		return $this->getMessagePool()->clearErrors();
	}
	public function clearWarnings() {
		return $this->getMessagePool()->clearWarnings();
	}
	public function clearMessagePool() {
		return $this->getMessagePool()->clearMessagePool();
	}
}
