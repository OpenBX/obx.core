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
	protected $_namespace = null;
	protected $_class = null;
	protected $_classPath = null;
	protected $_version = null;
	protected $_langPrefix = null;
	protected $_title = null;
	protected $_tableName = null;
	protected $_tableAlias = null;
	protected $_fields = array();

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
		$this->_configPath = $entityConfigFile;
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
		/** @noinspection PhpUndefinedMethodInspection */
		//$this->_version = \CUpdateClient::GetModuleVersion($this->_entityModuleID);
		if(!empty($configData['version']) && strpos($configData['version'], '.') !== false) {
			$arVersion = explode('.', $configData['version']);
			if(count($arVersion) >= 3
				&& is_numeric($arVersion[0])
				&& is_numeric($arVersion[1])
				&& is_numeric($arVersion[2])
			) {
				$this->_version = $arVersion[0].'.'.$arVersion[1].'.'.$arVersion[2];
			}
		}

		$configData['namespace'] = ''.trim($configData['namespace'], ' \\');
		//$configData['namespace'] = str_replace('\\\\', '\\', $configData['namespace']);
		if( strlen($configData['namespace']) > 254
			//SomeVeryIncredibleNameOfNamespaceOrFuckingClass - 48 symbols
			|| !preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50}(?:\\\\?))+~', $configData['namespace'], $matches)
		) {
			throw new Err('', Err::E_CFG_WRG_NAMESPACE);
		}
		$this->_namespace = $configData['namespace'];

		$configData['class'] = ''.trim($configData['class'], ' \\');
		if( !preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50})+~', $configData['class']) ) {
			throw new Err('', Err::E_CFG_WRG_CLASS_NAME);
		}
		$this->_class = $configData['class'];

		$this->_classPath = 'lib/'
			.strtolower(str_replace('\\', '/', $this->_namespace))
			.'/'.$this->_class.'.php'
		;
		if( empty($configData['class_path']) ) {
			throw new Err('', Err::E_CFG_NO_CLASS_PATH);
		}

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
		$this->_tableName = $configData['table_name'];
		$this->_tableAlias = $configData['table_alias'];
		$this->_langPrefix = str_replace('\\', '_', strtoupper($this->_namespace.'\\'.$this->_class));
		if(!empty($configData['lang_prefix'])) {
			$configData['lang_prefix'] = strtoupper(trim($configData['lang_prefix']));
			if(preg_match('~[A-Z0-9\\_\\-/\\|]~', $configData['lang_prefix'])) {
				$this->_langPrefix = $configData['lang_prefix'];
			}
		}
		$this->_title = array(
			'lang' => '%_ENTITY_TITLE',
			'ru' => $this->_langPrefix.'_ENTITY_TITLE',
			'en' => $this->_langPrefix.'_ENTITY_TITLE'
		);

		if(!empty($configData['title']) && is_array($configData['title'])) {
			if(!empty($configData['title']['lang'])) $this->_title = $configData['title']['lang'];
			if(!empty($configData['title']['ru'])) $this->_title = $configData['title']['lang'];
			if(!empty($configData['title']['en'])) $this->_title = $configData['title']['lang'];
		}


		// Обработка данных полей
		if(empty($configData['fields']) || !is_array($configData['fields'])) {
			throw new Err('', Err::E_CFG_FLD_LIST_IS_EMPTY);
		}
		foreach($configData['fields'] as $fieldCode => &$rawField) {
			$fieldCode = trim($fieldCode);
			if( !preg_match('~[a-zA-Z][a-zA-Z0-9\_]{1,62}~', $fieldCode) ) {
				throw new Err('', Err::E_CFG_TBL_WRG_NAME);
			}
			$fieldType = ''.trim($rawField['type']);
			if( !$this->checkExistsType($fieldType) ) {
				throw new Err('', Err::E_CFG_FLD_WRG_TYPE);
			}
			$codeStrUpper = strtoupper($rawField['code']);
			// Задаем набор возможных опций поля
			$field = array(
				'code' => $fieldCode,
				'type' => $fieldType,
				'user_type' => null,
				'length' => null,
				'unsigned' => false,
				'auto_increment' => false,
				'primary_key' => false,
				'deny_null' => false,
				'deny_zero' => false,
				'no_check' => false,
				'required' => false,
				'required_error' => array(
					'lang' => '%_ERR_REQUIRED_'.$codeStrUpper,
					'ru' => 'REQUIRED__'.$codeStrUpper.'__FIELD',
					'en' => 'REQUIRED__'.$codeStrUpper.'__FIELD'
				),
				'default' => null,
				'validator' => null,
				'break_invalid' => false,
				'get' => array(
					'ref' => null,
					'if_null_return' => null,
					'sub_query' => null,
					'sub_query_4_filter' => null,
					'required_tables' => null,
					'required_group_by' => null
				),
				'selected_by_default' => true,
				'title' => array(
					'lang' => '%_FLD_TITLE_OF_'.$codeStrUpper,
					'ru' => 'TITLE_OF__'.$codeStrUpper.'__FIELD',
					'en' => 'TITLE_OF__'.$codeStrUpper.'__FIELD'
				),
				'description' => array(
					'lang' => '%_FLD_DSCR_OF_'.$codeStrUpper,
					'ru' => 'DESCRIPTION_OF__'.$codeStrUpper.'__FIELD',
					'en' => 'DESCRIPTION_OF__'.$codeStrUpper.'__FIELD'
				),
			);
			if('ex' !== $field['type']) {
				$field['selected_by_default'] = true;
			}
			// Заменяем дефолтные параметры поля, на те, что указаны в конфиге
			foreach($field as $fldAttrName => &$fldAttrValue) {
				if(is_bool($fldAttrValue)) {
					if(isset($rawField[$fldAttrName]) ) {
						if( $rawField[$fldAttrName] === !$fldAttrValue) {
							$fldAttrValue = !$fldAttrValue;
						}
					}
				}
				if(null === $fldAttrValue && isset($rawField[$fldAttrName])) {
					$fldAttrValue = ''.$rawField[$fldAttrName];
				}
				if(is_array($fldAttrValue)
					&& is_array($rawField[$fldAttrName])
					&& !empty($rawField[$fldAttrName])
				) {
					if(array_key_exists('lang', $fldAttrValue)) {
						if(!empty($rawField[$fldAttrName]['lang'])) $fldAttrValue['lang'] = $rawField[$fldAttrName]['lang'];
						if(!empty($rawField[$fldAttrName]['ru'])) $fldAttrValue['ru'] = $rawField[$fldAttrName]['ru'];
						if(!empty($rawField[$fldAttrName]['en'])) $fldAttrValue['en'] = $rawField[$fldAttrName]['en'];
					}
					else {
						foreach($fldAttrValue as $fldSubAttrName => &$fldSubAttrValue) {
							if(is_bool($fldSubAttrValue)) {
								if(isset($rawField[$fldAttrName][$fldSubAttrName]) ) {
									if( $rawField[$fldAttrName][$fldSubAttrName] === !$fldSubAttrValue) {
										$fldSubAttrValue = !$fldSubAttrValue;
									}
								}
							}
							if(null === $fldSubAttrValue && isset($rawField[$fldAttrName][$fldSubAttrName])) {
								$fldSubAttrValue = ''.$rawField[$fldAttrName][$fldSubAttrName];
							}
						} unset($fldSubAttrName, $fldSubAttrValue);
					}
				}
			} unset($fldAttrName, $fldAttrValue);
			if(!empty($field['default'])) {
				$field['default'] = $DB->ForSql($field['default']);
			}

			$this->_fields[$fieldCode] = $field;
		} unset($field, $rawField);

		// Получаем данные одополнительных таблицах

		// DBSimple Data
		$this->_classPath = $this->_class;
		$this->_mainTable = $this->_tableAlias;
		$this->_arTableList = array(
			$this->_tableAlias => $this->_tableName
		);
		foreach($this->_fields as $fieldCode => &$field) {
			$fieldCheckType = $this->cfgField2DBSimpleFieldCheck($field);
			if(null !== $fieldCheckType) {
				$this->_arTableFieldsCheck[$field['code']] = $fieldCheckType;
				$this->_createTable[$field['code']] = array(
					'data_type' => $this->cfgFieldType2MySQL($field),
					'deny_null' => ' not null',
					'default' => ''
				);
				if(true === $field['deny_null']) {
					$this->_createTable[$field['code']]['deny_null'] = ' null';
				}
				if(!empty($field['default'])) {
					$this->_arTableFieldsDefault[$field['code']] = $field['default'];
					$this->_createTable[$field['code']]['default'] = $this->_arTableFieldsDefault[$field['code']];
				}
				if(true === $field['selected_by_default']) {
					if(null === $this->_arSelectDefault) $this->_arSelectDefault = array();
					$this->_arSelectDefault[] = $field['code'];
				}
			}
		}
		$debug=1;
	}

	protected function checkExistsType(&$type) {
		if('' === $type) $type = 'ex';
		switch($type) {
			case 'ex':
			case 'no_check':
			case 'pk_id':
			case 'int':
			case 'integer':
			case 'char':
			case 'text':
			case 'string':
			case 'code':
			case 'bool_char':
			case 'bchar':
			case 'real':
			case 'float':
			case 'ident':
			case 'datetime':
			case 'bx_lang_id':
			case 'iblock_id':
			case 'iblock_prop_id':
			case 'ib_prop_id':
			case 'iblock_element_id':
			case 'ib_element_id':
			case 'iblock_section_id':
			case 'ib_section_id':
			case 'user_id':
			case 'group_id':
			case 'user_group_id':
				return true;
			default:
				return false;
		}
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
			default:
				throw new Err('', Err::E_CFG_FLD_WRG_TYPE);
		}
		if(true === $this->checkUnsignedField($field)) $flags[] = 'FLD_UNSIGNED';
		if(true === $field['no_check']) $flags[] = 'FLD_T_NO_CHECK';
		if(true === $field['deny_null']) $flags[] = 'FLD_NOT_NULL';
		if(true === $field['deny_zero']) $flags[] = 'FLD_NOT_ZERO';
		if(true === $field['required']) $flags[] = 'FLD_REQUIRED';
		if(!empty($field['default'])) $flags[] = 'FLD_DEFAULT';
		if(!empty($field['validator'])) $flags[] = 'FLD_T_CUSTOM_CK';
		if(true === $field['break_invalid']) $flags[] = 'FLD_BRK_INCORR';
		return $flags;
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
				$type = 'char(1)'.($this->checkUnsignedField($field)?' unsigned':'');
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
				$type = 'decimal('.$field['length'].')'.($this->checkUnsignedField($field)?' unsigned':'');
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
		$ai = '';
		if(true === $field['auto_increment']) {
			$ai = ' auto_increment';
		}
		$unsigned = ($this->checkUnsignedField($field)?' unsigned':'');
		return 'int('.$field['length'].')'.$unsigned.$ai;
	}

	protected function checkUnsignedField(&$field) {
		if(true === $field['unsigned'] || 'pk_id' === $field['type']) {
			switch($field['type']) {
				case 'pk_id':
				case 'int':
				case 'integer':
				case 'float':
				case 'real':
				case 'bool_char':
				case 'bchar':
					return true; break;
				default: break;
			}
		}
		return false;
	}

	public function getCreateTableCode() {

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