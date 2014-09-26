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

use OBX\Core\Exceptions\DBSimple\RecordError;

IncludeModuleLangFile(__FILE__);


class ActiveRecord {
	protected $bNewRecord = true;
	/** @var Entity */
	protected $entity = null;
	protected $entityFields = array();
	protected $messagePool = null;

	protected $primaryKey = null;
	protected $primaryKeyAutoIncrement = true;

	protected $fieldsValues = null;
	protected $currentSelect = null;

	// DBSResult integration
	protected $result = null;

	/**
	 * @param Entity $entity
	 * @param int|string|null|Entity $id
	 * @param null $select
	 * @throws \ErrorException
	 */
	public function __construct(Entity $entity, $id = null, $select = null) {
		if( !($entity instanceof Entity) ) {
			throw new RecordError('', RecordError::E_RECORD_ENTITY_NOT_SET);
		}
		$this->entity = $entity;
		$this->primaryKey = $this->entity->getMainTablePrimaryKey();
		$this->primaryKeyAutoIncrement = $this->entity->getMainTableAutoIncrement();
		$this->entityFields = array_keys($this->entity->getTableFieldsCheck());
		$this->messagePool = $this->entity->getMessagePool();
		if(null !== $id) {
			if($id instanceof DBResult) {
				$this->readFromDBResult($id);
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
			$success = (true == $this->entity->add($this->fieldsValues));
		}
		else {
			$success = (true == $this->entity->update($this->fieldsValues));
		}

		if(false === $success) {
			throw new RecordError(
				array('#ERROR#' => $this->entity->getLastError()),
				RecordError::E_SAVE_FAILED
			);
		}
	}

	public function read($id, $select = null) {
		if(null === $select || !is_array($select)) {
			$select = $this->entityFields;
		}
		if(null !== $id) {
			$this->readFromDBResult($this->entity->getByID($id, $select, true));
		}
	}

	public function readFromDBResult(DBResult $result) {
		if( !($result instanceof DBResult) ) {
			$e = new RecordError('', RecordError::E_CANT_READ_FROM_DB_RESULT);
			$this->messagePool->addErrorException($e);
			throw $e;
		}
		if($this->entity !== $result->getDBSimpleEntity()) {
			$e = new RecordError('', RecordError::E_WRONG_DB_RESULT_ENTITY);
			$this->messagePool->addErrorException($e);
			throw $e;
		}
		if( !($arResult = $result->Fetch()) ) {
			
		}
	}

	public function __set($field, $value) {
		if($this->primaryKey == $field) {
			$e = new RecordError('', RecordError::E_CANT_SET_PRIMARY_KEY_VALUE);
			$this->messagePool->addErrorException($e);
			throw $e;
		}
	}

	public function __get($field) {

	}

	public function __isset($field) {
		return array_key_exists($field, $this->fieldsValues)?true:false;
	}

	/**
	 * @param array $fieldsValues
	 * @param bool $bAutoSave
	 */
	public function setFields($fieldsValues, $bAutoSave = false) {
		foreach($fieldsValues as $field => &$value) {
			if(array_key_exists($field, $this->entityFields) && $this->primaryKey != $field) {
				$this->fieldsValues[$field] = $value;
			}
		}
		if(true === $bAutoSave) {
			$this->save();
		}
	}

	public function getFields() {
		return $this->fieldsValues;
	}
}