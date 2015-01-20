<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\Test;

use Bitrix\Main\Loader;
use OBX\Core\DBSimple as DBS;
use OBX\Core\SimpleBenchMark;

Loader::includeModule('obx.core');

class TestEntityGenerator extends TestCase {
	const _DIR_ = __DIR__;

	public function test() {
		$generator = new DBS\EntityGenerator('/bitrix/modules/obx.core/dbs_entity/TestEntity.json');
	}
} 