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
	const LANG_PREFIX = 'OBX_DBSIMPLE_ENTITY_GEN_';

	const E_OPEN_CFG_FAILED = 1;
	const E_PARSE_CFG_FAILED = 2;
	const E_CFG_NO_MOD = 3;
	const E_CFG_NO_EVT_ID = 4;
	const E_CFG_WRG_NAMESPACE = 5;
	const E_CFG_WRG_CLASS_NAME = 6;
	const E_CFG_NO_CLASS_PATH = 7;

	const E_CFG_FLD_LIST_IS_EMPTY = 8;

	const E_CFG_TBL_WRG_NAME = 9;
	const E_CFG_TBL_WRG_ALIAS = 10;
	const E_CFG_TBL_ALIAS_NOT_UQ = 11;

	const E_CFG_FLD_WRG_NAME = 12;
	const E_CFG_FLD_WRG_TYPE = 13;

	//const E_CFG_
}