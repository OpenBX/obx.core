<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;

use OBX\Core\Exceptions\AError;


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
	 * ƒанный метод помечен как устаревший, поскольку верное его название addNotice
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
	 * @param \Exception $Exception
	 * @deprecated не желательно пользоватьс€ этим способом. line_no будет всегда указывать в этот метод
	 * @throws \Exception
	 */
	public function throwErrorException(\Exception $Exception) {
		if($Exception instanceof AError) {
			$class = get_class($Exception);
			/** @var AError $class */
			$errorCode = $class::ID.$Exception->getCode();
			$this->addError($Exception->getMessage(), $errorCode);
			throw $Exception;
		}
		if($Exception instanceof \Exception) {
			$this->addError($Exception->getMessage(), $Exception->getCode());
			throw $Exception;
		}
	}

	/**
	 * @param \Exception $Exception
	 * @param string $exceptionTextPrefix
	 */
	public function addErrorException(\Exception $Exception, $exceptionTextPrefix = ''){
		if($Exception instanceof AError) {
			$this->addError($exceptionTextPrefix.$Exception->getMessage(), $Exception->getFullCode());
		}
		elseif($Exception instanceof \Exception) {
			$this->addError($exceptionTextPrefix.$Exception->getMessage(), $Exception->getCode());
		}
	}

	/**
	 * @param \Exception $Exception
	 * @param int $debugLevel
	 * @param string $exceptionTextPrefix
	 */
	public function addWarningException(\Exception $Exception, $debugLevel = self::MSG_POOL_MAX_DBG_LVL, $exceptionTextPrefix = '') {
		$debugLevel = intval($debugLevel);
		if($debugLevel > $this->_debugLevel) {
			return;
		}
		if($Exception instanceof AError) {
			/** @var AError $class */
			$class = get_class($Exception);
			$errorCode = $class::ID.$Exception->getCode();
			$this->addWarning($exceptionTextPrefix.$Exception->getMessage(), $errorCode, $debugLevel);
		}
		elseif($Exception instanceof \Exception) {
			$this->addWarning($exceptionTextPrefix.$Exception->getMessage(), $Exception->getCode(), $debugLevel);
		}
	}


	/**
	 * ƒанный метод устарел, поскольку правильное его название getLastNotice
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
	 * ƒанный метод устарел. ¬ерное его название popLastNotice
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
	 * ћетод устарел. ѕравильный метод getNotices
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
	 * ћетод устарел. ѕравильный метод clearNotices
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

