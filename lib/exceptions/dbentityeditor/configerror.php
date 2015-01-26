<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\DBEntityEditor;
use OBX\Core\Exceptions\AError;

class ConfigError extends AError {
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

	const E_CFG_TBL_WRG_NAME = 10;
	const E_CFG_TBL_WRG_ALIAS = 11;

	const E_CFG_FLD_WRG_NAME = 21;
	const E_CFG_FLD_WRG_TYPE = 22;

	const E_CFG_WRG_IDX = 30;
	const E_CFG_WRG_IDX_FLD = 31;
	const E_CFG_WRG_UQ_IDX = 35;
	const E_CFG_WRG_UQ_IDX_FLD = 36;

	const E_CFG_REF_WRG_NAME = 40;
	const E_CFG_REF_WRG_ALIAS = 41;
	const E_CFG_REF_ALIAS_NOT_UQ = 42;
	const E_CFG_REF_READ_ENTITY_FAIL = 43;
	const E_CFG_REF_WRG_JOIN_TYPE = 44;
	const E_CFG_REF_WRG_CONDITION = 45;

	const E_CFG_WRG_DEF_SORT = 50;

	const E_GET_FLD_NOT_FOUND = 70;

	//const E_CFG_
}