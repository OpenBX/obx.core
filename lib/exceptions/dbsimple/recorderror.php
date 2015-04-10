<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core\Exceptions\DBSimple;
use OBX\Core\Exceptions\AError;

class RecordError extends AError
{
	const FILE = __FILE__;
	const ID = 'OBX_CORE_DBS_RECORD_ERROR_';

	const E_RECORD_ENTITY_NOT_SET = 1;
	const E_READ_NOT_DBS_RESULT = 2;
	const E_READ_NO_IDENTITY_FIELD = 3;
	const E_WRONG_DB_RESULT_ENTITY = 4;
	const E_SET_PRIMARY_KEY_VALUE = 5;
	const E_FIND_RECORD = 6;
	const E_SAVE_FAILED = 7;
	const E_GET_WRONG_FIELD = 8;
	const E_GET_LAZY_FIELD = 9;
	const E_SET_WRONG_FIELD = 10;
	const E_READ_BY_UQ_NOT_ALL_FLD = 11;
}