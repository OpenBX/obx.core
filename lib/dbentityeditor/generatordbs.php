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
class GeneratorDBS extends Generator {
	// DBSimple Vars
	//protected $_entityModuleID = null;
	//protected $_entityEventsID = null;
	//protected $_mainTable = null;
	//protected $_mainTablePrimaryKey = null;
	//protected $_mainTableAutoIncrement = null;
	//protected $_arTableList = null;
	//protected $_arTableLinks = null;
	//protected $_arTableLeftJoin = null;
	//protected $_arTableFields = null;
	//protected $_arSelectDefault = null;
	//protected $_arTableUnique = null;
	//protected $_arSortDefault = null;
	//protected $_arTableFieldsDefault = null;
	//protected $_arTableFieldsCheck = null;




	public function __init() {

		$this->namespace = $this->config->getNamespace();
		$this->className = $this->config->getClass();
		$this->uses = array(
			'OBX\Core\DBSimple\Entity'
		);
		$this->extends = 'Entity';
		$this->addInitialVariable('protected', '_entityModuleID', $this->config->getModuleID());
		$this->addInitialVariable('protected', '_entityEventsID', $this->config->getEventsID());
		$this->addInitialVariable('protected', '_mainTable', $this->config->getAlias());
		$arOwnFields = $this->config->getFieldsList(true);
		$bPrimaryFound = false;
		$bAutoIncrementFound = false;
		foreach($arOwnFields as $fieldName) {
			$field = $this->config->getField($fieldName);
			if(false === $bPrimaryFound && true == $field['primary_key']) {
				$this->addInitialVariable('protected', '_mainTablePrimaryKey', $fieldName);
			}
			if(false === $bAutoIncrementFound && true == $field['auto_increment']) {
				$this->addInitialVariable('protected', '_mainTableAutoIncrement', $fieldName);
			}
		}
		$this->initReferences();

		$this->addMethod('public', '__construct', array(),
			$this->init_arFieldsCheck()
		);
		$debug=1;
	}

	private function init_arFieldsCheck() {
		$code_arFieldsCheck = "\t\t".'$this->_arTableFieldsCheck('."\n";
		$arFieldsList = $this->config->getFieldsList(true);
		foreach($arFieldsList as $fieldAlias) {
			$field = $this->config->getField($fieldAlias);
			$arCheckFlags = $this->cfgField2DBSimpleFieldCheck($field);
			$code_arFieldsCheck .= "\t\t\t'".$field['code'].'\' => self::'.implode(' | self::', $arCheckFlags).",\n";
		}
		$code_arFieldsCheck .= "\t\t);\n";
		return $code_arFieldsCheck;
	}

	private function initReferences() {
		$referenceList = $this->config->getReferences();
		$value_arTableLinks = array();
		$value_arTableLeftJoin = array();
		$value_arTableRightJoin = array();
		foreach($referenceList as $reference) {
			$value_arTableLinks[] = array(
				array($reference['alias'] => $reference['reference_field']),
				array($this->config->getAlias() => $reference['self_field'])
			);
			switch($reference['type']) {
				case 'left_join':
					$value_arTableLeftJoin[$reference['alias']] = $reference['condition'];
					break;
				case 'right_join':
					$value_arTableRightJoin[$reference['alias']] = $reference['condition'];
					break;
			}
		}
		if(!empty($value_arTableLinks)) {
			$this->addInitialVariable('protected', '_arTableLinks', $value_arTableLinks);
		}
		if(!empty($value_arTableLeftJoin)) {
			$this->addInitialVariable('protected', '_arTableLeftJoin', $value_arTableLeftJoin);
		}
		if(!empty($value_arTableRightJoin)) {
			$this->addInitialVariable('protected', '_arTableRightJoin', $value_arTableRightJoin);
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

	public function saveEntityClass($path) {

	}
} 