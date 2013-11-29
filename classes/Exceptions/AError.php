<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 29.11.13
 * Time: 18:04
 */

namespace OBX\Core\Exceptions;


abstract class AError extends \ErrorException {

	static protected $_arLangMessages = null;

	/**
	 * @param string $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param int $severity [optional] The severity level of the exception.
	 * @param string $filename [optional] The filename where the exception is thrown.
	 * @param int $lineno [optional] The line number where the exception is thrown.
	 * @param \Exception $previous [optional] The previous exception used for the exception chaining.
	 */
	public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null) {
		if(empty($message)) {
			$message = static::getLangMessage($code);
		}
		parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
	}

	static abstract public function getLangMessage($errorCode);
	/*
	 * this method must be implemented like this example:
		 static public function getLangMessage($errorCode) {
			if( self::$_arLangMessages === null ) {
				self::$_arLangMessages = IncludeModuleLangFile(__FILE__, false, true);
			}
			$message = '';
			switch($errorCode) {
				case self::E_NO_ACCESS_DWN_FOLDER:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_DWN_E_NO_ACCESS_DWN_FOLDER'];
					break;
				case self::E_WRONG_PROTOCOL:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_DWN_E_WRONG_PROTOCOL'];
					break;
				case self::E_CONN_FAIL:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_DWN_E_CONN_FAIL'];
					break;
				case self::E_CANT_OPEN_DWN_FILE:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_DWN_E_CANT_OPEN_DWN_FILE'];
					break;
				case self::E_CANT_WRT_2_DWN_FILE:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_DWN_E_CANT_WRT_2_DWN_FILE'];
					break;
			}
			return $message;
		}
	 */
} 