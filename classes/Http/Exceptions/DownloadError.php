<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 28.11.13
 * Time: 18:18
 */

namespace OBX\Core\Http\Exceptions;
use OBX\Core\Exceptions\AError;


class DownloadError extends AError {
	const E_NO_ACCESS_DWN_FOLDER = 1;
	const E_WRONG_PROTOCOL = 2;
	const E_CONN_FAIL = 3;
	const E_CANT_OPEN_DWN_FILE = 4;
	const E_CANT_WRT_2_DWN_FILE = 5;

	static public function getLangMessage($errorCode) {
		$message = '';
		if( self::$_arLangMessages === null ) {
			self::$_arLangMessages = IncludeModuleLangFile(__FILE__, false, true);
		}
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
} 