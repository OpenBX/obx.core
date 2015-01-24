<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\DBEntityEditor;

use OBX\Core\Exceptions\DBEntityEditor\GeneratorDBSError as Err;

/**
 * Class GeneratorDBS
 * @package OBX\Core\DBEntityEditor
 * Генератор сущности DBSimple
 */
class GeneratorDBS implements IGenerator {
	// DBSimple Vars
	protected $_entityModuleID = null;
	protected $_entityEventsID = null;
	protected $_mainTable = null;
	protected $_mainTablePrimaryKey = null;
	protected $_mainTableAutoIncrement = null;
	protected $_arTableList = null;
	protected $_arTableLinks = null;
	protected $_arTableLeftJoin = null;
	protected $_arTableFields = null;
	protected $_arSelectDefault = null;
	protected $_arTableUnique = null;
	protected $_arSortDefault = null;
	protected $_arTableFieldsDefault = null;
	protected $_arTableFieldsCheck = null;

	protected $_config = null;


	public function __construct(IConfig $config) {
		if( null === $config || !$config->isReadSuccess()) {
			throw new Err('', Err::E_CFG_INCORRECT);
		}
		$this->_config = $config;
		$this->_classPath = $this->_config->getClass();
		$this->_mainTable = $this->_config->getAlias();
		$this->_arTableList = array(
			$this->_mainTable => $this->_config->getTableName()
		);

		// DBSimple Data

//		foreach($this->_fields as $fieldCode => &$field) {
//			$fieldCheckType = $this->cfgField2DBSimpleFieldCheck($field);
//			if(null !== $fieldCheckType) {
//				$this->_arTableFieldsCheck[$field['code']] = $fieldCheckType;
//				$this->_createTable[$field['code']] = array(
//					'data_type' => $this->cfgFieldType2MySQL($field),
//					'deny_null' => ' not null',
//					'default' => ''
//				);
//				if(true === $field['deny_null']) {
//					$this->_createTable[$field['code']]['deny_null'] = ' null';
//				}
//				if(!empty($field['default'])) {
//					$this->_arTableFieldsDefault[$field['code']] = $field['default'];
//					$this->_createTable[$field['code']]['default'] = $this->_arTableFieldsDefault[$field['code']];
//				}
//				if(true === $field['selected_by_default']) {
//					if(null === $this->_arSelectDefault) $this->_arSelectDefault = array();
//					$this->_arSelectDefault[] = $field['code'];
//				}
//			}
//		}
	}

	protected function cfgField2DBSimpleFieldCheck(&$field) {
		$flags = array();
		switch($field['type']) {
			case '':
			case 'ex':
				return null;
			case 'pk_id':
				$flags[] = 'FLD_T_PK_ID';
				break;
			case 'int':
			case 'integer':
				$flags[] = 'FLD_T_INT';
				break;
			case 'char':
				$flags[] = 'FLD_T_CHAR';
				break;
			case 'text':
			case 'string':
				$flags[] = 'FLD_T_STRING';
				break;
			case 'code':
				$flags[] = 'FLD_T_CODE';
				break;
			case 'bool_char':
			case 'bchar':
				$flags[] = 'FLD_T_BCHAR';
				break;
			case 'real':
			case 'float':
				$flags[] = 'FLD_T_FLOAT';
				break;
			case 'ident':
				$flags[] = 'FLD_T_IDENT';
				break;
			case 'datetime':
				$flags[] = 'FLD_T_DATETIME';
				break;
			case 'bx_lang_id':
				$flags[] = 'FLD_T_BX_LANG_ID';
				break;
			case 'iblock_id':
				$flags[] = 'FLD_T_IBLOCK_ID';
				break;
			case 'iblock_prop_id':
			case 'ib_prop_id':
				$flags[] = 'FLD_T_IBLOCK_PROP_ID';
				break;
			case 'iblock_element_id':
			case 'ib_element_id':
				$flags[] = 'FLD_T_IBLOCK_ELEMENT_ID';
				break;
			case 'iblock_section_id':
			case 'ib_section_id':
				$flags[] = 'FLD_T_IBLOCK_SECTION_ID';
				break;
			case 'user_id':
				$flags[] = 'FLD_T_USER_ID';
				break;
			case 'group_id':
			case 'user_group_id':
				$flags[] = 'FLD_T_GROUP_ID';
				break;
		}
		if(true === $field['unsigned']) $flags[] = 'FLD_UNSIGNED';
		if(true === $field['no_check']) $flags[] = 'FLD_T_NO_CHECK';
		if(true === $field['deny_null']) $flags[] = 'FLD_NOT_NULL';
		if(true === $field['deny_zero']) $flags[] = 'FLD_NOT_ZERO';
		if(true === $field['required']) $flags[] = 'FLD_REQUIRED';
		if(!empty($field['default'])) $flags[] = 'FLD_DEFAULT';
		if(!empty($field['validator'])) $flags[] = 'FLD_T_CUSTOM_CK';
		if(true === $field['break_invalid']) $flags[] = 'FLD_BRK_INCORR';
		return $flags;
	}

	public function generateEntityClass() {

	}

	public function saveEntityClass() {

	}
} 