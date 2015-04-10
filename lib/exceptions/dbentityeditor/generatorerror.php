<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Exceptions\DBEntityEditor;

use OBX\Core\Exceptions\AError;

class GeneratorError extends AError {
	const FILE = __FILE__;
	const ID = 'OBX_CORE_DB_ENT_GEN_ERR_';

	const E_CFG_INCORRECT = 1;
	const E_CLASS_SAVE_FAILED = 2;

}