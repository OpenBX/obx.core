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

IncludeModuleLangFile(__FILE__);


class ActiveRecord {
	protected $bNewRecord = true;
	/** @var DBSimple */
	protected $entity = null;
	protected $entityFields = array();
	protected $messPool = null;

	protected $primaryKey = null;
	protected $primaryKeyAutoIncrement = true;

	protected $fieldsValues = null;
	protected $currentSelect = null;

	// DBSResult integration
	protected $result = null;

	/**
	 * @param DBSimple $entity
	 * @param int|string|null|DBSResult $id
	 * @param null $select
	 * @throws \ErrorException
	 */
	public function __construct(DBSimple $entity, $id = null, $select = null) {
		if( !($entity instanceof DBSimple) ) {
			throw new \ErrorException('DBSimple entity not set.');
		}
		$this->entity = $entity;
		$this->primaryKey = $this->entity->getMainTablePrimaryKey();
		$this->primaryKeyAutoIncrement = $this->entity->getMainTableAutoIncrement();
		$this->entityFields = array_keys($this->entity->getTableFieldsCheck());
		$this->messPool = $this->entity->getMessagePool();
		if(null !== $id) {
			if($id instanceof DBSResult) {
				if($this->entity === $id->getDBSimpleEntity()) {
					throw new \ErrorException('ActiveRecord: can\'t read DBSResult. Wrong DBSimple entity');
				}
				$this->_read($id);
			}
			elseif(null !== $this->primaryKey) {
				$this->read($id, $select);
			}
		}
	}



	public function getEntity() {
		return $this->entity;
	}

	public function save() {
		if( true === $this->bNewRecord ) {
			return $this->entity->add($this->fieldsValues);
		}
		return $this->entity->update($this->fieldsValues);
	}

	public function read($id, $select = null) {
		if(null === $select || !is_array($select)) {
			$select = $this->entityFields;
		}
		if(null !== $id) {
			$this->fieldsValues = $this->_fetchResult($this->entity->getByID($id, $select, true));
		}
	}

	protected function _fetchResult(DBSResult $result) {
		$this->result = $result;
		return $this->result->Fetch();
	}

	protected function _getNextResult(DBSResult $result) {

	}

	/**
	 * @param string $field
	 * @param $value
	 * @param bool $bAutoSave
	 */
	public function setField($field, $value, $bAutoSave = false) {

	}

	public function getField($field) {

	}

	public function getFields($fieldName) {
		return $this->fieldsValues;
	}
}