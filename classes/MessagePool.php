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

use OBX\Core\Exceptions\AError;

interface IMessagePool
{
	function addNotice($text, $code = 0);
	function addWarning($text, $code = 0);
	function addError($text, $code = 0);
	function addErrorException(\ErrorException $Exception);
	function throwErrorException(\ErrorException $Exception);
	function addWarningException(\ErrorException $Exception);

	function getLastNotice($return = 'TEXT');
	function getLastWarning($return = 'TEXT');
	function getLastError($return = 'TEXT');

	function popLastNotice($return = 'TEXT');
	function popLastWarning($return = 'TEXT');
	function popLastError($return = 'TEXT');

	function getNotices();
	function getWarnings();
	function getErrors();
	function getMessagePoolData();

	function countNotices();
	function countWarnings();
	function countErrors();
	function countMessagePoolData();

	function clearNotices();
	function clearWarnings();
	function clearErrors();
	function clearMessagePool();
}

interface IMessagePoolStatic
{
	/** @deprecated */
	static function addNotice($text, $code = 0);
	static function addWarning($text, $code = 0);
	static function addError($text, $code = 0);
	static function addErrorException(\ErrorException $Exception);
	static function throwErrorException(\ErrorException $Exception);
	static function addWarningException(\ErrorException $Exception);

	static function getLastNotice($return = 'TEXT');
	static function getLastWarning($return = 'TEXT');
	static function getLastError($return = 'TEXT');

	static function popLastMessage($return = 'TEXT');
	static function popLastWarning($return = 'TEXT');
	static function popLastError($return = 'TEXT');

	static function getNotices();
	static function getWarnings();
	static function getErrors();
	static function getMessagePoolData();

	static function countNotices();
	static function countWarnings();
	static function countErrors();
	static function countMessagePoolData();

	static function clearNotices();
	static function clearWarnings();
	static function clearErrors();
	static function clearMessagePool();
}

/**
 * Class MessagePool
 * @package OBX\Core
 */
class MessagePool implements IMessagePool
{
	protected $_arNotices = array();
	protected $_countNotices = 0;
	protected $_arErrors = array();
	protected $_countErrors = 0;
	protected $_arWarnings = array();
	protected $_countWarnings = 0;
	protected $_arCommonMessagePool = array();
	protected $_countCommonMessages = 0;

	/**
	 * @var null|LogFile
	 */
	protected $_LogFile = null;
	const MSG_POOL_LOG_NOTHING = 0;
	const MSG_POOL_LOG_ERRORS = 1;
	const MSG_POOL_LOG_WARNINGS = 2;
	const MSG_POOL_LOG_MESSAGES = 4;
	const MSG_POOL_LOG_ALL = 7;
	protected $_logBehaviour = self::MSG_POOL_LOG_ALL;

	protected $_debugLevel = 0;
	const MSG_POOL_MAX_DBG_LVL = 5;


//	public function __construct() {
//		$this->_logBehaviour = self::MSG_POOL_LOG_ERRORS | self::MSG_POOL_LOG_WARNINGS;
//	}

	/**
	 * @param LogFile $LogFile
	 * @return bool
	 */
	public function registerLogFile(LogFile $LogFile = null) {
		if($LogFile instanceof LogFile) {
			$this->_LogFile = $LogFile;
			return true;
		}
		return false;
	}

	public function setLogBehaviour($behaviour) {
		$behaviour = intval($behaviour);
		if($behaviour>self::MSG_POOL_LOG_ALL) {
			return false;
		}
		$this->_logBehaviour = $behaviour;
		return true;
	}
	public function getLogBehaviour() {
		return $this->_logBehaviour;
	}

	/**
	 * @return null|LogFile
	 */
	public function getLogFile() {
		return $this->_LogFile;
	}

	public function setDebugLevel($level) {
		$level = intval($level);
		if($level > self::MSG_POOL_MAX_DBG_LVL) {
			return false;
		}
		$this->_debugLevel = $level;
		return true;
	}

	public function getDebugLevel() {
		return $this->_debugLevel;
	}

	/**
	 * Данный метод помечен как устаревший, поскольку верное его название addNotice
	 * @deprecated
	 */
	public function addMessage($text, $code = 0, $debugLevel=0) {
		$this->addNotice($text, $code, $debugLevel);
	}

	function addNotice($text, $code = 0, $debugLevel=0) {
		$debugLevel = intval($debugLevel);
		if($debugLevel > $this->_debugLevel) {
			return;
		}
		$this->_arCommonMessagePool[$this->_countCommonMessages] = array(
			"TEXT" => $text,
			"CODE" => $code,
			"TYPE" => "N"
		);
		$this->_arNotices[$this->_countNotices] = &$this->_arCommonMessagePool[$this->_countCommonMessages];
		$this->_countNotices++;
		$this->_countCommonMessages++;
		if( $this->_LogFile
			&& ($this->_logBehaviour & self::MSG_POOL_LOG_MESSAGES) > 0
		) {
			$this->_LogFile->logMessage($text, LogFile::MSG_TYPE_NOTE);
		}
	}

