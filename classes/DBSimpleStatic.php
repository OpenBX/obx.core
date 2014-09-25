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


interface IDBSimpleStatic
{
	//static function getInstance();
	static function add($arFields);
	static function update($arFields, $bNotUpdateUniqueFields = false);
	static function delete($PRIMARY_KEY_VALUE);
	static function deleteByFilter($arFields);
	static function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	static function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	static function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnCDBResult = false);
	static function getLastQueryString();
}

abstract class DBSimpleStatic extends MessagePoolStatic implements IDBSimpleStatic {
	static protected $_arDBSimple = array();
	final static public function __initDBSimple(DBSimple $DBSimple) {
		$className = get_called_class();
		if( !isset(self::$_arDBSimple[$className]) ) {
			if($DBSimple instanceof DBSimple) {
				self::$_arDBSimple[$className] = $DBSimple;
				self::setMessagePool($DBSimple->getMessagePool());
			}
		}
	}

	/**
	 * @return DBSimple
	 * @throws \ErrorException
	 */
	final static public function getInstance() {
		$className = get_called_class();
		if( isset(self::$_arDBSimple[$className]) ) {
			return self::$_arDBSimple[$className];
		}
		$className = str_replace('OBX_', 'OBX\\', $className);
		if( isset(self::$_arDBSimple[$className]) ) {
			return self::$_arDBSimple[$className];
		}
		throw new \ErrorException("Static Class $className not initialized. May be in static decorator class used non static method. See Call-Stack");
	}
	static public function add($arFields) {
		return self::getInstance()->add($arFields);
	}
	static function update($arFields, $bNotUpdateUniqueFields = false) {
		return self::getInstance()->update($arFields, $bNotUpdateUniqueFields);
	}
	static public function delete($PRIMARY_KEY_VALUE) {
		return self::getInstance()->delete($PRIMARY_KEY_VALUE);
	}
	static public function deleteByFilter($arFilter, $bCheckExistence = true) {
		return self::getInstance()->deleteByFilter($arFilter, $bCheckExistence);
	}
	static public function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnCDBResult = false) {
		return self::getInstance()->getByID($PRIMARY_KEY_VALUE, $arSelect, $bReturnCDBResult);
	}
	static public function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true) {
		return self::getInstance()->getList($arSort, $arFilter, $arGroupBy, $arPagination, $arSelect, $bShowNullFields);
	}
	static function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true) {
		return self::getInstance()->getListArray($arSort, $arFilter, $arGroupBy, $arPagination, $arSelect, $bShowNullFields);
	}
	static function getLastQueryString() {
		return self::getInstance()->getLastQueryString();
	}
	public static function getFieldNames($arSelect = null){
		return self::getInstance()->getFieldNames($arSelect);
	}

	public static function getEditFields() {
		return self::getInstance()->getEditFields();
	}

	public static function getFieldsDescription(){
		return self::getInstance()->getFieldsDescription();
	}
}