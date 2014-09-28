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

class DBResult extends \CDBResult {
	protected $_obxDBSimpleEntity = null;
	function __construct(Entity $entity, \CDBResult $DBResult = null) {
		if( $entity instanceof Entity ) {
			$this->_obxDBSimpleEntity = $entity;
		}
		else {
			throw new \ErrorException(__CLASS__.': entity object not set');
		}
		parent::CDBResult($DBResult);
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