	public function addWarning($text, $code = 0, $debugLevel=0) {
		$debugLevel = intval($debugLevel);
		if($debugLevel > $this->_debugLevel) {
			return;
		}
		$this->_arCommonMessagePool[$this->_countCommonMessages] = array(
			"TEXT" => $text,
			"CODE" => $code,
			"TYPE" => "W"
		);
		$this->_arWarnings[$this->_countWarnings] = &$this->_arCommonMessagePool[$this->_countCommonMessages];
		$this->_countWarnings++;
		$this->_countCommonMessages++;
		if( $this->_LogFile
			&& ($this->_logBehaviour & self::MSG_POOL_LOG_WARNINGS) > 0
		) {
			$this->_LogFile->logMessage($text.((!empty($code))?'. Warning code: '.$code:''), LogFile::MSG_TYPE_WARNING);
		}
	}
	public function addError($text, $code = 0) {
		$this->_arCommonMessagePool[$this->_countCommonMessages] = array(
			"TEXT" => $text,
			"CODE" => $code,
			"TYPE" => "E"
		);
		$this->_arErrors[$this->_countErrors] = &$this->_arCommonMessagePool[$this->_countCommonMessages];
		$this->_countErrors++;
		$this->_countCommonMessages++;
		if( $this->_LogFile
			&& ($this->_logBehaviour & self::MSG_POOL_LOG_ERRORS) > 0
		) {
			$this->_LogFile->logMessage($text.((!empty($code))?'. Error code: '.$code:''), LogFile::MSG_TYPE_ERROR);
		}
	}

	/**
	 * @param \ErrorException $Exception
	 * @throws \ErrorException
	 */
	public function throwErrorException(\ErrorException $Exception) {
		if($Exception instanceof AError) {
			$class = get_class($Exception);
			/** @var AError $class */
			$errorCode = $class::LANG_PREFIX.$Exception->getCode();
			$this->addError($Exception->getMessage(), $errorCode);
			throw $Exception;
		}
		if($Exception instanceof \ErrorException) {
			$this->addError($Exception->getMessage(), $Exception->getCode());
			throw $Exception;
		}
	}

	/**
	 * @param \ErrorException $Exception
	 */
	public function addErrorException(\ErrorException $Exception){
		if($Exception instanceof AError) {
			$class = get_class($Exception);
			/** @var AError $class */
			$errorCode = $class::LANG_PREFIX.$Exception->getCode();
			$this->addError($Exception->getMessage(), $errorCode);
		}
		elseif($Exception instanceof \ErrorException) {
			$this->addError($Exception->getMessage(), $Exception->getCode());
		}
	}

	/**
	 * @param \ErrorException $Exception
	 * @param int $debugLevel
	 */
	public function addWarningException(\ErrorException $Exception, $debugLevel = self::MSG_POOL_MAX_DBG_LVL) {
		$debugLevel = intval($debugLevel);
		if($debugLevel > $this->_debugLevel) {
			return;
		}
		if($Exception instanceof AError) {
			/** @var AError $class */
			$class = get_class($Exception);
			$errorCode = $class::LANG_PREFIX.$Exception->getCode();
			$this->addWarning($Exception->getMessage(), $errorCode, $debugLevel);
		}
		elseif($Exception instanceof \ErrorException) {
			$this->addWarning($Exception->getMessage(), $Exception->getCode(), $debugLevel);
		}
	}


