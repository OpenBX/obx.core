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
use OBX\Core\MessagePoolDecorator;

IncludeModuleLangFile(__FILE__);


class Record extends MessagePoolDecorator {
	protected $bNewRecord = true;
	/** @var Entity */
	protected $entity = null;
	protected $entityFields = array();

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
	 * @throws RecordError
	 */
	public function __construct(Entity $entity, $id = null, $select = null) {
		if( !($entity instanceof Entity) ) {
			throw new RecordError('', RecordError::E_RECORD_ENTITY_NOT_SET);
		}
		$this->entity = $entity;
		$this->primaryKey = $this->entity->getMainTablePrimaryKey();
		$this->primaryKeyAutoIncrement = $this->entity->getMainTableAutoIncrement();
		$this->entityFields = array_keys($this->entity->getTableFieldsCheck());
		$this->MessagePool = $this->entity->getMessagePool();
		if(null !== $id) {
			$this->read($id, $select);
		}
	}

	public function getEntity() {
		return $this->entity;
	}

	public function save() {
		if( true === $this->bNewRecord ) {
			$this->bNewRecord = false;
			return $this->entity->add($this->fieldsValues);
		}
		else {
			$this->bNewRecord = false;
			return $this->entity->update($this->fieldsValues);
		}
	}

	public function read($id, $select = null) {
		if(null === $select || !is_array($select)) {
			$select = $this->entityFields;
		}
		return $this->readFromDBResult($this->entity->getByID($id, $select, true));
	}

	public function readFromDBResult(DBResult $result) {
		if( !($result instanceof DBResult) ) {
			$e = new RecordError('', RecordError::E_CANT_READ_FROM_DB_RESULT);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}
		if($this->entity !== $result->getDBSimpleEntity()) {
			$e = new RecordError('', RecordError::E_WRONG_DB_RESULT_ENTITY);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}
		if( $arResult = $result->Fetch() ) {
			foreach($arResult as $field => &$value) {
				if(in_array($field, $this->entityFields)) {
					$this->fieldsValues[$field] = $value;
				}
			}
		}
		else {
			$this->MessagePool->addErrorException(new RecordError('', RecordError::E_CANT_FIND_RECORD));
			return false;
		}
		$this->bNewRecord = false;
		return true;
	}

	public function __set($field, $value) {
		if($this->primaryKey == $field) {
			$e = new RecordError('', RecordError::E_CANT_SET_PRIMARY_KEY_VALUE);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}

		if( true === $this->bNewRecord ) {
			if(!in_array($field, $this->entityFields)) {
				$e = new RecordError(array('#FIELD#' => $field), RecordError::E_SET_WRONG_FIELD);
				$this->MessagePool->addErrorException($e);
				throw $e;
			}
		}
		elseif(!array_key_exists($field, $this->fieldsValues)) {
			$e = new RecordError(array('#FIELD#' => $field), RecordError::E_SET_WRONG_FIELD);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}

		$this->fieldsValues[$field] = $value;
	}

	public function __get($field) {
		if(!array_key_exists($field, $this->fieldsValues)) {
			$e = new RecordError(array('#FIELD#' => $field), RecordError::E_GET_WRONG_FIELD);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}
		return $this->fieldsValues[$field];
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


	/**
	 * @param array $fields
	 * @param null $indexName
	 * @return bool
	 * @throws \OBX\Core\Exceptions\DBSimple\RecordError
	 * Прочитать запись по значениям полей уникального индекса
	 * Имя индекса можно указать явно,
	 * что бы не производить сопоставление о полям существующих в сущности уникальных индексов
	 * (хотя это экономия на спичках...)
	 */
	public function readByUniqueIndex(array $fields, $indexName = null) {
		$uniqueIndexList = $this->entity->getTableUnique();
		if(null !== $indexName && !empty($uniqueIndexList[$indexName])) {
			if( !$this->_checkUniqueIndex($fields, $uniqueIndexList[$indexName]) ) {
				$e = new RecordError('', RecordError::E_CANT_RD_BY_UQ_NOT_ALL_FLD);
				$this->addErrorException($e);
				// Здесь обязательно кидаем исключение,
				// поскольку задача программиста проследить за тем, что бы были заполнены все поля unique-индекса
				throw $e;
			}
			return $this->_readByUniqueIndex($fields, $uniqueIndexList[$indexName]);
		}
		// Ищем уникальный индекс, поля которого переданы в аргумент $fields
		$foundUniqueIndex = null;
		foreach($uniqueIndexList as $indexName => &$indexFields) {
			if($this->_checkUniqueIndex($fields, $indexFields, true)) {
				$foundUniqueIndex = $indexName;
			}
		}
		if(null === $foundUniqueIndex) {
			$e = new RecordError('', RecordError::E_CANT_RD_BY_UQ_NOT_ALL_FLD);
			$this->addErrorException($e);
			// Здесь обязательно кидаем исключение,
			// поскольку задача программиста проследить за тем, что бы были заполнены все поля unique-индекса
			throw $e;
		}
		return $this->_readByUniqueIndex($fields, $uniqueIndexList[$foundUniqueIndex]);
	}

	private function _readByUniqueIndex($fields) {
		$dbResult = $this->entity->getList(null, $fields);
		if( !($result = $dbResult->Fetch()) ) {
			$this->addErrorException(new RecordError('', RecordError::E_CANT_FIND_RECORD));
			return false;
		}
		$this->fieldsValues = $result;
		$this->bNewRecord = false;
		return true;
	}
	/**
	 * Проверяет заполнены ли все поля указанного unique-индекса
	 * @param array &$fields
	 * @param array &$indexFields
	 * @return bool
	 */
	private function _checkUniqueIndex(&$fields, &$indexFields) {
		foreach($indexFields as &$indexField) {
			if(!array_key_exists($indexField, $fields)) {
				return false;
			}
		}
		return true;
	}
}