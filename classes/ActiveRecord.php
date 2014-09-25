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


class ActiveRecord {
	protected $bNewRecord = true;
	/** @var DBSimple */
	protected $entity = null;
	protected $fields = array();
	protected $messPool = null;

	public function __construct(DBSimple $entity) {
		if( !($entity instanceof DBSimple) ) {
			throw new \Exception('DBSimple entity not set.');
		}
		$this->entity = $entity;
		$this->messPool = $this->entity->getMessagePool();
	}

	public function getEntity() {
		return $this->entity;
	}

	public function save() {
		if( true === $this->bNewRecord ) {
			return $this->entity->add($this->fields);
		}
		return $this->entity->update($this->fields);
	}

	public function read($ID) {
		if(null !== $ID) {
			$this->fields = $this->entity->getByID($ID);
		}
	}


	/**
	 * @param array $fields
	 * @param bool $bAutoSave
	 */
	public function setField($fields, $bAutoSave = false) {

	}

	public function getField() {

	}
}