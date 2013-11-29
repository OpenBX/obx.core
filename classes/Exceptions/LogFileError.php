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

class LogFileError extends AError {
	const E_WRONG_PATH = 1;
	const E_PERM_DENIED = 2;
	const E_CANT_OPEN = 3;
	const E_SENDER_IS_EMPTY = 4;

	static public function getLangMessage($errorCode) {
		if( static::$_arLangMessages === null ) {
			static::$_arLangMessages = IncludeModuleLangFile(__FILE__, false, true);
		}
		if(empty($message)) {
			switch($errorCode) {
				case self::E_WRONG_PATH:
					$message = static::$_arLangMessages['OBX_CORE_LOGFILE_E_WRONG_PATH'];
					break;
				case self::E_PERM_DENIED:
					$message = static::$_arLangMessages['OBX_CORE_LOGFILE_E_PERM_DENIED'];
					break;
				case self::E_CANT_OPEN:
					$message = static::$_arLangMessages['OBX_CORE_LOGFILE_E_CANT_OPEN'];
					break;
				case self::E_SENDER_IS_EMPTY:
					$message = static::$_arLangMessages['OBX_CORE_LOGFILE_E_SENDER_IS_EMPTY'];
					break;
			}
		}
		return $message;
	}
}