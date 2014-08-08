<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\LessCss;

use OBX\Core\Exceptions\AError;

class LessCssError extends AError {
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_LESSCSS_ERROR_';

	const E_SITE_NOT_FOUND = 1;
	const E_LESS_JS_FILE_NOT_FOUND = 2;
} 