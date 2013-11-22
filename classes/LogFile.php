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
use OBX\Core\Exceptions\LogFileError;
class LogFile {
	protected $_logFile = null;
	protected $_logFilePath = null;
	protected $_logTimeStampFormat = 'Y-m-d H:i:s';
	protected $_sender = null;
	protected $_logMessageFormat = '[%timestamp%] %sender%: %text%';
	protected $_logErrWrnFormat = '[%timestamp%] %sender%: %type%: %text%';

	const MSG_TYPE_ERROR = 'Error';
	const MSG_TYPE_WARNING = 'Warning';
	const MSG_TYPE_NOTE = 'Note';

	const F_REWRITE = 'w';
	const F_APPEND = 'a';
	protected $_defaultMessageType = self::MSG_TYPE_ERROR;

	public function __construct($sender, $logFileRelPath, $logOpenMode = self::F_APPEND, $defaultMessageType = null, $logMessageFormat = null) {
		if( empty($logFileRelPath) ) {
			throw new LogFileError('', LogFileError::E_WRONG_PATH);
		}
		$logFileRelPath = str_replace(array('\\', '//'), '/', $logFileRelPath);
		$logFileRelPath = rtrim($logFileRelPath, '/');
		$logFilePath = $_SERVER['DOCUMENT_ROOT'].$logFileRelPath;
		if( !CheckDirPath($logFilePath) ) {
			throw new LogFileError('', LogFileError::E_PERM_DENIED);
		}
		if(!is_string($sender) || empty($sender)) {
			throw new LogFileError('', LogFileError::E_SENDER_IS_EMPTY);
		}
		if($defaultMessageType !== null) {
			$this->setDefaultMessageType($defaultMessageType);
		}
		switch($logOpenMode) {
			case self::F_APPEND:
			case self::F_REWRITE:
				break;
			default:
				$logOpenMode = self::F_APPEND;
		}
		$logFile = fopen($logFilePath, $logOpenMode);
		if(!$logFile) {
			throw new LogFileError('', LogFileError::E_CANT_OPEN);
		}
		$this->_logFilePath = $logFileRelPath;
		$this->_logFile = $logFile;
		if($logMessageFormat !== null && is_string($logMessageFormat)) {
			$this->_logMessageFormat = $logMessageFormat;
		}
		$this->_sender = $sender;
		$this->_logMessageFormat = str_replace('%sender%', $this->_sender, $this->_logMessageFormat);
	}

	public function __destruct() {
		fclose($this->_logFile);
	}
	protected function __clone() {}


	public function logMessage($text, $type = null, $sender = null) {
		if(null === $this->_logFile) {
			return false;
		}
		if($sender === null || !is_string($sender) || empty($sender)) {
			$sender = $this->_sender;
		}
		switch($type) {
			case self::MSG_TYPE_ERROR:
			case self::MSG_TYPE_WARNING:
			case self::MSG_TYPE_NOTE:
				break;
			default:
				$type = $this->_defaultMessageType;
		}
		fwrite($this->_logFile, str_replace(
			array(
				'%timestamp%',
				'%sender%',
				'%type%',
				'%text%'
			),
			array(
				$this->getLogTimeStamp(),
				$sender,
				$type,
				$text
			),
			(($type == self::MSG_TYPE_NOTE)?$this->_logMessageFormat:$this->_logErrWrnFormat)
		)."\r\n");
	}

	public function setDefaultMessageType($defaultMessageType) {
		switch($defaultMessageType) {
			case self::MSG_TYPE_ERROR:
			case self::MSG_TYPE_WARNING:
			case self::MSG_TYPE_NOTE:
				$this->_defaultMessageType = $defaultMessageType;
				return true;
				break;
		}
		return false;
	}

	public function getLogTimeStamp() {
		return date($this->_logTimeStampFormat, time());
	}

	/**
	 * @param string $format
	 * http://php.net/manual/en/function.date.php
	 * @return bool
	 */
	public function setTimeStampFormat($format) {
		if(!is_string($format) || empty($format)) {
			return false;
		}
		$this->_logTimeStampFormat = $format;
		return true;
	}

	public function setMessageFormat($format) {
		if(!is_string($format) || empty($format)) {
			return false;
		}
		$this->_logErrWrnFormat = $format;
		$this->_logMessageFormat = str_replace(array(
			' %type%: ',
			' %type% ',
			'%type%: ',
			'%type% ',
			' %type%:',
			' %type%',
			'%type%:',
			'%type%',
		), '', $this->_logErrWrnFormat);
		return true;
	}
}
