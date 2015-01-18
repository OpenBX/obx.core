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


use OBX\Core\Exceptions\DBSimple\EntityGeneratorError;

class EntityGenerator
{

	protected $_entityModuleID = null;
	protected $_entityEventsID = null;
	protected $_configPath = null;
	protected $_classPath = null;
	protected $_namespace = null;
	protected $_className = null;
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


	public function __construct($entityConfigFile) {
		if( !is_file(OBX_DOC_ROOT.$entityConfigFile) ) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_OPEN_CFG_FAILED);
		}
		$jsonConfig = file_get_contents(OBX_DOC_ROOT.$entityConfigFile);
		$configData = json_decode($jsonConfig, true);
		$this->readEntityConfig($configData);
	}

	protected function readEntityConfig(&$configData) {
		if( empty($configData['module'])
			&& !is_dir(OBX_DOC_ROOT.$configData['module'])
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/include.php')
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/install/index.php')
		) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_NO_MOD);
		}
		$this->_entityModuleID = $configData['module'];
		if( empty($configData['events_id']) ) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_NO_EVT_ID);
		}
		$this->_entityEventsID = $configData['events_id'];
		if( empty($configData['namespace']) ) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_NO_NS);
		}
		$this->_namespace = $configData['namespace'];
		if( empty($configData['class_name']) ) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_NO_CLASS_NAME);
		}
		$this->_className = $configData['class_name'];
		if( empty($configData['class_path']) ) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_NO_CLASS_PATH);
		}
		$this->_classPath = $configData['class_path'];

		if(empty($configData['table']) || !is_array($configData['table'])) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_TBL_LIST_EMPTY);
		}
		$bMainTableSet = false;
		if(empty($configData['main_table'])) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_MAIN_TBL_NOT_SET);
		}
		foreach($configData['table'] as &$table) {
			//тут заполняем
			if($table['alias'] === $configData['main_table']) {
				$bMainTableSet = true;
			}
		}
		if(true !== $bMainTableSet) {
			throw new EntityGeneratorError('', EntityGeneratorError::E_CFG_MAIN_TBL_NOT_SET)
		}
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