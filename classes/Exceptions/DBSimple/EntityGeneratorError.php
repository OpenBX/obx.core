<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\DBSimple;
use OBX\Core\Exceptions\AError;

class EntityGeneratorError extends AError {
	const _DIR_ = __DIR__;
	const _FILE_ = __FILE__;

	const E_OPEN_CFG_FAILED = 1;
	const E_CFG_NO_MOD = 2;
	const E_CFG_NO_EVT_ID = 3;
	const E_CFG_NO_NS = 4;
	const E_CFG_NO_CLASS_NAME = 5;
	const E_CFG_NO_CLASS_PATH = 6;
	const E_CFG_TBL_LIST_EMPTY = 7;
	const E_CFG_MAIN_TBL_NOT_SET = 8;
} 