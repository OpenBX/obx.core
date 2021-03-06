<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Test\BXEntityEditor;

use OBX\Core\Test\TestCase;
use OBX\Core\DBEntityEditor\Config;
use OBX\Core\SimpleBenchMark;




class TestConfig extends TestCase {
	const _DIR_ = __DIR__;


	const TEST_ENTITY = '/bitrix/modules/obx.core/data_entity/TestEntity.json';
	const TEST_ENTITY_SAVE = '/bitrix/modules/obx.core/test/BXEntityEditor/SavedTestEntity.json';

	public function _test() {
		$config = new Config(self::TEST_ENTITY);
	}

	public function testGenerateCreateSql() {
		$config = new Config(self::TEST_ENTITY);
		$sqlCreateEntity = $config->getCreateTableCode();
		$expectedSql = <<<SQL
create table if not exists obx_core_test_entity(
	ID int(11) unsigned not null auto_increment,
	CODE varchar(15) not null,
	NAME varchar(255) not null,
	SORT int(11) not null default '100',
	SOME_BCHAR char(1) not null default 'Y',
	CREATE_TIME datetime null,
	TIMESTAMP_X datetime null,
	SOME_TEXT text null,
	IBLOCK_ID int(11) not null,
	USER_ID int(11) null,
	CUSTOM_CK varchar(255) null,
	VALIDATION varchar(255) null,
	primary key(ID),
	unique obx_core_test_entity_code_bchar(CODE, SOME_BCHAR),
	index obx_core_test_entity_code(CODE)
);

SQL;
		$this->assertEquals($expectedSql, $sqlCreateEntity);
	}

	public function testGetConfigContent() {
		$config = new Config(self::TEST_ENTITY);
		$json = $config->getConfigContent();
		$arJson = json_decode($json, true);
		$this->assertNotEmpty($arJson['fields']['SORT']['required_error']['lang']);
		$this->assertEquals('%_SORT_IS_EMPTY', $arJson['fields']['SORT']['required_error']['lang']);
	}

	public function testSaveConfigFile() {
		$config = new Config(self::TEST_ENTITY);
		$config->saveConfigFile(self::TEST_ENTITY_SAVE);
		$savedConfigContent = file_get_contents(OBX_DOC_ROOT.self::TEST_ENTITY_SAVE);
		$savedConfigContent = str_replace(
			'"TestSecondEntity.json"',
			'"/bitrix/modules/obx.core/data_entity/TestSecondEntity.json"',
			$savedConfigContent
		);
		file_put_contents(OBX_DOC_ROOT.self::TEST_ENTITY_SAVE, $savedConfigContent);

		$configReloaded = new Config(self::TEST_ENTITY_SAVE);
		$this->assertEquals($config->getNamespace(), $configReloaded->getNamespace());
		$this->assertEquals($config->getClass(), $configReloaded->getClass());
	}
}