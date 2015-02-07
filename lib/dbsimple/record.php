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

use OBX\Core\Exceptions\DBSimple\RecordError as Err;
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

	// DBSResult integration
	protected $result = null;

	const DEF_LAZY_LOAD = false;
	protected $lazyLoad = self::DEF_LAZY_LOAD;

	/**
	 * @param Entity $entity
	 * @param int|string|null|Entity $id
	 * @param null $select
	 * @param bool $lazyLoad
	 * @throws Err
	 */
	public function __construct(Entity $entity, $id = null, $select = null, $lazyLoad = self::DEF_LAZY_LOAD) {
		if( !($entity instanceof Entity) ) {
			throw new Err('', Err::E_RECORD_ENTITY_NOT_SET);
		}
		$this->entity = $entity;
		$this->primaryKey = $this->entity->getMainTablePrimaryKey();
		$this->primaryKeyAutoIncrement = $this->entity->getMainTableAutoIncrement();
		if(empty($select) || !is_array($select)) {
			$this->entityFields = array_keys($this->entity->getTableFieldsCheck());
		}
		else {
			$this->entityFields = array();
			$arRealTableFields = $this->entity->getTableFields();
			foreach($select as &$field) {
				if(!array_key_exists($field, $arRealTableFields)) {
					$this->entityFields[] = $field;
				}
			}
		}
		$this->MessagePool = $this->entity->getMessagePool();
		if(null !== $id) {
			$this->read($id, $select);
		}
		$this->lazyLoad = !!$lazyLoad;
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
			$e = new Err('', Err::E_READ_NOT_DBS_RESULT);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}
		if($this->entity !== $result->getDBSimpleEntity()) {
			$e = new Err('', Err::E_WRONG_DB_RESULT_ENTITY);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}
		if( $arResult = $result->Fetch() ) {
			$primaryKey = $this->entity->getMainTablePrimaryKey();
			if( !array_key_exists($primaryKey, $arResult)
				&& !$this->_checkUniqueIndex($arResult, $foundUniqueName)
			) {
				$e = new Err('', Err::E_READ_NO_IDENTITY_FIELD);
				$this->MessagePool->addErrorException($e);
				throw $e;
			}
			foreach($arResult as $field => &$value) {
				if(in_array($field, $this->entityFields)) {
					$this->fieldsValues[$field] = $value;
				}
			}
		}
		else {
			$this->MessagePool->addErrorException(new Err('', Err::E_FIND_RECORD));
			return false;
		}
		$this->bNewRecord = false;
		return true;
	}

	public function __set($field, $value) {
		if($this->primaryKey == $field) {
			$e = new Err('', Err::E_SET_PRIMARY_KEY_VALUE);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}

		if( true === $this->bNewRecord ) {
			if(!in_array($field, $this->entityFields)) {
				$e = new Err(array('#FIELD#' => $field), Err::E_SET_WRONG_FIELD);
				$this->MessagePool->addErrorException($e);
				throw $e;
			}
		}
		elseif(!array_key_exists($field, $this->fieldsValues)
			&& !in_array($field, $this->entityFields)
		) {
			$e = new Err(array('#FIELD#' => $field), Err::E_SET_WRONG_FIELD);
			$this->MessagePool->addErrorException($e);
			throw $e;
		}

		$this->fieldsValues[$field] = $value;
	}

	public function __get($field) {
		if(!array_key_exists($field, $this->fieldsValues)) {
			if( true !== $this->lazyLoad || !in_array($field, $this->entityFields)
			) {
				$e = new Err(array('#FIELD#' => $field), Err::E_GET_WRONG_FIELD);
				$this->MessagePool->addErrorException($e);
				throw $e;
			}
			// TODO: lazyLoad
			// Что бы получить недостающие данные, нам необходим первичный или unique ключ
			// Надо обязательно проверить получение этих данных в методе readFromDBResult
			// если эти данные не пришли, надо выбросить исключение
			$primaryKey = $this->entity->getMainTablePrimaryKey();
			if(array_key_exists($primaryKey, $this->fieldsValues)) {
				if( !$this->readFromDBResult($this->entity->getByID($this->fieldsValues[$primaryKey], null, true)) ) {
					$e = new Err(array('#FIELD#' => $field), Err::E_GET_LAZY_FIELD);
					$this->addErrorException($e);
					throw $e;
				}
			}
			else {
				if( !$this->_checkUniqueIndex($this->fieldsValues, $foundUniqueName) ) {
					$e = new Err(array('#FIELD#' => $field), Err::E_GET_LAZY_FIELD);
					$this->addErrorException($e);
					throw $e;
				}
				if( !$this->readByUniqueIndex($this->fieldsValues) ) {
					$e = new Err(array('#FIELD#' => $field), Err::E_GET_LAZY_FIELD);
					$this->addErrorException($e);
					throw $e;
				}
			}
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
	 * @throws Err
	 * Прочитать запись по значениям полей уникального индекса
	 * Имя индекса можно указать явно. Это необходимо в том случае если в $fields
	 * указаны значения полей подходящих под несколько уникальных ключей и нужна явная
	 * возможность убрать неопределенность. Однако если не указать, то будет выбран первый
	 * попавшийся ключ, значения которого будут заполнены
	 */
	public function readByUniqueIndex(array $fields, $indexName = null) {
		// Првоеряем есть ли поля уникального индекса
		if(!$this->_checkUniqueIndex($fields, $indexName)) {
			$e = new Err('', Err::E_READ_BY_UQ_NOT_ALL_FLD);
			$this->addErrorException($e);
			// Здесь обязательно кидаем исключение,
			// поскольку задача программиста проследить за тем, что бы были заполнены все поля unique-индекса
			throw $e;
		}
		$arUniqueList = $this->entity->getTableUnique();
		$filterFieldsList = $arUniqueList[$indexName];
		$filter = array();
		foreach($filterFieldsList as $filterField) {
			$filter[$filterField] = $this->fieldsValues[$filterField];
		}
		$dbResult = $this->entity->getList(null, $filter);
		if( !($result = $dbResult->Fetch()) ) {
			$this->addErrorException(new Err('', Err::E_FIND_RECORD));
			return false;
		}
		$this->fieldsValues = $result;
		$this->bNewRecord = false;
		return true;
	}

	/**
	 * Проверяет заполнены ли все поля указанного unique-индекса
	 * @param array &$fields
	 * @param string &$indexName
	 * @return bool
	 */
	private function _checkUniqueIndex(&$fields, &$indexName = null) {
		static $uniqueIndexList = null;
		if(null === $uniqueIndexList) $uniqueIndexList = $this->entity->getTableUnique();

		if(null !== $indexName) {
			if( !array_key_exists($indexName, $uniqueIndexList) ) {
				$indexName = null;
			}
		}
		if(null === $indexName) {
			foreach($uniqueIndexList as $uniqueName => &$indexFieldsList) {
				foreach($indexFieldsList as $fieldCode) {
					if(!array_key_exists($fieldCode, $fields)) {
						return false;
					}
				}
				$indexName = $uniqueName;
			}
		}
		else {
			foreach($uniqueIndexList[$indexName] as $fieldCode) {
				if(!array_key_exists($fieldCode, $fields)) {
					return false;
				}
			}
		}
		return true;
	}
}