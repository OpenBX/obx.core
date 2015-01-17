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
			throw new EntityGeneratorError;
		}
		$jsonConfig = file_get_contents(OBX_DOC_ROOT.$entityConfigFile);
		$configData = json_decode($jsonConfig, true);
		$this->readEntityConfig($configData);
	}

	protected function readEntityConfig(&$configData) {

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