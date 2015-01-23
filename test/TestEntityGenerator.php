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
use OBX\Core\DBEntityEditor as Editor;
use OBX\Core\SimpleBenchMark;



class TestEntityGenerator extends TestCase {
	const _DIR_ = __DIR__;

	public function test() {
		$generator = new Editor\Config('/bitrix/modules/obx.core/dbs_entity/TestEntity.json');
	}
} 