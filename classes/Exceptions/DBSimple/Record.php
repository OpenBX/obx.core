<?php
namespace OBX\Core\Exceptions\DBSimple;
use OBX\Core\Exceptions\AError;

class RecordError extends AError
{
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_DBS_RECORD_ERROR';

	const E_RECORD_ENTITY_NOT_SET = 1;
	const E_CANT_READ_FROM_DB_RESULT = 2;
	const E_WRONG_DB_RESULT_ENTITY = 3;
	const E_CANT_SET_PRIMARY_KEY_VALUE = 4;
	const E_CANT_FIND_RECORD = 5;
	const E_SAVE_FAILED = 6;
}