	/**
	 * Данный метод устарел, поскольку правильное его название getLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	public function getLastMessage($return = 'TEXT') {
		return $this->getLastNotice($return);
	}

	public function getLastNotice($return = 'TEXT'){
		$arLastNotice = $this->_arNotices[$this->_countNotices-1];
		switch($return) {
			case 'TEXT':
			case 'CODE':
			case 'TYPE':
				break;
			case 'ALL':
			case 'ARRAY':
				return $arLastNotice;
				break;
			default:
				$return = 'TEXT';
				break;
		}
		return $arLastNotice[$return];
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

	/**
	 * Данный метод устарел. Верное его название popLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	public function popLastMessage($return = 'TEXT') {
		return $this->popLastNotice($return);
	}

	public function popLastNotice($return = 'TEXT') {
		$arLastMessage = $this->getLastNotice($return);
		if($this->_countNotices > 0) {
			unset($this->_arNotices[$this->_countNotices-1]);
			$this->_countNotices--;
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

	/**
	 * Метод устарел. Правильный метод getNotices
	 * @deprecated
	 * @return array
	 */
	public function getMessages() {
		return $this->_arNotices;
	}
	public function getNotices() {
		return $this->_arNotices;
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

	public function countNotices() {
		return $this->_countNotices;
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

	/**
	 * Метод устарел. Правильный метод clearNotices
	 * @deprecated
	 */
	public function clearMessages() {
		$this->clearNotices();
	}


	public function clearNotices() {
		$this->_arErrors = array();
		foreach($this->_arCommonMessagePool as $key => &$arr) {
			if($arr["TYPE"]=="N") {
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
		$this->_arNotices = array();
		$this->_arErrors = array();
		$this->_arWarnings = array();
		$this->_arCommonMessagePool = array();
	}
}



/**
 * @package OBX\Core
 */
class MessagePoolStatic implements IMessagePoolStatic {
	static protected $MessagePool = array();

	/**
	 * @return CMessagePool
	 */
	final static public function getMessagePool() {
		$className = get_called_class();
		if( !isset(self::$MessagePool[$className]) ) {
			self::$MessagePool[$className] = new MessagePool;
		}
		return self::$MessagePool[$className];
	}
	final static public function setMessagePool($MessPool) {
		$className = get_called_class();
		if($MessPool instanceof MessagePool) {
			self::$MessagePool[$className] = $MessPool;
		}
	}
	static public function registerLogFile(LogFile $LogFile) {
		return self::getMessagePool()->registerLogFile($LogFile);
	}
	static public function setLogBehaviour($behaviour) {
		return self::getMessagePool()->setLogBehaviour($behaviour);
	}
	static public function getLogBehaviour($behaviour) {
		return self::getMessagePool()->getLogBehaviour($behaviour);
	}
	static public function setDebugLevel($level) {
		return self::getMessagePool()->setDebugLevel($level);
	}
	static public function getDebugLevel($level) {
		return self::getMessagePool()->getDebugLevel($level);
	}
	static public function getLogFile() {
		return self::getMessagePool()->getLogFile();
	}

	/**
	 * Метод устарел. правильный метод addNotice
	 * @param $text
	 * @param int $code
	 * @param int $debugLevel
	 * @deprecated
	 */
	static public function addMessage($text, $code = 0, $debugLevel = 0) {
		self::getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	static public function addNotice($text, $code = 0, $debugLevel = 0) {
		self::getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	static public function addError($text, $code = 0) {
		self::getMessagePool()->addError($text, $code);
	}

	/**
	 * @param \ErrorException $Exception
	 * @throws \ErrorException
	 */
	static public function throwErrorException(\ErrorException $Exception){
		self::getMessagePool()->throwErrorException($Exception);
	}
	/**
	 * @param \ErrorException $Exception
	 */
	static public function addErrorException(\ErrorException $Exception){
		self::getMessagePool()->addErrorException($Exception);
	}
	static public function addWarningException(\ErrorException $Exception, $debugLevel = MessagePool::MSG_POOL_MAX_DBG_LVL) {
		self::getMessagePool()->addWarningException($Exception, $debugLevel);
	}
	static public function addWarning($text, $code = 0, $debugLevel = 0) {
		self::getMessagePool()->addWarning($text, $code, $debugLevel);
	}
	static public function getLastError($return = 'TEXT') {
		return self::getMessagePool()->getLastError($return);
	}
	static public function getLastWarning($return = 'TEXT') {
		return self::getMessagePool()->getLastWarning($return);
	}

	/**
	 * Метод устарел. Правильный метод getLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	static public function getLastMessage($return = 'TEXT') {
		return self::getMessagePool()->getLastNotice($return);
	}
	static public function getLastNotice($return = 'TEXT') {
		return self::getMessagePool()->getLastNotice($return);
	}
	static public function popLastError($return = 'TEXT') {
		return self::getMessagePool()->popLastError($return);
	}
	static public function popLastWarning($return = 'TEXT') {
		return self::getMessagePool()->popLastWarning($return);
	}
	static public function popLastMessage($return = 'TEXT') {
		return self::getMessagePool()->popLastNotice($return);
	}

	/**
	 * Метод устарел. Правильный метод getNotices
	 * @return array
	 * @deprecated
	 */
	static public function getMessages() {
		return self::getMessagePool()->getNotices();
	}
	static public function getNotices() {
		return self::getMessagePool()->getNotices();
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

	/**
	 * Метод устарел. Правильный метод countNotices
	 * @return int
	 * @deprecated
	 */
	static public function countMessages() {
		return self::getMessagePool()->countNotices();
	}
	static public function countNotices() {
		return self::getMessagePool()->countNotices();
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
	/**
	 * Метод устарел. Правильный метод clearNotices
	 * @deprecated
	 */
	static public function clearMessages() {
		self::getMessagePool()->clearNotices();
	}
	static public function clearNotices() {
		self::getMessagePool()->clearNotices();
	}
	static public function clearErrors() {
		self::getMessagePool()->clearErrors();
	}
	static public function clearWarnings() {
		self::getMessagePool()->clearWarnings();
	}
	static public function clearMessagePool() {
		self::getMessagePool()->clearMessagePool();
	}
}

class MessagePoolDecorator implements IMessagePool {
	/**
	 * @var null|CMessagePool
	 */
	protected $MessagePool = null;

	/**
	 * @return CMessagePool
	 */
	public function getMessagePool() {
		if($this->MessagePool == null) {
			$this->MessagePool = new MessagePool;
		}
		return $this->MessagePool;
	}
	public function setMessagePool($MessPool) {
		if($MessPool instanceof MessagePool) {
			$this->MessagePool = $MessPool;
		}
	}

	public function registerLogFile(LogFile $LogFile) {
		return $this->getMessagePool()->registerLogFile($LogFile);
	}
	public function getLogFile() {
		return $this->getMessagePool()->getLogFile();
	}
	public function setLogBehaviour($behaviour) {
		return $this->getMessagePool()->setLogBehaviour($behaviour);
	}
	public function getLogBehaviour($behaviour) {
		return $this->getMessagePool()->getLogBehaviour($behaviour);
	}
	public function setDebugLevel($level) {
		return $this->getMessagePool()->setDebugLevel($level);
	}
	public function getDebugLevel($level) {
		return $this->getMessagePool()->getDebugLevel($level);
	}

	/**
	 * Метод устарел. Праивльный метод addNotice
	 * @param $text
	 * @param int $code
	 * @param int $debugLevel
	 * @deprecated
	 */
	public function addMessage($text, $code = 0, $debugLevel = 0) {
		$this->getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	public function addNotice($text, $code = 0, $debugLevel = 0) {
		$this->getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	public function addError($text, $code = 0) {
		$this->getMessagePool()->addError($text, $code);
	}

	/**
	 * @param \ErrorException $Exception
	 * @throws \ErrorException
	 */
	public function throwErrorException(\ErrorException $Exception){
		$this->getMessagePool()->throwErrorException($Exception);
	}
	/**
	 * @param \ErrorException $Exception
	 */
	public function addErrorException(\ErrorException $Exception){
		$this->getMessagePool()->addErrorException($Exception);
	}
	public function addWarningException(\ErrorException $Exception, $debugLevel = MessagePool::MSG_POOL_MAX_DBG_LVL) {
		$this->getMessagePool()->addWarningException($Exception, $debugLevel);
	}
	public function addWarning($text, $code = 0, $debugLevel = 0) {
		$this->getMessagePool()->addWarning($text, $code, $debugLevel);
	}
	public function getLastError($return = 'TEXT') {
		return $this->getMessagePool()->getLastError($return);
	}
	public function getLastWarning($return = 'TEXT') {
		return $this->getMessagePool()->getLastWarning($return);
	}

	/**
	 * Метод устарел. Праивльный метод getLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	public function getLastMessage($return = 'TEXT') {
		return $this->getMessagePool()->getLastNotice($return);
	}
	public function getLastNotice($return = 'TEXT') {
		return $this->getMessagePool()->getLastNotice($return);
	}
	public function popLastError($return = 'TEXT') {
		return $this->getMessagePool()->popLastError($return);
	}
	public function popLastWarning($return = 'TEXT') {
		return $this->getMessagePool()->popLastWarning($return);
	}

	/**
	 * Метод устарел. Правильный метод popLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	public function popLastMessage($return = 'TEXT') {
		return $this->getMessagePool()->popLastNotice($return);
	}
	public function popLastNotice($return = 'TEXT') {
		return $this->getMessagePool()->popLastNotice($return);
	}

	/**
	 * Метод устарел. Праивльный метод getNotices
	 * @return array
	 * @deprecated
	 */
	public function getMessages() {
		return $this->getMessagePool()->getNotices();
	}
	public function getNotices() {
		return $this->getMessagePool()->getNotices();
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

	/**
	 * Метод устарел. Правильный метод countNotices
	 * @return int
	 * @deprecated
	 */
	public function countMessages() {
		return $this->getMessagePool()->countNotices();
	}
	public function countNotices() {
		return $this->getMessagePool()->countNotices();
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

	/**
	 * Метод устарел. Праивльный метод clearNotices
	 * @deprecated
	 */
	public function clearMessages() {
		$this->getMessagePool()->clearNotices();
	}
	public function clearNotices() {
		$this->getMessagePool()->clearNotices();
	}
	public function clearErrors() {
		$this->getMessagePool()->clearErrors();
	}
	public function clearWarnings() {
		$this->getMessagePool()->clearWarnings();
	}
	public function clearMessagePool() {
		$this->getMessagePool()->clearMessagePool();
	}
}
