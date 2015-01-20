<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\DBSimple;

use OBX\Core\Exceptions\DBSimple\EntityGeneratorError as Err;
use OBX\Core\Tools;

class EntityGenerator
{
	protected $_entityModuleID = null;
	protected $_entityEventsID = null;
	protected $_configPath = null;
	protected $_classPath = null;
	protected $_namespace = null;
	protected $_className = null;

	protected $_createTable = array();

	// DBSimple Vars
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


	/**
	 * @param $entityConfigFile
	 * @throws \OBX\Core\Exceptions\DBSimple\EntityGeneratorError
	 */
	public function __construct($entityConfigFile) {
		/** @global \CDatabase $DB */
		global $DB;
		if( !is_file(OBX_DOC_ROOT.$entityConfigFile) ) {
			throw new Err('', Err::E_OPEN_CFG_FAILED);
		}
		$jsonConfig = file_get_contents(OBX_DOC_ROOT.$entityConfigFile);
		$configData = json_decode($jsonConfig, true);
		if(null === $configData) {
			throw new Err(
				array('JSON_ERROR' => Tools::getJsonErrorMsg()),
				Err::E_PARSE_CFG_FAILED
			);
		}
		if( empty($configData['module'])
			&& !is_dir(OBX_DOC_ROOT.$configData['module'])
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/include.php')
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/install/index.php')
		) {
			throw new Err('', Err::E_CFG_NO_MOD);
		}
		$this->_entityModuleID = $configData['module'];
		if( empty($configData['events_id']) ) {
			throw new Err('', Err::E_CFG_NO_EVT_ID);
		}
		$this->_entityEventsID = $configData['events_id'];
		if( empty($configData['namespace']) ) {
			throw new Err('', Err::E_CFG_NO_NS);
		}
		$this->_namespace = $configData['namespace'];
		if( empty($configData['class']) ) {
			throw new Err('', Err::E_CFG_NO_CLASS_NAME);
		}
		$this->_className = $configData['class'];
		if( empty($configData['class_path']) ) {
			throw new Err('', Err::E_CFG_NO_CLASS_PATH);
		}
		$this->_classPath = $configData['class_path'];
		$this->_mainTable = $configData['table_alias'];
		$configData['table_alias'] = trim($configData['table_alias']);
		$configData['table_name'] = trim($configData['table_name']);
		if(empty($configData['table_alias'])
			|| !preg_match('~[a-zA-Z][a-zA-Z0-9\_]{0,254}~', $configData['table_alias'])
		) {
			throw new Err('', Err::E_CFG_TBL_WRG_ALIAS);
		}
		if(empty($configData['table_name'])
			|| !preg_match('~[a-zA-Z][a-zA-Z0-9\_]{1,62}~', $configData['table_name'])
		) {
			throw new Err('', Err::E_CFG_TBL_WRG_NAME);
		}
		$this->_mainTable = $configData['table_alias'];
		$this->_arTableList = array(
			$configData['table_alias'] => $configData['table_name']
		);

		if(empty($configData['fields']) || !is_array($configData['fields'])) {
			throw new Err('', Err::E_CFG_FLD_LIST_IS_EMPTY);
		}
		foreach($configData['fields'] as &$field) {
			if(empty($field['code'])
				|| !preg_match('~[a-zA-Z][a-zA-Z0-9\_]{1,62}~', $field['code'])
			) {
				throw new Err('', Err::E_CFG_TBL_WRG_NAME);
			}
			$field['type'] = ''.trim($field['type']);
			$fieldCheckType = $this->cfgFieldType2DBSimpleFieldCheck($field);
			if(null !== $fieldCheckType) {
				$this->_arTableFieldsCheck[$field['code']] = array($fieldCheckType);
				$this->_createTable[$field['code']] = array(
					'data_type' => $this->cfgFieldType2MySQL($field),
					'unsigned' => '',
					'allow_null' => ' not null',
					'auto_increment' => '',
					'default' => ''
				);
				if(isset($field['allow_null']) && true === $field['allow_null']) {
					$this->_createTable[$field['code']]['allow_null'] = ' null';
				}
				if(
					('FLD_T_INT' === $fieldCheckType || 'int'=== $field['type'] || 'integer'===$field['type'])
					&&
					(isset($field['auto_increment']) && true === $field['auto_increment'])
				) {
					$this->_createTable[$field['code']]['auto_increment'] = ' auto_increment';
				}
				if(!empty($field['default'])) {
					$this->_arTableFieldsDefault[$field['code']] = $DB->ForSql($field['default']);
					$this->_createTable[$field['code']]['default'] = $this->_arTableFieldsDefault[$field['code']];
				}
			}
		}
		$debug=1;
	}

