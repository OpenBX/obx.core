<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\Curl;


use OBX\Core\Exceptions\AError;

class RequestError extends AError {
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_CURL_ERROR_';
	const E_CURL_NOT_INSTALLED = 1;
	const E_WRONG_PATH = 2;
	const E_PERM_DENIED = 3;
	const E_FILE_NAME_TOO_LOG = 4;
	const E_NO_ACCESS_DWN_FOLDER = 5;
	const E_OPEN_DWN_FAILED = 6;
	const E_FILE_SAVE_FAILED = 7;
	const E_FILE_SAVE_NO_RESPONSE = 8;
	static protected $_bCURLChecked = false;

	static public function checkCURL() {
		if( self::$_bCURLChecked === false ) {
			if( !function_exists('curl_version') ) {
				throw new self('', self::E_CURL_NOT_INSTALLED);
			}
			self::$_bCURLChecked = true;
		}
	}
}