<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Exceptions\PhpGenerator;

use OBX\Core\Exceptions\AError;

class PhpClassError extends AError {
	const FILE = __FILE__;
	const ID = 'OBX_CORE_GEN_PHP_CLASS_ERR_';

	const E_WRG_NAMESPACE = 1;
	const E_WRG_CLASS = 2;
	const E_WRG_CLASS_NAME = 3;
	const E_WRG_BASE_CLASS = 4;
	const E_SET_WRG_IMPL = 11;
	const E_SET_IMPL_IF_EXISTS = 12;
	const E_SET_WRG_USES = 13;
	const E_SET_USES_CLASS_EXIST = 14;
	const E_SET_USES_ALIAS_EXIST = 15;

	const E_ADD_MET_WRG_NAME = 31;
	const E_ADD_MET_EXISTS = 32;
	const E_ADD_MET_WRG_ACCESS = 33;
	const E_ADD_MET_WRG_ARG_NAME = 34;
	const E_ADD_MET_WRG_ARG_TYPE = 35;

	const E_ADD_VAR_WRG_ACCESS = 40;
	const E_ADD_VAR_WRG_NAME = 41;
	const E_ADD_VAR_INIT_VAL_NOT_CONST = 42;

	const E_ADD_CONST_WRG_NAME = 51;
	const E_ADD_CONST_INIT_VAL = 52;

	const E_GET_MET_NO_FOUND = 61;
	const E_GET_VAR_NO_FOUND = 62;
	const E_GET_CONST_NO_FOUND = 63;
}