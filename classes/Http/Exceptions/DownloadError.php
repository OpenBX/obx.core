<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Http\Exceptions;
use OBX\Core\Exceptions\AError;


class DownloadError extends AError {
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_HTTP_DWN_ERROR_';
	const E_NO_ACCESS_DWN_FOLDER = 1;
	const E_WRONG_PROTOCOL = 2;
	const E_CONN_FAIL = 3;
	const E_CANT_OPEN_DWN_FILE = 4;
	const E_CANT_WRT_2_DWN_FILE = 5;
	const E_CANT_SAVE_NOT_FINISHED = 6;
	const E_CANT_SAVE_TO_FOLDER = 7;
} 