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

use OBX\Core\Exceptions\DBSimple\RecordError;

class DBResult extends \CDBResult {
	protected $_obxDBSimpleEntity = null;
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
	 * @return Record
	 */
	public function fetchRecord() {
		return new Record($this->_obxDBSimpleEntity, $this);
	}
}
