<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\Test\BXEntityEditor;


use OBX\Core\DBEntityEditor\Config;
use OBX\Core\Test\TestCase;
use OBX\Core\DBEntityEditor\GeneratorDBS;

class TestGenerator extends TestCase
{
	const _DIR_ = __DIR__;
	const TEST_ENTITY = '/bitrix/modules/obx.core/data_entity/TestEntity.json';

	public function test() {
		$generator = new GeneratorDBS(new Config(self::TEST_ENTITY));
	}
} 