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
	const TEST_ENTITY_PHP = '/bitrix/modules/obx.core/data_entity/DBSEntities/TestEntity.php';

	public function testGenerateClass() {
		$generator = new GeneratorDBS(new Config(self::TEST_ENTITY));
		if(file_exists(OBX_DOC_ROOT.self::TEST_ENTITY_PHP)) {
			unlink(OBX_DOC_ROOT.self::TEST_ENTITY_PHP);
		}
		$this->assertFileNotExists(OBX_DOC_ROOT.self::TEST_ENTITY_PHP);
		$generator->saveEntityClass();
		$this->assertFileExists(OBX_DOC_ROOT.self::TEST_ENTITY_PHP);
		/** @noinspection PhpIncludeInspection */
		include_once OBX_DOC_ROOT.self::TEST_ENTITY_PHP;
		$this->assertTrue(class_exists(
			$generator->getConfig()->getNamespace()
			.'\\'.$generator->getConfig()->getClass()
		));
	}
} 