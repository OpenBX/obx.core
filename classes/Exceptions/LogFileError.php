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
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_LOGFILE_ERROR_';
	const E_WRONG_PATH = 1;
	const E_PERM_DENIED = 2;
	const E_CANT_OPEN = 3;
	const E_SENDER_IS_EMPTY = 4;
}