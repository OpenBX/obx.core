<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\Xml;

use OBX\Core\Exceptions\AError;

class ParserError extends AError {
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_XML_PARSER_ERROR_';
	const E_XML_FILE_NOT_FOUND = 1;
	const E_XML_FILE_CANT_OPEN = 2;
	const E_TMP_TBL_WRONG_NAME = 3;
	const E_TMP_TBL_EXISTS = 4;
	const E_ADD_ATTR_ON_EXISTS_TBL = 5;
	const E_ADD_IDX_NO_EXISTS_TBL = 6;
	const E_WRONG_ATTR_NAME = 7;
	const E_ATTR_EXISTS = 8;
	const E_XML_FILE_EXT_NOT_ALLOWED = 9;
	const E_WRONG_PHP_MB_STR_FUNC_OVERLOAD = 10;
}