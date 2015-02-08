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
use OBX\Core\PhpGenerator\PhpClass;

/**
 * Class GeneratorDBS
 * @package OBX\Core\DBEntityEditor
 * Генератор сущности DBSimple
 */
class GeneratorDBS extends Generator {
	// DBSimple Vars
	private $_entityModuleID = null;
	private $_entityEventsID = null;
	private $_mainTable = null;
	private $_mainTablePrimaryKey = null;
	private $_mainTableAutoIncrement = null;
	private $_arTableList = null;
	private $_arTableLinks = null;
	private $_arTableLeftJoin = null;
	private $_arTableRightJoin = null;
	private $_arTableFields = null;
	private $_arSelectDefault = null;
	private $_arTableUnique = null;
	private $_arSortDefault = null;
	private $_arGroupByFields = null;
	private $_arTableFieldsDefault = null;
	private $_arTableFieldsCheck = null;
	private $_arDBSimpleLangMessages = null;
	private $_arFieldsDescription = null;
	private $_arTableJoinNullFieldDefaults = null;


	protected function __init() {
		$this->phpClass->setUses(array(
			'OBX\Core\DBSimple\Entity'
		));
		$this->phpClass->setBaseClass('Entity');
		$this->_entityModuleID = $this->config->getModuleID();
		$this->_entityEventsID = $this->config->getEventsID();
		$this->_mainTable = $this->config->getAlias();
		$arOwnFields = $this->config->getFieldsList(true);
		$bPrimaryFound = false;
		$bAutoIncrementFound = false;
		foreach($arOwnFields as $fieldName) {
			$field = $this->config->getField($fieldName);
			if(false === $bPrimaryFound && true == $field['primary_key']) {
				$this->_mainTablePrimaryKey = $fieldName;
			}
			if(false === $bAutoIncrementFound && true == $field['auto_increment']) {
				$this->_mainTableAutoIncrement = $fieldName;
			}
		}
		$this->_arTableList = array(
			$this->_mainTable => $this->config->getTableName()
		);
		$this->initReferences();
		$this->init_arSelectDefault();
		$this->init_arTableUnique();
		$this->init_arSortDefault();
		$this->init_arGroupByFields();
		$this->init_arTableFieldsDefault();
		$this->init_arTableJoinNullFieldDefaults();
		$this->init_arTableFields();

		$this->phpClass->addVariableIfNotNull('protected', '_entityModuleID', $this->_entityModuleID);
		$this->phpClass->addVariableIfNotNull('protected', '_entityEventsID', $this->_entityEventsID);
		$this->phpClass->addVariableIfNotNull('protected', '_mainTable', $this->_mainTable);
		$this->phpClass->addVariableIfNotNull('protected', '_mainTablePrimaryKey', $this->_mainTablePrimaryKey);
		$this->phpClass->addVariableIfNotNull('protected', '_mainTableAutoIncrement', $this->_mainTableAutoIncrement);
		$this->phpClass->addVariableIfNotNull('protected', '_arTableList', $this->_arTableList);
		$this->phpClass->addVariableIfNotNull('protected', '_arTableLinks', $this->_arTableLinks);
		$this->phpClass->addVariableIfNotNull('protected', '_arTableLeftJoin', $this->_arTableLeftJoin);
		$this->phpClass->addVariableIfNotNull('protected', '_arTableRightJoin', $this->_arTableRightJoin);
		$this->phpClass->addVariableIfNotNull('protected', '_arSelectDefault', $this->_arSelectDefault);
		$this->phpClass->addVariableIfNotNull('protected', '_arTableUnique', $this->_arTableUnique);
		$this->phpClass->addVariableIfNotNull('protected', '_arSortDefault', $this->_arSortDefault);
		$this->phpClass->addVariableIfNotNull('protected', '_arGroupByFields', $this->_arGroupByFields);
		$this->phpClass->addVariableIfNotNull('protected', '_arTableFieldsDefault', $this->_arTableFieldsDefault);
		$this->phpClass->addVariableIfNotNull('protected', '_arTableJoinNullFieldDefaults', $this->_arTableJoinNullFieldDefaults);

		$this->phpClass->addVariableIfNotNull('protected', '_arFieldsCheck', array(), $this->_arFieldsCheck);

		$this->phpClass->addMethod('public', '__construct', array(),
			$this->getVariableDynamicInitCode('all')
		);
	}

	private function init_arTableFields() {
		$_arTableFields = array();
		$arFieldsList = $this->config->getFieldsList(false);
		foreach($arFieldsList as $fieldCode) {
			$field = $this->config->getField($fieldCode);
			if($field['type'] != 'ex') {
				$_arTableFields[$fieldCode] = array($this->config->getAlias() => $fieldCode);
			}
			else {
				if(!empty($field['get']['ref'])) {
					list($refTableAlias, $refFieldName) = explode('.', $field['get']['ref']);
					$_arTableFields[$fieldCode] = array($refTableAlias => $refFieldName);
				}
				elseif(!empty($field['get']['sub_query'])) {
					$_arTableFields[$fieldCode] = array(
						'SUB_QUERY' => $field['get']['sub_query'],
						'REQUIRED_TABLES' => $field['get']['required_tables']
					);
				}
			}
			if(!empty($field['get']['sub_query_4_filter'])) {
				$_arTableFields[$fieldCode]['SUB_QUERY_4_FILTER'] = $field['get']['sub_query_4_filter'];
			}
		}
		if(!empty($_arTableFields)) {
			$this->_arTableFields = $_arTableFields;
		}
	}

