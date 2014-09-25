<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core;

//IncludeModuleLangFile(__FILE__);


class DBSResult extends \CDBResult {
	protected $_obxAbstractionName = null;
	function __construct($DBResult = null) {
		parent::__construct($DBResult);
	}
	public function setAbstractionName($className) {
		if( class_exists($className) ) {
			$this->_obxAbstractionName = $className;
		}
	}
	public function getAbstractionName() {
		return $this->_obxAbstractionName;
	}

	/**
	 * @return ActiveRecord
	 */
	public function fetchRecord() {

	}
}