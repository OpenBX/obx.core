<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/
namespace OBX\Core\Exceptions;

class LogFileError extends \ErrorException {
	const E_WRONG_PATH = 1;
	const E_PERM_DENIED = 2;
	const E_CANT_OPEN = 3;
	const E_SENDER_IS_EMPTY = 4;

	static protected $_arLangMessages = null;

	/**
	 * @param string $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param int $severity [optional] The severity level of the exception.
	 * @param string $filename [optional] The filename where the exception is thrown.
	 * @param int $lineno [optional] The line number where the exception is thrown.
	 * @param \Exception $previous [optional] The previous exception used for the exception chaining.
	 */
	public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous) {
		if( self::$_arLangMessages === null ) {
			self::$_arLangMessages = IncludeModuleLangFile(__FILE__, false, true);
		}
		if(empty($message)) {
			switch($code) {
				case self::E_WRONG_PATH:
					$message = self::$_arLangMessages['OBX_CORE_LOGFILE_E_WRONG_PATH'];
					break;
				case self::E_PERM_DENIED:
					$message = self::$_arLangMessages['OBX_CORE_LOGFILE_E_PERM_DENIED'];
					break;
				case self::E_CANT_OPEN:
					$message = self::$_arLangMessages['OBX_CORE_LOGFILE_E_CANT_OPEN'];
					break;
				case self::E_SENDER_IS_EMPTY:
					$message = self::$_arLangMessages['OBX_CORE_LOGFILE_E_SENDER_IS_EMPTY'];
					break;
			}
		}
		parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
	}
}