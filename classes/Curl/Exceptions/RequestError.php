<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Curl\Exceptions;


class RequestError extends \ErrorException {
	const E_CURL_NOT_INSTALLED = 1;
	const E_WRONG_PATH = 2;
	const E_PERM_DENIED = 3;
	const E_FILE_NAME_TOO_LOG = 4;
	const E_NO_ACCESS_DWN_FOLDER = 5;
	const E_FILE_SAVE_FAILED = 6;
	static protected $_arLangMessages = null;
	static protected $_bCURLChecked = false;

	/**
	 * @param string $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param int $severity [optional] The severity level of the exception.
	 * @param string $filename [optional] The filename where the exception is thrown.
	 * @param int $lineno [optional] The line number where the exception is thrown.
	 * @param \Exception $previous [optional] The previous exception used for the exception chaining.
	 */
	public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null) {
		if( self::$_arLangMessages === null ) {
			self::$_arLangMessages = IncludeModuleLangFile(__FILE__, false, true);
		}
		if(empty($message)) {
			switch($code) {
				case self::E_CURL_NOT_INSTALLED:
					$message = self::$_arLangMessages['OBX_CORE_CURL_E_NOT_INSTALLED'];
					break;
				case self::E_WRONG_PATH:
					$message = self::$_arLangMessages['OBX_CORE_CURL_E_WRONG_PATH'];
					break;
				case self::E_PERM_DENIED:
					$message = self::$_arLangMessages['OBX_CORE_CURL_E_PERM_DENIED'];
					break;
				case self::E_FILE_NAME_TOO_LOG:
					$message = self::$_arLangMessages['OBX_CORE_CURL_E_FILE_NAME_TOO_LOG'];
					break;
				case self::E_NO_ACCESS_DWN_FOLDER:
					$message = self::$_arLangMessages['OBX_CORE_CURL_E_NO_ACCESS_DWN_FOLDER'];
					break;
				case self::E_FILE_SAVE_FAILED:
					$message = self::$_arLangMessages['OBX_CORE_CURL_E_FILE_SAVE_FAILED'];
					break;
			}
		}
		parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
	}

	static public function checkCURL() {
		if( self::$_bCURLChecked === false ) {
			if( !function_exists('curl_version') ) {
				throw new self('', self::E_CURL_NOT_INSTALLED);
			}
			self::$_bCURLChecked = true;
		}
	}
}