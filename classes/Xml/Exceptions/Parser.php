<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Xml\Exceptions;

class ParserError extends \ErrorException {
	const XML_FILE_NOT_FOUND = 1;
	const XML_FILE_CANT_OPEN = 2;
	const TMP_TBL_WRONG_NAME = 3;
	const TMP_TBL_EXISTS = 4;
	const E_ADD_ATTR_ON_EXISTS_TBL = 5;
	const E_ADD_IDX_ON_EXISTS_TBL = 6;
	const E_WRONG_ATTR_NAME = 7;
}