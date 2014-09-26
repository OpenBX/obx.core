<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\DBSimple;

//IncludeModuleLangFile(__FILE__);


class Result extends \CDBResult {
	protected $_obxDBSimpleEntity = null;
	protected $_obxActiveRecord = null;
	function __construct($DBResult = null) {
		parent::__construct($DBResult);
	}

	public function setDBSimpleEntity(Entity $entity) {
		if( $entity instanceof Entity ) {
			$this->_obxDBSimpleEntity = $entity;
			return true;
		}
		return false;
	}

	public function getDBSimpleEntity() {
		return $this->_obxDBSimpleEntity;
	}

	/**
	 * @return Result
	 */
	public function fetchRecord() {
		if(null === $this->_obxActiveRecord) {
			//$this->_obxActiveRecord
		}
	}
}