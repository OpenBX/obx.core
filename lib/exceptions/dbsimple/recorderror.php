<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\DBSimple;
use OBX\Core\Exceptions\AError;

class RecordError extends AError
{
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_DBS_RECORD_ERROR_';

	const E_RECORD_ENTITY_NOT_SET = 1;
	const E_CANT_READ_FROM_DB_RESULT = 2;
	const E_WRONG_DB_RESULT_ENTITY = 3;
	const E_CANT_SET_PRIMARY_KEY_VALUE = 4;
	const E_CANT_FIND_RECORD = 5;
	const E_SAVE_FAILED = 6;
	const E_GET_WRONG_FIELD = 7;
	const E_SET_WRONG_FIELD = 8;
	const E_CANT_RD_BY_UQ_NOT_ALL_FLD = 9;

}