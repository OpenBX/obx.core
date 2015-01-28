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

class GeneratorError extends AError {
	const _DIR_ = __DIR__;
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_DB_ENT_GEN_ERR_';

	const E_CFG_INCORRECT = 1;

	const E_ADD_MET_WRG_NAME = 2;
	const E_ADD_MET_EXISTS = 3;
	const E_ADD_MET_WRG_ACCESS = 4;
	const E_ADD_MET_WRG_ARG_NAME = 5;
	const E_ADD_MET_WRG_ARG_TYPE = 6;
}