	private function init_arTableJoinNullFieldDefaults() {
		$arFieldsList = $this->config->getFieldsList(false);
		$_arTableJoinNullFieldDefaults = array();
		foreach($arFieldsList as $fieldCode) {
			$field = $this->config->getField($fieldCode);
			if(!empty($field['get']['if_null_return'])) {
				$_arTableJoinNullFieldDefaults[$fieldCode] = $field['get']['if_null_return'];
			}
		}
		if(!empty($_arTableJoinNullFieldDefaults)) {
			$this->_arTableJoinNullFieldDefaults = $_arTableJoinNullFieldDefaults;
		}
	}

	private function init_arTableFieldsDefault() {
		$arFieldsList = $this->config->getFieldsList(true);
		$_arTableFieldsDefault = array();
		foreach($arFieldsList as $fieldCode) {
			$field = $this->config->getField($fieldCode);
			if(!empty($field['default'])) {
				$_arTableFieldsDefault[$fieldCode] = $field['default'];
			}
		}
		if(!empty($_arTableFieldsDefault)) {
			$this->_arTableFieldsDefault = $_arTableFieldsDefault;
		}
	}

	private function init_arGroupByFields() {
		$_arGroupByFieldsDefault = array();
		$cfgGroupByFields = $this->config->getDefaultGroupBy();
		$arRefFields = $this->getConfRefFields();
		$selfFields = $this->config->getFieldsList(true);
		foreach($selfFields as $selfFieldName) {
			$arRefFields[$this->config->getAlias().'.'.$selfFieldName] = $selfFieldName;
		}
		foreach($cfgGroupByFields as $groupByFieldName) {
			if( strpos($groupByFieldName, '.') !== false ) {
				if(!empty($arRefFields[$groupByFieldName])) {
					$_arGroupByFieldsDefault[] = $arRefFields[$groupByFieldName];
				}
			}
			else {
				$_arGroupByFieldsDefault[] = $groupByFieldName;
			}
		}
		if(!empty($_arGroupByFieldsDefault)) {
			$this->_arGroupByFields = $_arGroupByFieldsDefault;
		}
	}

	private function init_arSortDefault() {
		// Если этот метод сгенерит имя полей с алиасом. DBSimple такого не пропустит
		// DBSimple проверяет есть ли такое поле по имени алиаса поля, а не по самому полю с указанием алиаса таблицы
		$_arSortDefault = array();
		$arSortFields = $this->config->getDefaultSort();
		$arRefFields = $this->getConfRefFields();
		$selfFields = $this->config->getFieldsList(true);
		foreach($selfFields as $selfFieldName) {
			$arRefFields[$this->config->getAlias().'.'.$selfFieldName] = $selfFieldName;
		}
		foreach($arSortFields as $sort) {
			if( strpos($sort['by'], '.') !== false ) {
				if(!empty($arRefFields[$sort['by']])) {
					$_arSortDefault[$arRefFields[$sort['by']]] = $sort['order'];
				}
			}
			else {
				$_arSortDefault[$sort['by']] = $sort['order'];
			}
		}
		if(!empty($_arSortDefault)) {
			$this->_arSortDefault = $_arSortDefault;
		}
	}

	private function getConfRefFields() {
		$arRefFields = array();
		$arFieldsList = $this->config->getFieldsList(false);
		foreach($arFieldsList as $fieldCode) {
			$field = $this->config->getField($fieldCode);
			if($field['type'] == 'ex' && !empty($field['get']['ref']) ) {
				$arRefFields[$field['get']['ref']] = $fieldCode;
			}
		}
		return $arRefFields;
	}

	private function init_arTableUnique() {
		$_arTableUnique = array();
		$uniqueList = $this->config->getUnique();
		foreach($uniqueList as $uqName => $unique) {
			$_arTableUnique[$uqName] = $unique['fields'];
		}
		if(!empty($_arTableUnique)) {
			$this->_arTableUnique = $_arTableUnique;
		}
	}

	private function init_arSelectDefault() {
		$_arSelectDefault = array();
		$arFieldsList = $this->config->getFieldsList(false);
		foreach($arFieldsList as $fieldCode) {
			$field = $this->config->getField($fieldCode);
			if(true === $field['selected_by_default']) {
				$_arSelectDefault[] = $fieldCode;
			}
		}
		if(!empty($_arSelectDefault)) {
			$this->_arSelectDefault = $_arSelectDefault;
		}
	}

