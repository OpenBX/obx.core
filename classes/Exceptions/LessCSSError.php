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


class LessCSSError extends AError {
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_LESSCSS_ERROR_';

	const E_TEMPLATE_NOT_FOUND = 1;
	const E_LESS_JS_FILE_NOT_FOUND = 2;
} 