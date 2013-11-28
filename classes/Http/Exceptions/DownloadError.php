<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 28.11.13
 * Time: 18:18
 */

namespace OBX\Core\Http\Exceptions;


class DownloadError extends \ErrorException {
	const E_NO_ACCESS_DWN_FOLDER = 1;

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
		if( self::$_arLangMessages === null ) {
			self::$_arLangMessages = IncludeModuleLangFile(__FILE__, false, true);
		}
		if(empty($message)) {
			switch($code) {
				case self::E_NO_ACCESS_DWN_FOLDER:
					$message = self::$_arLangMessages['OBX_CORE_HTTP_E_NO_ACCESS_DWN_FOLDER'];
					break;
			}
		}
		parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
	}
} 