	private function getCode_arFieldsCheck() {
		$code_arFieldsCheck = "\t\t".'$this->_arTableFieldsCheck = array('."\n";
		$arFieldsList = $this->config->getFieldsList(true);
		foreach($arFieldsList as $fieldAlias) {
			$field = $this->config->getField($fieldAlias);
			$arCheckFlags = $this->cfgField2DBSimpleFieldCheck($field);
			$code_arFieldsCheck .= "\t\t\t'".$field['code'].'\' => self::'.implode(' | self::', $arCheckFlags).",\n";
		}
		$code_arFieldsCheck .= "\t\t);\n";
		return $code_arFieldsCheck;
	}

	private function getCode_arDBSimpleLangMessages() {
		$_arDBSimpleLangMessages = array();
		$fieldsList = $this->config->getFieldsList(true);
		$iErrorCode = 0;
		foreach($fieldsList as $fieldCode) {
			$field = $this->config->getField($fieldCode);
			if(true === $field['required'] && !empty($field['required_error'])) {
				$_arDBSimpleLangMessages['REQ_FLD_'.$fieldCode] = array(
						'TYPE' => 'E',
						'TEXT' => $field['required_error'],
						'CODE' => ++$iErrorCode
				);
				$this->phpClass->setLangMessageArray($field['required_error']);
			}
		}
		$uniqueList = $this->config->getUnique();
		foreach($uniqueList as $uqCode => $unique) {
			$_arDBSimpleLangMessages['DUP_ADD_'.$uqCode] = array(
				'TYPE' => 'E',
				'TEXT' => $unique['duplicate_error_add'],
				'CODE' => ++$iErrorCode
			);
			$_arDBSimpleLangMessages['DUP_UPD_'.$uqCode] = array(
				'TYPE' => 'E',
				'TEXT' => $unique['duplicate_error_update'],
				'CODE' => ++$iErrorCode
			);
		}

		$langMessages = $this->config->getLangMessages();
		$_arDBSimpleLangMessages['NOTHING_TO_DELETE'] = array(
			'TYPE' => 'E',
			'TEXT' => $langMessages['error_nothing_to_delete'],
			'CODE' => ++$iErrorCode
		);
		$_arDBSimpleLangMessages['NOTHING_TO_UPDATE'] = array(
			'TYPE' => 'E',
			'TEXT' => $langMessages['error_nothing_to_update'],
			'CODE' => ++$iErrorCode
		);
		$code_arDBSimpleLangMessages = "\t\t".'$this->_arDBSimpleLangMessages = '
			.PhpClass::convertArray2PhpCode($_arDBSimpleLangMessages, "\t\t", $langRegister).";\n";
		foreach($langRegister as $msgID => $langArray) {
			foreach($langArray as $lang => $message) {
				$this->phpClass->setLangMessage($msgID, $lang, $message);
			}
		}
		return $code_arDBSimpleLangMessages;
	}

	private function getCode_arFieldsDescription() {
		$_arFieldsDescription = array();
		$arFieldsList = $this->config->getFieldsList(false);
		foreach($arFieldsList as $fieldCode) {
			$field = $this->config->getField($fieldCode);
			$_arFieldsDescription[$fieldCode] = array(
				'NAME' => $field['title'],
				'DESCRIPTION' => $field['description']
			);
			$this->phpClass->setLangMessageArray($field['title']);
			$this->phpClass->setLangMessageArray($field['description']);
		}
		return "\t\t".'$this->_arFieldsDescription = '.PhpClass::convertArray2PhpCode($_arFieldsDescription, "\t\t").';';
	}

	private function initReferences() {
		$referenceList = $this->config->getReferences();
		$_arTableLinks = array();
		$_arTableLeftJoin = array();
		$_arTableRightJoin = array();
		foreach($referenceList as $reference) {
			$this->_arTableList[$reference['alias']] = $reference['table'];
			$_arTableLinks[] = array(
				array($reference['alias'] => $reference['reference_field']),
				array($this->config->getAlias() => $reference['self_field'])
			);
			switch($reference['type']) {
				case 'left_join':
					$_arTableLeftJoin[$reference['alias']] = $reference['condition'];
					break;
				case 'right_join':
					$_arTableRightJoin[$reference['alias']] = $reference['condition'];
					break;
			}
		}
		if(!empty($_arTableLinks)) {
			$this->_arTableLinks = $_arTableLinks;
		}
		if(!empty($_arTableLeftJoin)) {
			$this->_arTableLeftJoin = $_arTableLeftJoin;
		}
		if(!empty($_arTableRightJoin)) {
			$this->_arTableRightJoin = $_arTableRightJoin;
		}

	}

	private function cfgField2DBSimpleFieldCheck(&$field) {
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
		if(!empty($field['validator'])) $flags[] = 'FLD_CUSTOM_CK';
		if(true === $field['break_invalid']) $flags[] = 'FLD_BRK_INCORR';
		return $flags;
	}
} 