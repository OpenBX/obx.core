<?php
namespace OBX\Core\Exceptions\PhpGenerator;

use OBX\Core\Exceptions\AError;

class ClassError extends AError {
	const _DIR_ = __DIR__;
	const LANG_PREFIX = 'OBX_CORE_GEN_PHP_CLASS_ERR';

	const E_ADD_MET_WRG_NAME = 11;
	const E_ADD_MET_EXISTS = 12;
	const E_ADD_MET_WRG_ACCESS = 13;
	const E_ADD_MET_WRG_ARG_NAME = 14;
	const E_ADD_MET_WRG_ARG_TYPE = 15;

	const E_ADD_VAR_WRG_ACCESS = 20;
	const E_ADD_VAR_WRG_NAME = 21;
	const E_ADD_VAR_INIT_VAL_NOT_CONST = 22;
}