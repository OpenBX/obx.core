<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

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
	 * @param bool $lazyLoad
	 * @return Record
	 */
	public function fetchRecord($lazyLoad = Record::DEF_LAZY_LOAD) {
		$record = new Record($this->_obxDBSimpleEntity, null, null, $lazyLoad);
		if(true !== $record->readFromDBResult($this)) {
			$record = null;
		}
		return $record;
	}
}
