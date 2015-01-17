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
} 