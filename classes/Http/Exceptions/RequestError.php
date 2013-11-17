<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 18.11.13
 * Time: 0:18
 */

namespace OBX\Core\Http\Exceptions;


class RequestError extends \ErrorException {
	const E_CURL_NOT_INSTALLED = 1;
	const E_WRONG_FILE_PATH = 2;
	const E_CANT_OPEN_FILE = 3;
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
	public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous) {
		if( self::$_arLangMessages === null ) {
			self::$_arLangMessages = IncludeModuleLangFile(__FILE__, true);
		}
		if(empty($message)) {
			switch($code) {
				case self::E_WRONG_FILE_PATH:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_REQ_E_WRONG_FILE_PATH'];
					break;
				case self::E_CANT_OPEN_FILE:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_REQ_E_CANT_OPEN_FILE'];
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