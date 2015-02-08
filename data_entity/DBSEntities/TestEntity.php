<?php

namespace OBX\Core\DBSEntities;

use OBX\Core\DBSimple\Entity as Entity;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class TestEntity extends Entity
{

	protected $_entityModuleID = 'obx.core';
	protected $_entityEventsID = 'TestEntityRow';
	protected $_mainTable = 'E';
	protected $_mainTablePrimaryKey = 'ID';
	protected $_mainTableAutoIncrement = 'ID';
	protected $_arTableList = array(
		'E' => 'obx_core_test_entity',
		'IB' => 'b_iblock',
		'U' => 'b_user',
		'S' => 'obx_core_test_second_entity',
	);
	protected $_arTableLinks = array(
		0 => array(
			0 => array('IB' => 'ID'),
			1 => array('E' => 'IBLOCK_ID'),
		),
		1 => array(
			0 => array('U' => 'ID'),
			1 => array('E' => 'USER_ID'),
		),
		2 => array(
			0 => array('S' => 'TEST_ENTITY_ID'),
			1 => array('E' => 'ID'),
		),
	);
	protected $_arTableLeftJoin = array(
		'IB' => 'E.IBLOCK_ID = IB.ID',
		'U' => 'E.USER_ID = U.ID',
		'S' => 'E.ID = S.TEST_ENTITY_ID',
	);
	protected $_arSelectDefault = array(
		0 => 'ID',
		1 => 'CODE',
		2 => 'NAME',
		3 => 'SORT',
		4 => 'SOME_BCHAR',
		5 => 'CREATE_TIME',
		6 => 'TIMESTAMP_X',
		7 => 'SOME_TEXT',
		8 => 'IBLOCK_ID',
		9 => 'IBLOCK_CODE',
		10 => 'IBLOCK_NAME',
		11 => 'USER',
		12 => 'CUSTOM_CK',
		13 => 'VALIDATION',
	);
	protected $_arTableUnique = array(
		'obx_core_test_entity_code_bchar' => array(
			0 => 'CODE',
			1 => 'SOME_BCHAR',
		),
	);
	protected $_arSortDefault = array(
		'SORT' => 'ASC',
		'ID' => 'ASC',
	);
	protected $_arGroupByFields = array(0 => 'ID');
	protected $_arTableFieldsDefault = array(
		'SORT' => 100,
		'SOME_BCHAR' => 'Y',
	);
	protected $_arTableJoinNullFieldDefaults = array(
		'SORT' => 100,
		'SOME_BCHAR' => 'Y',
		'USER' => 'no-name',
	);
	protected $_arDBSimpleLangMessages = null;
	protected $_arFieldsDescription = null;

	public function __construct()
	{

		$this->_arDBSimpleLangMessages = array(
			'REQ_FLD_CODE' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_CODE_IS_EMPTY'),
				'CODE' => 1,
			),
			'REQ_FLD_NAME' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_NAME_IS_EMPTY'),
				'CODE' => 2,
			),
			'REQ_FLD_SORT' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_SORT_IS_EMPTY'),
				'CODE' => 3,
			),
			'REQ_FLD_SOME_BCHAR' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_ERR_REQUIRED_SOME_BCHAR'),
				'CODE' => 4,
			),
			'DUP_ADD_obx_core_test_entity_code_bchar' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_E_DUP_ADD_UQ_CODE_BCHAR'),
				'CODE' => 5,
			),
			'DUP_UPD_obx_core_test_entity_code_bchar' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_E_DUP_UPD_UQ_CODE_BCHAR'),
				'CODE' => 6,
			),
			'NOTHING_TO_DELETE' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_E_NOTHING_TO_DELETE'),
				'CODE' => 7,
			),
			'NOTHING_TO_UPDATE' => array(
				'TYPE' => 'E',
				'TEXT' => Loc::getMessage('OBX_DBS_TEST_ENTITY_E_NOTHING_TO_UPDATE'),
				'CODE' => 8,
			),
		);
		$this->_arFieldsDescription = array(
			'ID' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_ID'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_ID'),
			),
			'CODE' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_CODE'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_CODE'),
			),
			'NAME' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_NAME'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_NAME'),
			),
			'SORT' => array(
				'NAME' => Loc::getMessage('&_FLD_TITLE_SORT'),
				'DESCRIPTION' => Loc::getMessage('&_FLD_DSCR_SORT'),
			),
			'SOME_BCHAR' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_SOME_BCHAR'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_SOME_BCHAR'),
			),
			'CREATE_TIME' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_CREATE_TIME'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_CREATE_TIME'),
			),
			'TIMESTAMP_X' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_TIMESTAMP_X'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_TIMESTAMP_X'),
			),
			'SOME_TEXT' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_SOME_TEXT'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_SOME_TEXT'),
			),
			'IBLOCK_ID' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_IBLOCK_ID'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_IBLOCK_ID'),
			),
			'IBLOCK_CODE' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_IBLOCK_CODE'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_IBLOCK_CODE'),
			),
			'IBLOCK_NAME' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_IBLOCK_CODE'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_IBLOCK_CODE'),
			),
			'USER_ID' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_USER'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_USER'),
			),
			'USER' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_USER'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_USER'),
			),
			'CUSTOM_CK' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_CUSTOM_CK'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_CUSTOM_CK'),
			),
			'VALIDATION' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_VALIDATION'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_VALIDATION'),
			),
			'SEC_ENT_JSON' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_SEC_ENT_JSON'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_SEC_ENT_JSON'),
			),
			'SEC_ENT_CNT' => array(
				'NAME' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_TITLE_SEC_ENT_JSON'),
				'DESCRIPTION' => Loc::getMessage('OBX_DBS_TEST_ENTITY_FLD_DSCR_SEC_ENT_JSON'),
			),
		);

	}

}