	protected function cfgFieldType2DBSimpleFieldCheck(&$field) {
		if(isset($field['no_check'])) {
			return 'no_check';
		}
		switch($field['type']) {
			case '':
			case 'ex':
				return null;
			case 'no_check':
				return 'FLD_T_NO_CHECK';
			case 'pk_id':
				return 'FLD_T_PK_ID';
			case 'int':
			case 'integer':
				return 'FLD_T_INT';
			case 'char':
				return 'FLD_T_CHAR';
			case 'text':
			case 'string':
				return 'FLD_T_STRING';
			case 'code':
				return 'FLD_T_CODE';
			case 'bool_char':
			case 'bchar':
				return 'FLD_T_BCHAR';
			case 'real':
			case 'float':
				return 'FLD_T_FLOAT';
			case 'ident':
				return 'FLD_T_IDENT';
			case 'datetime':
				return 'FLD_T_DATETIME';

			case 'bx_lang_id':
				return 'FLD_T_BX_LANG_ID';
			case 'iblock_id':
				return 'FLD_T_IBLOCK_ID';
			case 'iblock_prop_id':
			case 'ib_prop_id':
				return 'FLD_T_IBLOCK_PROP_ID';
			case 'iblock_element_id':
			case 'ib_element_id':
				return 'FLD_T_IBLOCK_ELEMENT_ID';
			case 'iblock_section_id':
			case 'ib_section_id':
				return 'FLD_T_IBLOCK_SECTION_ID';
			case 'user_id':
				return 'FLD_T_USER_ID';
			case 'group_id':
			case 'user_group_id':
				return 'FLD_T_GROUP_ID';
			default:
				throw new Err('', Err::E_CFG_FLD_WRG_TYPE);
		}
	}

	protected function cfgFieldType2MySQL(&$field) {
		$type = null;
		switch($field['type']) {
			case '':
			case 'ex':
				break;
			case 'int':
			case 'integer':
			case 'pk_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'char':
				break;
			case 'text':
				$type = 'text';
				break;
			case 'string':
				if(isset($field['length'])) {
					$field['length'] = intval($field['length']);
					if($field['length'] > 255 || $field['length'] < 1) {
						$field['length'] = 255;
					}
				} else $field['length'] = 255;
				$type = 'varchar('.$field['length'].')';
				break;
			case 'code':
				$type = 'varchar(15)';
				break;
			case 'bool_char':
			case 'bchar':
				$type = 'char(1)';
				break;
			case 'real':
			case 'float':
				//http://dev.mysql.com/doc/refman/5.0/en/precision-math-decimal-characteristics.html
				if(isset($field['length'])) {
					list($ldec, $rdec) = explode(',', $field['length']);
					$ldec = intval($ldec);
					$rdec = intval($rdec);
					if($ldec > 64 || $field['length'] < 1) $ldec = 18;
					if($rdec > $ldec ) $rdec = $ldec;
					if($rdec < 1) $ldec = 2;
					$field['length'] = $ldec.','.$rdec;
				} else $field['length'] = '18,2';
				$type = 'decimal('.$field['length'].')';
				break;
			case 'ident':
				$type = 'varchar(255)';
				break;
			case 'datetime':
				$type = 'datetime';
				break;
			case 'bx_lang_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_prop_id':
			case 'ib_prop_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_element_id':
			case 'ib_element_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_section_id':
			case 'ib_section_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'user_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'group_id':
			case 'user_group_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			default:
				throw new Err('', Err::E_CFG_FLD_WRG_TYPE);
		}
		return $type;
	}

	protected function getIntFieldType4MySQL(&$field) {
		if(isset($field['length'])) {
			$field['length'] = intval($field['length']);
			if($field['length'] > 11 || $field['length'] < 1) {
				$field['length'] = 11;
			}
		} else $field['length'] = 11;
		$unsigned = '';
		if(isset($field['auto_increment']) && true === $field['auto_increment']
			&& isset($field['primary_key']) && true === $field['primary_key']
		) {
			$unsigned = ' unsigned';
		}
		return 'int('.$field['length'].')'.$unsigned;
	}

	public function getCreateTablesCode() {

	}

	public function generateEntityClass() {

	}

	public function saveEntityClass() {

	}

	public function saveConfig() {

	}

	public function addTable() {

	}
} 