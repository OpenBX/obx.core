<?php
/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @License GPLv3                  **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

IncludeModuleLangFile(__FILE__);

interface OBX_IDBSimple
{
	//static function getInstance();
	function add($arFields);
	function update($arFields, $bNotUpdateUniqueFields = false);
	function delete($PRIMARY_KEY_VALUE);
	function deleteByFilter($arFields);
	function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnCDBResult = false);
}

interface OBX_IDBSimpleStatic
{
	//static function getInstance();
	static function add($arFields);
	static function update($arFields, $bNotUpdateUniqueFields = false);
	static function delete($PRIMARY_KEY_VALUE);
	static function deleteByFilter($arFields);
	static function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	static function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	static function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnCDBResult = false);
}

abstract class OBX_DBSimpleStatic extends OBX_CMessagePoolStatic implements OBX_IDBSimpleStatic {
	static protected $_arDBSimple = array();
	final static public function __initDBSimple($DBSimple) {
		$className = get_called_class();
		if( !isset(self::$_arDBSimple[$className]) ) {
			if($DBSimple instanceof OBX_DBSimple) {
				self::$_arDBSimple[$className] = $DBSimple;
				self::setMessagePool($DBSimple->getMessagePool());
			}
		}
	}

	/**
	 * @return OBX_DBSimple
	 * @throws Exception
	 */
	final static public function getInstance() {
		$className = get_called_class();
		if( !isset(self::$_arDBSimple[$className]) ) {
			throw new Exception("Static Class $className not initialized. May be in static decorator class used non static method. See Call-Stack");
		}
		return self::$_arDBSimple[$className];
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

abstract class OBX_DBSimple extends OBX_CMessagePoolDecorator
{
	protected function __construct() {}
	final protected function __clone() {}

	static protected $_arDBSimple = array();

	/**
	 * @return OBX_DBSimple
	 */
	final static public function getInstance() {
		$className = get_called_class();
		if( !isset(self::$_arDBSimple[$className]) ) {
			self::$_arDBSimple[$className] = new $className;
		}
		return self::$_arDBSimple[$className];
	}


	// FIELD TYPES
	const FLD_T_NO_CHECK = 1;				// без проверки - использовать с FLD_CUSTOM_CK
	const FLD_T_INT = 2;					// целый
	const FLD_T_CHAR = 4;					// один символ
	const FLD_T_STRING = 8;					// любая строка: htmlspecialcharsEx
	const FLD_T_CODE = 16;					// Символьный код: ~^[a-zA-Z\_][a-z0-9A-Z\_]{0,15}$~
	const FLD_T_BCHAR = 32;					// битриксовский булев символ:) : 'Y' || 'N'
	const FLD_T_FLOAT = 64;					// десятичный
	const FLD_T_IDENT = 128;				// любой идентификатор ~^[a-z0-9A-Z\_]{1,254}$~

	const FLD_T_BX_LANG_ID = 256;			// Битриксовский LID два символа
	const FLD_T_IBLOCK_ID = 512;			// ID Инфоблока. Проверяет наличие
	const FLD_T_IBLOCK_PROP_ID = 1024;		// ID свойства элемента ИБ. Проверяет наличие
	const FLD_T_IBLOCK_ELEMENT_ID = 2048;	// ID элемента инфоблока. Проверяет наличие
	const FLD_T_IBLOCK_SECTION_ID = 4096;	// ID секции инфблока. Проверяет наличие
	const FLD_T_USER_ID = 8192;				// ID пользвоателя битрикс
	const FLD_T_GROUP_ID = 16384;			// ID группы пользователей битрикс

	// FIELD ATTR
	const FLD_NOT_NULL = 131072;		// не нуль
	const FLD_DEFAULT = 262144;			// задать значение по дефолту если нуль - зн-я по умолч. в массиве $this->_arTableFieldsDefault
	const FLD_REQUIRED = 524288;		// значение поля является обязательным при добавлении новой строки
	const FLD_CUSTOM_CK = 1048576;		// своя ф-ия проверки значения
	const FLD_UNSET = 2097152;			// выкинуть значение из arFields!
	const FLD_BRK_INCORR = 4194304;		// прервать выполнение ф-ии, если значение неверно
	const FLD_ATTR_ALL = 8257536;		// все вместе: FLD_NOT_NULL | FLD_DEF_NULL | FLD_REQUIRED | FLD_CUSTOM_CHECK


	const ERR_NOTHING_TO_DELETE = 1024;		// невозможно удалить. заись не найдена
	const ERR_DUP_PK = 2048;				// Запись с таким PRIMARY_KEY уже есть
	const ERR_DUP_UNIQUE = 4096;			// дублирование значения уникального индекса
	const ERR_MISS_REQUIRED = 8192;			// Не заполнено обязательное поле
	const ERR_NOTHING_TU_UPDATE = 16384;	// невозможно обновить. запись не найдена
	const ERR_CANT_DEL_WITHOUT_PK = 32768;  // невозсожно использовать метод delete без использования PrimaryKey
	//const WRN_
	//const MSG_
	
	const PREPARE_ADD = 3;
	const PREPARE_UPDATE = 5;

	/**
	 * @const FLD_CUSTOM_CK - бит наличия своей ф-ии проверки значения
	 * Тут подбробнее
	 * Суть в том, что в классе-наследнике, если указать этот аттрибут поля,
	 * при выполнении $this->prepareFieldsData()
	 * будет выполнена ф-ия класса-наследника вида __check_<ИМЯ_ПОЛЯ>($fieldValue, $arCheckData)
	 * Таким образом можно в классе наследгнике добавить свою проверку типа
	 * для метода $this->prepareFieldsData()
	 */

	protected $_arTableList = array();
	protected $_arTableFields = array();
	protected $_mainTable = '';
	protected $_mainTablePrimaryKey = 'ID';
	protected $_mainTableAutoIncrement = 'ID';
	protected $_arTableLinks = array();
	protected $_arTableLeftJoin = array();
	protected $_arTableRightJoin = array();
	protected $_arTableJoinNullFieldDefaults = array();
	protected $_arTableIndex = array();
	protected $_arTableUnique = array();
	protected $_arFilterDefault = array();
	protected $_arSelectDefault = array();
	protected $_arSortDefault = array('ID' => 'ASC');

	protected $_arTableFieldsCheck = array();
	protected $_arDBSimpleLangMessages = array();
	protected $_arTableFieldsDefault = array();

	protected $_arGroupByFields = array();

	protected $_arFieldsDescription = array();
	protected $_arFieldsEditInAdmin = array();

	protected function prepareFieldsData($prepareType, &$arFields, $arTableFieldsCheck = null, $arTableFieldsDefault = null) {

		global $DB;
		$arFieldsPrepared = array();
		if($arTableFieldsDefault==null) {
			$arTableFieldsDefault = $this->_arTableFieldsDefault;
		}
		else {
			$arTableFieldsDefault = array_merge($this->_arTableFieldsDefault, $arTableFieldsDefault);
		}
		if($arTableFieldsCheck==null) {
			$arTableFieldsCheck = $this->_arTableFieldsCheck;
		}
		else {
			$arTableFieldsCheck = array_merge($this->_arTableFieldsCheck, $arTableFieldsCheck);
		}

		$arCheckResult = array(
			'__BREAK' => false
		);
		foreach($arFields as $fieldName => &$fieldValue)
		{
			$arCheckResult[$fieldName] = null;
			if( array_key_exists($fieldName, $arTableFieldsCheck) )
			{
				$arCheckResult[$fieldName] = array(
					'RAW_VALUE' => $fieldValue,
					'FIELD_TYPE' => null,
					'FIELD_TYPE_MASK' => 0,
					'FIELD_ATTR' => array(),
					'IS_EMPTY' => false,
					'IS_CORRECT' => false,
					'FROM_DEFAULTS' => false,
					'CHECK_DATA' => array()
				);
				$fieldType = $arTableFieldsCheck[$fieldName];
				$bValueIsCorrect = false;
				$bNotNull = false;
				$bDefaultIfNull = false;
				if( $fieldType & self::FLD_NOT_NULL) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_NOT_NULL'] = self::FLD_NOT_NULL;
					$bNotNull = true;
				}
				if( ($fieldType & self::FLD_DEFAULT) ) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_DEFAULT'] = self::FLD_DEFAULT;
					if( $prepareType == self::PREPARE_ADD ) {
						$bDefaultIfNull = true;
					}
				}
				if( $fieldType & self::FLD_REQUIRED ) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_REQUIRED'] = self::FLD_REQUIRED;
				}
				$bEmpty = empty($fieldValue);
				if($bEmpty) {
					$arCheckResult[$fieldName]['IS_EMPTY'] = true;
				}
				switch( ($fieldType & ~self::FLD_ATTR_ALL) ) {
					case self::FLD_T_NO_CHECK:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_NO_CHECK';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_NO_CHECK;
						$bValueIsCorrect = true;
						break;
					case self::FLD_T_CHAR:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_CHAR';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_CHAR;
						if( (!$bNotNull && $bEmpty) || !$bEmpty ) {
							$fieldValue = substr($fieldValue, 0 ,1);
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_INT:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_INT';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_INT;
						$fieldValue = intval($fieldValue);
						if( (!$bNotNull && $fieldValue==0) || $fieldValue>0 ) {
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_STRING:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_STRING';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_STRING;
						$valStrLen = strlen($fieldValue);
						if( (!$bNotNull && $valStrLen==0) || $valStrLen>0 ) {
							$fieldValue = $DB->ForSql(htmlspecialcharsEx($fieldValue));
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_CODE:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_CODE';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_CODE;
						$fieldValue = trim($fieldValue);
						if( (!$bNotNull && empty($fieldValue)) || preg_match('~^[a-zA-Z\_][a-z0-9A-Z\_]{0,15}$~', $fieldValue) ) {
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_BCHAR:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_BCHAR';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_BCHAR;
						if( !$bNotNull && empty($fieldValue) ) {
							$bValueIsCorrect = true;
						}
						else {
							$fieldValue = strtoupper($fieldValue);
							if( $fieldValue == 'Y' || $fieldValue == 'N' ) {
								$bValueIsCorrect = true;
							}
						}
						break;
					case self::FLD_T_FLOAT:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_FLOAT';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_FLOAT;
						$fieldValue = floatval($fieldValue);
						if( (!$bNotNull && $fieldValue==0) || $fieldValue>0 ) {
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_IDENT:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IDENT';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IDENT;
						$fieldValue = trim($fieldValue);
						if(
								( !$bNotNull && empty($fieldValue) )
							||	( is_numeric($fieldValue) && ($fieldValue = intval($fieldValue))>0 )
							||	( preg_match('~^[a-z0-9A-Z\_]{1,255}$~', $fieldValue) )
						) {
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_BX_LANG_ID:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_BX_LANG_ID';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_BX_LANG_ID;
						$fieldValue = trim($fieldValue);
						if( strlen($fieldValue)>0 && preg_match('~^[a-zA-Z\_][a-z0-9A-Z\_]?$~', $fieldValue)) {
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_IBLOCK_ID:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IBLOCK_ID';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IBLOCK_ID;
						$fieldValue = intval($fieldValue);
						$rs = CIBlock::GetByID($fieldValue);
						if( ($arData = $rs->GetNext()) ) {
							$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_IBLOCK_PROP_ID:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IBLOCK_PROP_ID';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IBLOCK_PROP_ID;
						$rs = CIBlockProperty::GetByID($fieldValue);
						if( ($arData = $rs->GetNext()) ) {
							$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_IBLOCK_ELEMENT_ID:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IBLOCK_ELEMENT_ID';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IBLOCK_ELEMENT_ID;
						$rs = CIBlockElement::GetByID($fieldValue);
						if( ($arData = $rs->GetNext()) ) {
							$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_IBLOCK_SECTION_ID:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IBLOCK_SECTION_ID';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IBLOCK_SECTION_ID;
						$rs = CIBlockSection::GetByID($fieldValue);
						if( ($arData = $rs->GetNext()) ) {
							$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_USER_ID:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_USER_ID';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_USER_ID;
						$rs = CUser::GetByID($fieldValue);
						if( ($arData = $rs->GetNext()) ) {
							$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
							$bValueIsCorrect = true;
						}
						break;
					case self::FLD_T_GROUP_ID:
						$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_GROUP_ID';
						$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_GROUP_ID;
						$rs = CGroup::GetByID($fieldValue);
						if( ($arData = $rs->GetNext()) ) {
							$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
							$bValueIsCorrect = true;
						}
						break;
				}
				if( $fieldType & self::FLD_CUSTOM_CK ) {
					$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_CUSTOM_CK';
					$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_CUSTOM_CK;
					if( $bValueIsCorrect ) {
						$customCheckFunc = '__check_'.$fieldName;
						if( is_callable(array($this, $customCheckFunc)) ) {
							//$bValueIsCorrect = call_user_func($customCheckFunc, $fieldValue, $arCheckResult[$fieldName]['CHECK_DATA']);
							$bValueIsCorrect = $this->$customCheckFunc($fieldValue, $arCheckResult[$fieldName]['CHECK_DATA']);
						}
					}
				}
				if( !$bValueIsCorrect && ($fieldType & self::FLD_BRK_INCORR) ) {
					$arCheckResult['__BREAK'] = true;
				}

				if($bEmpty && $bDefaultIfNull) {
					if(array_key_exists($fieldName, $arTableFieldsDefault)) {
						$arCheckResult[$fieldName]['FROM_DEFAULTS'] = true;
						$arFieldsPrepared[$fieldName] = $arTableFieldsDefault[$fieldName];
					}
				}
				elseif($bValueIsCorrect) {
					$arCheckResult[$fieldName]['IS_CORRECT'] = true;
					$arFieldsPrepared[$fieldName] = $fieldValue;
				}
			}
		}
		$arFields = $arFieldsPrepared;
		return $arCheckResult;
	}

	/**
	 * Очень удобная ф-ия для использования после $this->prepareFieldsData
	 * $this->prepareFieldsData отсекает пустые или невалидные данные,
	 * а эта -фия проверяет те из них которые обязательны для внесения в БД
	 * Например:
	 *		prepareFieldsData отсек обязательное поле CODE которое не прошло валидацию,
	 * 		если для поля выставлен аттрибут обязательного наличия(self::FLD_REQUIRED), то данная ф-ия вернет
	 * 		данное поле в результирующем массиве
	 * @param $arFields ссылка - поля переданные в аргументе
	 * @param null $arTableFieldsDefault - значения полей по умолчанию, если поле потеряно, но есть дефолтное значение, будет подставлено оно
	 * @return array Массив пропущенных обязательных значений
	 */
	protected function checkRequiredFields(&$arFields, &$arCheckResult, $arTableFieldsCheck = null, $arTableFieldsDefault = null) {

		if($arTableFieldsDefault==null) {
			$arTableFieldsDefault = $this->_arTableFieldsDefault;
		}
		else {
			$arTableFieldsDefault = array_merge($this->_arTableFieldsDefault, $arTableFieldsDefault);
		}
		if($arTableFieldsCheck==null) {
			$arTableFieldsCheck = $this->_arTableFieldsCheck;
		}
		else {
			$arTableFieldsCheck = array_merge($this->_arTableFieldsCheck, $arTableFieldsCheck);
		}
		$arMessedFields = array();
		foreach($arTableFieldsCheck as $asFieldName => &$fieldAttr) {
			$bRequired = ($fieldAttr & self::FLD_REQUIRED)?true:false;
			if( $bRequired && !array_key_exists($asFieldName, $arFields) ) {
				if( ($fieldAttr & self::FLD_DEFAULT)
					&& ( !isset($arFields[$asFieldName]) || $arCheckResult[$asFieldName]['IS_EMPTY'] )
					&& array_key_exists($asFieldName, $arTableFieldsDefault)
				) {
					$arFields[$asFieldName] = $arTableFieldsDefault[$asFieldName];
				}
				else {
					$arMessedFields[] = $asFieldName;
				}
			}
		}
		return $arMessedFields;
	}

	protected function preparePagination(&$arPagination) {

	}

	public function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true) {
		global $DB;

		$arTableList = $this->_arTableList;
		$arTableLinks = $this->_arTableLinks;
		$arTableFields = $this->_arTableFields;
		$arTableLeftJoin = $this->_arTableLeftJoin;
		$arTableRightJoin = $this->_arTableRightJoin;

		$sFields = '';
		$arSelectFromTables = array();
		$sSelectFrom = '';
		$sJoin = '';
		$sWhereTblLink = '';
		$sWhereFilter = '';
		foreach($arTableList as $asTblName => $fullTblName) {
			$arSelectFromTables[$asTblName] = false;
			if($asTblName == $this->_mainTable) {
				$arSelectFromTables[$asTblName] = true;
			}
		}

		// SELECT
		if( empty($arSelect) || !is_array($arSelect) ) {
			// Если SELECT пустой
			$arSelectDefault = $this->_arSelectDefault;
			if( count($arSelectDefault)>0 ) {
				$arSelect = $arSelectDefault;
			}
			else {
				foreach ($arTableFields as $fieldCode => $arSqlField) {
					$arSelect[] = $fieldCode;
				}
			}
		}
		$bFirst = true;
		foreach($arSelect as $fieldCode) {
			if(array_key_exists($fieldCode, $arTableFields) ) {
				$arTblField = $arTableFields[$fieldCode];
				list($asName, $tblFieldName) = each($arTblField);
				$isSubQuery = ((strpos($tblFieldName,'(')===false)?false:true);
				if(!$isSubQuery){
					$sqlField = $asName.'.'.$tblFieldName;
				}
				else{
					$sqlField = $tblFieldName;
				}

				$sFields .= (($bFirst)?"\n\t":", \n\t").$sqlField.' AS '.$fieldCode;
				$bFirst = false;
				$arSelectFromTables[$asName] = true;
			}
		}

		$arFilterDefault = $this->_arFilterDefault;
		$bFilterIsDefault = false;
		if( (empty($arFilter) || !is_array($arFilter)) && count($arFilterDefault)>0 ) {
			$arFilter = $arFilterDefault;
			$bFilterIsDefault = true;
		}
		if( is_array($arFilter) && !empty($arFilter) ) {
			if( !$bFilterIsDefault && count($arFilterDefault)>0 ) {
				$arFilter = array_merge($arFilterDefault, $arFilter);
			}
			foreach( $arFilter as $fieldCode => $filterFieldValue) {
				if( $filterFieldValue === null || $filterFieldValue == 'undefined' ) {
					continue;
				}
				$EQ = '=';
				$arrayFieldValueCond = 'OR';
				$fieldCodeCond1 = substr($fieldCode, 0, 1);
				$fieldCodeCond2 = substr($fieldCode, 0, 2);
				if( $fieldCodeCond1 == '!' ) {
					$fieldCode = substr($fieldCode, 1);
					$EQ = '<>';
					$arrayFieldValueCond = 'AND';
				}
				elseif( $fieldCodeCond1 == '<') {
					if($fieldCodeCond2 == '<=') {
						$fieldCode = substr($fieldCode, 2);
						$EQ = '<=';
					}
					else {
						$fieldCode = substr($fieldCode, 1);
						$EQ = '<';
					}
				}
				elseif( $fieldCodeCond1 == '>') {
					if($fieldCodeCond2 == '>=') {
						$fieldCode = substr($fieldCode, 2);
						$EQ = '>=';
					}
					else {
						$fieldCode = substr($fieldCode, 1);
						$EQ = '>';
					}
				}
				if(array_key_exists($fieldCode, $arTableFields)) {
					$arTblField = $arTableFields[$fieldCode];
					list($asName, $tblFieldName) = each($arTblField);
					$sqlField = $asName.'.'.$tblFieldName;
					if( !is_array($filterFieldValue) ) {
						$filterFieldValue = $DB->ForSql($filterFieldValue);
						$sWhereFilter .= "\n\tAND (".$sqlField.' '.$EQ.' \''.$filterFieldValue.'\')';
					}
					elseif( count($filterFieldValue)>0 ) {
						$sWhereFilter .= "\n\tAND (";
						$bFirstFilterFieldPart = true;
						foreach($filterFieldValue as &$filterFieldValuePart) {
							$filterFieldValuePart = $DB->ForSql($filterFieldValuePart);
							$sWhereFilter .= "\n\t"
								.($bFirstFilterFieldPart?"\t\t":"\t".$arrayFieldValueCond.' ')
								.$sqlField.' '.$EQ.' \''.$filterFieldValuePart.'\''
							;
							$bFirstFilterFieldPart = false;
						}
						$sWhereFilter .= "\n\t)";
					}
					$arSelectFromTables[$asName] = true;
				}
			}
		}

		if( empty($arSort) || !is_array($arSort) ) {
			$arSortDefault = $this->_arSortDefault;
			if( count($arSortDefault)>0 ) {
				$arSort = $arSortDefault;
			}
		}
		$sSort = '';
		$bFirst = true;
		foreach($arSort as $fieldCode => $orAscDesc) {
			if( array_key_exists($fieldCode, $arTableFields) ) {
				$orAscDesc = strtoupper($orAscDesc);
				if($orAscDesc == 'ASC' || $orAscDesc == 'DESC') {
					$arTblField = $arTableFields[$fieldCode];
					list($asName, $tblFieldName) = each($arTblField);
					$isSubQuery = ((strpos($tblFieldName,'(')===false)?false:true);
					if (!$isSubQuery){
						$sqlField = $asName.'.'.$tblFieldName;
					}else{
						$sqlField = $fieldCode;
					}
					$sSort .= (($bFirst)?"\nORDER BY \n\t":", \n\t").$sqlField.' '.$orAscDesc;
					$bFirst = false;
					$arSelectFromTables[$asName] = true;
				}
			}
		}

		// Группируем
		$arGroupByFields = $this->_arGroupByFields;
		if( is_array($arGroupBy) && count($arGroupBy) > 0 ) {
			foreach ($arGroupBy as $fieldCode){
				if( isset($arTableFields[$fieldCode]) ) {
					$arTblField = $arTableFields[$fieldCode];
					list($asName, $tblFieldName) = each($arTblField);
					$arGroupByFields[$asName] = $tblFieldName;
				}
			}
		}
		$sGroupBy = '';
		$arSqlGroupedByField = array();
		foreach($arGroupByFields as $tblAlias => $tblFieldName) {
			$arSqlGroupedByField[] = $tblAlias.'.'.$tblFieldName;
		}
		if (count($arSqlGroupedByField) > 0){
			$sGroupBy = "\nGROUP BY ( ".implode(", ",$arSqlGroupedByField)." )";
		}

		// Часть WHERE в которой связываем таблицы
		foreach($arTableLinks as $linkKey => $arTblLink) {
			$arLeftField = $arTblLink[0];
			$arRightField = $arTblLink[1];
			list($asLeftTblName, $leftFieldName) = each($arLeftField);
			list($asRightTblName, $rightFieldName) = each($arRightField);
			if( $bShowNullFields
				&& ( array_key_exists($asLeftTblName, $arTableLeftJoin)
					|| array_key_exists($asLeftTblName, $arTableRightJoin)
					|| array_key_exists($asRightTblName, $arTableLeftJoin)
					|| array_key_exists($asRightTblName, $arTableRightJoin))
			) {
				continue;
			}
			if( $arSelectFromTables[$asLeftTblName] && $arSelectFromTables[$asRightTblName] ) {
				$sWhereTblLink .= "\n\t AND ".$asLeftTblName.'.'.$leftFieldName.' = '.$asRightTblName.'.'.$rightFieldName;
			}
			continue;
		}
		unset($asTblName, $linkKey, $arTblLink);

		$arTableLeftJoinTables = $arTableLeftJoin;
		foreach($arTableLeftJoinTables as $sdTblName => &$bJoinThisTable) {
			$bJoinThisTable = false;
		}
		$arTableRightJoin = $arTableRightJoin;
		foreach($arTableRightJoin as $sdTblName => &$bJoinThisTable) {
			$bJoinThisTable = false;
		}
		// Из каких таблиц выбираем | какие таблицы джойним
		$bFirstSelectFrom = true;
		foreach($arSelectFromTables as $asTblName => $bSelectFromTable) {
			if($bSelectFromTable) {
				if( $bShowNullFields && array_key_exists($asTblName, $arTableLeftJoinTables) ) {
					$arTableLeftJoinTables[$asTblName] = true;
					$sJoin .= "\nLEFT JOIN\n\t".$arTableList[$asTblName].' AS '.$asTblName.' ON ('.$arTableLeftJoin[$asTblName].')';
				}
				elseif( $bShowNullFields && array_key_exists($asTblName, $arTableRightJoin) ) {
					$arTableRightJoin[$asTblName] = true;
					$sJoin .= "\nRIGHT JOIN\n\t".$arTableList[$asTblName].' AS '.$asTblName.' ON ('.$arTableRightJoin[$asTblName].')';
				}
				else {
					$sSelectFrom .= (($bFirstSelectFrom)?"\n\t":", \n\t").$arTableList[$asTblName].' AS '.$asTblName;
					$bFirstSelectFrom = false;
				}
			}
		}
		if($bFirstSelectFrom) {
			list($firstTableAlias, $firstTableName) = each($arTableList);
			$sSelectFrom .= "\n\t".$firstTableName.' AS '.$firstTableAlias;
		}
		$sWhere = '';
		if( !empty($sSelectFrom) || !empty($sSelectFrom) ) {
			$sWhere = "\nWHERE (1=1)".$sWhereTblLink.$sWhereFilter;
		}

		$sqlList = 'SELECT '.$sFields."\nFROM ".$sSelectFrom.$sJoin.$sWhere.$sGroupBy.$sSort;
		$res = $DB->Query($sqlList, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		return $res;
	}

	public function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true) {

		$arTableJoinNullFieldDefaults = $this->_arTableJoinNullFieldDefaults;
		$rsList = $this->getList($arSort, $arFilter, $arGroupBy, $arPagination, $arSelect, $bShowNullFields);
		$arList = array();
		while( $arItem = $rsList->Fetch() ) {
			if( count($arTableJoinNullFieldDefaults)>0 ) {
				foreach($arItem as $fieldName => &$fieldValue) {
					if( empty($fieldValue) && array_key_exists($fieldName, $arTableJoinNullFieldDefaults) ) {
						$fieldValue = $arTableJoinNullFieldDefaults[$fieldName];
					}
				}
			}
			$arList[] = $arItem;
		}
		return $arList;
	}

	public function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnCDBResult = false) {
		global $DB;

		$arTableList = $this->_arTableList;
		$arTableLinks = $this->_arTableLinks;
		$arTableFields = $this->_arTableFields;
		$mainTable = $this->_mainTable;
		$mainTablePrimaryKey = $this->_mainTablePrimaryKey;
		$mainTableAutoIncrement = $this->_mainTableAutoIncrement;

		$sFields = '';
		$arSelectFromTables = array();
		$sSelectFrom = '';
		$sWhereTblLink = '';
		$sWhere = "\nWHERE";

		$arMainTableLinkStrings = array();
		foreach($arTableLinks as $arTableLink) {
			$arLeftField = $arTableLink[0];
			$arRightField = $arTableLink[1];
			list($leftTblAlias, $leftTblField) = each($arLeftField);
			list($rightTblAlias, $rightTblField) = each($arRightField);
			if($leftTblAlias == $mainTable) {
				$arMainTableLinkStrings[$rightTblAlias.'.'.$rightTblField] = array($leftTblAlias, $leftTblField);
			}
			if($rightTblAlias == $mainTable) {
				$arMainTableLinkStrings[$leftTblAlias.'.'.$leftTblField] = array($rightTblAlias, $rightTblField);
			}
		}

		foreach($arTableList as $asTblName => $fullTblName) {
			$arSelectFromTables[$asTblName] = false;
			if($asTblName == $mainTable) {
				$arSelectFromTables[$asTblName] = true;
			}
		}
		// SELECT
		if( empty($arSelect) || !is_array($arSelect) ) {
			// Если SELECT пустой
			$arSelect = array();
			foreach ($arTableFields as $fieldCode => $arSqlField) {
				list($tlbAlias, $tblFieldName) = each($arSqlField);
				if($tlbAlias == $mainTable || array_key_exists($tlbAlias.'.'.$tblFieldName, $arMainTableLinkStrings)) {
					$arSelect[] = $fieldCode;
				}
			}
		}
		$bFirst = true;
		$arAlreadySelected = array();
		foreach($arSelect as $fieldCode) {
			if(array_key_exists($fieldCode, $arTableFields) ) {
				$arTblField = $arTableFields[$fieldCode];
				list($asName, $tblFieldName) = each($arTblField);
				// Очень спорный момент. Нужно аккуратно проектировать подзапросы
//				$isSubQuery = ((strpos($tblFieldName,'(')===false)?false:true);
//				if($isSubQuery){
//					continue;
//				}
				if($asName != $mainTable) {
					if( !array_key_exists($asName.'.'.$tblFieldName, $arMainTableLinkStrings) ) {
						continue;
					}
					$arrTmp = $arMainTableLinkStrings[$asName.'.'.$tblFieldName];
					$asName = $arrTmp[0];
					$tblFieldName = $arrTmp[1];
				}
				$sqlField = $asName.'.'.$tblFieldName;
				if( array_key_exists($sqlField, $arAlreadySelected) ) continue;
				$sFields .= (($bFirst)?"\n\t":", \n\t").$sqlField.' AS '.$fieldCode;
				$arAlreadySelected[$sqlField] = true;
				$bFirst = false;
				$arSelectFromTables[$asName] = true;
			}
		}
		// Часть WHERE в которой связываем таблицы
		foreach($arTableLinks as $linkKey => $arTblLink) {
			$arLeftField = $arTblLink[0];
			$arRightField = $arTblLink[1];
			list($asLeftTblName, $leftFieldName) = each($arLeftField);
			list($asRightTblName, $rightFieldName) = each($arRightField);
			if( $arSelectFromTables[$asLeftTblName] && $arSelectFromTables[$asRightTblName] ) {
				$sWhereTblLink .= "\n\t AND ".$asLeftTblName.'.'.$leftFieldName.' = '.$asRightTblName.'.'.$rightFieldName;
			}
			continue;
		}
		unset($asTblName, $linkKey, $arTblLink);

		$bFirstSelectFrom = true;
		foreach($arSelectFromTables as $asTblName => $bSelectFromTable) {
			if($bSelectFromTable) {
				$sSelectFrom .= (($bFirstSelectFrom)?"\n\t":", \n\t").$arTableList[$asTblName].' AS '.$asTblName;
				$bFirstSelectFrom = false;
			}
		}

		if($mainTablePrimaryKey == $mainTableAutoIncrement) {
			$PRIMARY_KEY_VALUE = intval($PRIMARY_KEY_VALUE);
		}
		else {
			$this->prepareFieldsData(self::PREPARE_UPDATE, $arFilter = array($mainTablePrimaryKey => $PRIMARY_KEY_VALUE));
			$PRIMARY_KEY_VALUE = $arFilter[$mainTablePrimaryKey];
		}
		$sWhere .= "\n\t".$mainTable.'.'.$mainTablePrimaryKey.' = \''.$PRIMARY_KEY_VALUE.'\'';
		$sWhere .= $sWhereTblLink;
		$sqlByPrimaryKey = 'SELECT '.$sFields."\nFROM ".$sSelectFrom.$sWhere;
		$rsList = $DB->Query($sqlByPrimaryKey, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		if(!$bReturnCDBResult) {
			if( ($arElement = $rsList->Fetch()) ) {
				return $arElement;
			}
			return array();
		}
		return $rsList;
	}

	protected function _onStartAdd(&$arFields) { return true; }
	protected function _onBeforeAdd(&$arFields, &$arCheckResult) { return true; }
	protected function _onAfterAdd(&$arFields) { return true; }

	protected function _getLangMessageReplace($field) {
		$arLangReplace = array(
			'FIELD' => $field
		);
		$arFieldsDescription = $this->_arFieldsDescription;
		if( !is_array($arFieldsDescription) && count($arFieldsDescription)>0 ) {
			if( array_key_exists($field, $arFieldsDescription) ) {
				if( array_key_exists('NAME', $arFieldsDescription[$field]) ) {
					$arLangReplace['#'.$field.'_NAME#'] = $arFieldsDescription[$field]['NAME'];
					$arLangReplace['#FIELD_NAME#'] = $arFieldsDescription[$field]['NAME'];
				}
				if( array_key_exists('DESC', $arFieldsDescription[$field]) ) {
					$arLangReplace['#'.$field.'_DESCRIPTION#'] = $arFieldsDescription[$field]['DESC'];
					$arLangReplace['#FIELD_DESCRIPTION#'] = $arFieldsDescription[$field]['DESC'];
				}
				if( array_key_exists('DESCR', $arFieldsDescription[$field]) ) {
					$arLangReplace['#'.$field.'_DESCRIPTION#'] = $arFieldsDescription[$field]['DESCR'];
					$arLangReplace['#FIELD_DESCRIPTION#'] = $arFieldsDescription[$field]['DESCR'];
				}
				if( array_key_exists('DESCRIPTION', $arFieldsDescription[$field]) ) {
					$arLangReplace['#'.$field.'_DESCRIPTION#'] = $arFieldsDescription[$field]['DESCRIPTION'];
					$arLangReplace['#FIELD_DESCRIPTION#'] = $arFieldsDescription[$field]['DESCRIPTION'];
				}
			}
		}
		return $arLangReplace;
	}
//	public function getLangMessage($field = 'ALL', $bReplaceMacroses = false) {
//
//	}

	/**
	 * @param $arFields
	 * @return int | bool
	 */
	public function add($arFields) {
		global $DB;

		$bContinueAfterEvent = $this->_onStartAdd($arFields); if(!$bContinueAfterEvent) return 0;
		$mainTableAutoIncrement = $this->_mainTableAutoIncrement;
		$mainTablePrimaryKey = $this->_mainTablePrimaryKey;
		if( $mainTableAutoIncrement != null && isset($arFields[$mainTableAutoIncrement]) ) {
			unset($arFields[$mainTableAutoIncrement]);
		}
		$arCheckResult = $this->prepareFieldsData(self::PREPARE_ADD, $arFields);
		if($arCheckResult['__BREAK']) return 0;

		$bContinueAfterEvent = $this->_onBeforeAdd($arFields, $arCheckResult); if(!$bContinueAfterEvent) return 0;

		$arLangMessages = $this->_arDBSimpleLangMessages;
		$arMissedFields = $this->checkRequiredFields($arFields, $arCheckResult);
		if( count($arMissedFields)>0 ) {
			$bBreakOnMissField = false;
			foreach($arMissedFields as $fieldName) {
				if(array_key_exists('REQ_FLD_'.$fieldName, $arLangMessages) ) {
					$arLangMessage = $arLangMessages['REQ_FLD_'.$fieldName];
					// Заменяем макросы имён полей в lang-сообщениях
					$arLangReplace = $this->_getLangMessageReplace($fieldName);
					if( count($arLangReplace)>0 ) {
						foreach($arLangReplace as $placeHolder => &$phValue) {
							$arLangMessages['TEXT'] = str_replace($placeHolder, $phValue, $arLangMessages['TEXT']);
						}
					}
					switch( $arLangMessage['TYPE'] ) {
						case 'E':
							$this->addError($arLangMessage['TEXT'], $arLangMessage['CODE']);
							$bBreakOnMissField = true;
							break;
						case 'W':
							$this->addWarning($arLangMessage['TEXT'], $arLangMessage['CODE']);
							break;
						case 'M':
							$this->addMessage($arLangMessage['TEXT'], $arLangMessage['CODE']);
							break;
					}
				}
				else {
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_ADD_MISS_FIELD', array(
						'#FIELD#' => $fieldName
					)), self::ERR_MISS_REQUIRED);
					$bBreakOnMissField = true;
				}
			}
			if($bBreakOnMissField) return 0;
		}
		
		// check for duplicate primary key (if primary key is not auto_increment field)
		if( $mainTablePrimaryKey != null && $mainTablePrimaryKey != $mainTableAutoIncrement ) {
			$arItemByPrimaryKey = $this->getByID($arFields[$mainTablePrimaryKey]);
			if( count($arItemByPrimaryKey)>0 ) {
				if(array_key_exists('DUP_PK', $arLangMessages) ) {
					$arLangReplace = $this->_getLangMessageReplace($mainTablePrimaryKey);
					if( count($arLangReplace)>0 ) {
						foreach($arLangReplace as $placeHolder => &$phValue) {
							$arLangMessages['DUP_PK']['TEXT'] = str_replace($placeHolder, $phValue, $arLangMessages['DUP_PK']['TEXT']);
						}
					}
					$this->addError($arLangMessages['DUP_PK']['TEXT'], $arLangMessages['DUP_PK']['CODE']);
				}
				else {
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_ADD_DUP_PK', array(
						'#PK_NAME#' => $mainTablePrimaryKey,
						'#PK_VALUE#' => $arFields[$mainTablePrimaryKey]
					)), self::ERR_DUP_PK);
				}
				return 0;
			}
		}
		
		$arTableUnique = $this->_arTableUnique;
		// check for duplicate unique index
		if( count($arTableUnique)>0 ) {
			foreach( $arTableUnique as $udxName => $arUniqueFields ) {
				$arUniqueFilter = array();
				$arInUniqueMacrosNames = array();
				$arInUniqueMacrosValues = array();
				$strUniqueFieldsList = '';
				$strUniqueFieldsValues = '';
				$bFirstUniqueField = true;
				foreach($arUniqueFields as $inUniqueFieldName) {
					$arUniqueFilter[$inUniqueFieldName] = $arFields[$inUniqueFieldName];
					$arInUniqueMacrosNames[] = '#'.$inUniqueFieldName.'#';
					$arInUniqueMacrosValues[] = $arFields[$inUniqueFieldName];
					$strUniqueFieldsList .= (($bFirstUniqueField)?"'":"', '").$inUniqueFieldName;
					$strUniqueFieldsValues .= (($bFirstUniqueField)?"'":"', '").$arFields[$inUniqueFieldName];
					$bFirstUniqueField = false;
				}
				if(!$bFirstUniqueField) {
					$strUniqueFieldsList .= "'";
					$strUniqueFieldsValues .= "'";
				}
				if( count($arUniqueFilter)>0 ) {
					$arExistsList = $this->getListArray(null, $arUniqueFilter, null, null, null, false);
					if( count($arExistsList)>0 ) {
						if(array_key_exists('DUP_ADD_'.$udxName, $arLangMessages) ) {
							$this->addError(
								str_replace(
									$arInUniqueMacrosNames,
									$arInUniqueMacrosValues,
									$arLangMessages['DUP_ADD_'.$udxName]['TEXT']
								),
								$arLangMessages['DUP_ADD_'.$udxName]['CODE']
							);
						}
						else {
							$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_ADD_DUP_UNIQUE', array(
								'#FLD_LIST#' => $strUniqueFieldsList,
								'#FLD_VALUES#' => $strUniqueFieldsValues
							)), self::ERR_DUP_UNIQUE);
						}
						return 0;
					}
				}
			}
		}
		$arTableList = $this->_arTableList;
		$mainEntityTableName  = $arTableList[$this->_mainTable];
		$arInsert = $DB->PrepareInsert($mainEntityTableName, $arFields);
		$sqlInsert = 'INSERT INTO '.$mainEntityTableName.' ('.$arInsert[0].') VALUES ('.$arInsert[1].');';
		$DB->Query($sqlInsert, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);

		$bContinueAfterEvent = $this->_onAfterAdd($arFields); if(!$bContinueAfterEvent) return 0;

		if($mainTablePrimaryKey !== null) {
			if($mainTablePrimaryKey == $mainTableAutoIncrement ) {
				$arFields[$mainTablePrimaryKey] = $DB->LastID();
			}
			return $arFields[$mainTablePrimaryKey];
		}
		return true;
	}

	protected function _onStartUpdate(&$arFields) { return true; }
	protected function _onBeforeUpdate(&$arFields, &$arCheckResult) { return true; }
	protected function _onBeforeExecUpdate(&$arFields, &$arCheckResult) { return true; }
	protected function _onAfterUpdate(&$arFields) { return true; }
	public function update($arFields, $bNotUpdateUniqueFields = false) {
		global $DB;

		$bContinueAfterEvent = $this->_onStartUpdate($arFields); if(!$bContinueAfterEvent) return false;
		$arCheckResult = $this->prepareFieldsData(self::PREPARE_UPDATE, $arFields);
		if($arCheckResult['__BREAK']) return false;
		$bContinueAfterEvent = $this->_onBeforeUpdate($arFields, $arCheckResult); if(!$bContinueAfterEvent) return false;

		$mainTablePrimaryKey = $this->_mainTablePrimaryKey;
		$arTableList = $this->_arTableList;
		$mainEntityTableName  = $arTableList[$this->_mainTable];
		$ID = null;
		if( isset($arFields[$mainTablePrimaryKey]) ) {
			$ID = $arFields[$mainTablePrimaryKey];
			unset($arFields[$mainTablePrimaryKey]);
		}
		if( count($arFields)<1 ) {
			return true;
		}
		$arLangMessages = $this->_arDBSimpleLangMessages;
		// если PK не задан, то можно проверить является ли набор заданный в arFields unique индексом
		// и по нему найти значение PK
		$arThatElement = null;
		if( !$ID && is_array($this->_arTableUnique) && count($this->_arTableUnique)>0 ) {
			foreach($this->_arTableUnique as $arUnique) {
				$bAllNeededUniqueFldsExists = true;
				$arUniqueFilter = array();
				foreach($arUnique as $inUniqFldName) {
					if(!array_key_exists($inUniqFldName, $arFields) ) {
						$bAllNeededUniqueFldsExists = false;
						break;
					}
					$arUniqueFilter[$inUniqFldName] = $arFields[$inUniqFldName];
				}
				if($bAllNeededUniqueFldsExists) {
					$arExistsRowList = $this->getListArray(null, $arUniqueFilter, null, null, array($mainTablePrimaryKey), false);
					if( count($arExistsRowList)==1 && isset($arExistsRowList[0])) {
						if( !empty($arExistsRowList[0]) && isset($arExistsRowList[0][$mainTablePrimaryKey])) {
							// TODO: Оптимизировать кол-во запросов к БД
							//$arThatElement = $arExistsRowList[0];
							//$ID = $arThatElement[$mainTablePrimaryKey];
							$ID = $arExistsRowList[0][$mainTablePrimaryKey];
						}
					}
					
				}
				if($ID) {
					break;
				}
			}
		}
		if(!$ID) {
			if( array_key_exists('NOTHING_TO_UPDATE', $arLangMessages) ) {
				$this->addError($arLangMessages['NOTHING_TO_UPDATE']['TEXT'], $arLangMessages['NOTHING_TO_UPDATE']['CODE']);
			}
			else {
				$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_UPD_NOTHING_TO_UPDATE'), self::ERR_NOTHING_TU_UPDATE);
			}
			return false;
		}
		else {
			//if($arThatElement === null) {
				$arThatElement = $this->getByID($ID);
			//}
			if( empty($arThatElement) ) {
				if( array_key_exists('NOTHING_TO_UPDATE', $arLangMessages) ) {
					$this->addError($arLangMessages['NOTHING_TO_UPDATE']['TEXT'], $arLangMessages['NOTHING_TO_UPDATE']['CODE']);
				}
				else {
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_UPD_NOTHING_TO_UPDATE'), self::ERR_NOTHING_TU_UPDATE);
				}
				return false;
			}
			$arCheckResult['__EXIST_ROW'] = $arThatElement;
		}

		$arElementInFuture = array_merge($arThatElement, $arFields);
		$arTableUnique = $this->_arTableUnique;
		// check for duplicate unique index
		if( count($arTableUnique)>0 ) {
			foreach( $arTableUnique as $udxName => $arUniqueFields ) {
				if($bNotUpdateUniqueFields) {
					foreach($arUniqueFields as $inUniqueFieldName) {
						if( array_key_exists($inUniqueFieldName, $arFields) ) {
							unset($arFields[$inUniqueFieldName]);
						}
					}
				}
				else {
					$arUniqueFilter = array();
					$arInUniqueMacrosNames = array();
					$arInUniqueMacrosValues = array();
					$strUniqueFieldsList = '';
					$strUniqueFieldsValues = '';
					$bFirstUniqueField = true;
					foreach($arUniqueFields as $inUniqueFieldName) {
						$arUniqueFilter[$inUniqueFieldName] = $arElementInFuture[$inUniqueFieldName];
						$arInUniqueMacrosNames[] = '#'.$inUniqueFieldName.'#';
						$arInUniqueMacrosValues[] = $arElementInFuture[$inUniqueFieldName];
						$strUniqueFieldsList .= (($bFirstUniqueField)?"'":"', '").$inUniqueFieldName;
						$strUniqueFieldsValues .= (($bFirstUniqueField)?"'":"', '").$arElementInFuture[$inUniqueFieldName];
						$bFirstUniqueField = false;
					}
					if(!$bFirstUniqueField) {
						$strUniqueFieldsList .= "'";
						$strUniqueFieldsValues .= "'";
					}
					if( count($arUniqueFilter)>0 ) {
						$arUniqueFilter['!'.$mainTablePrimaryKey] = $arThatElement[$mainTablePrimaryKey];
						$arExistsList = $this->getListArray(null, $arUniqueFilter);
						//$arExistsList = $this->getListArray(null, $arUniqueFilter, null, null, null, false);
						if( count($arExistsList)>0 ) {
							if(array_key_exists('DUP_UPD_'.$udxName, $arLangMessages) ) {
								$this->addError(
									str_replace(
										$arInUniqueMacrosNames,
										$arInUniqueMacrosValues,
										$arLangMessages['DUP_UPD_'.$udxName]['TEXT']
									),
									$arLangMessages['DUP_UPD_'.$udxName]['CODE']
								);
							}
							else {
								$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_UPD_DUP_UNIQUE', array(
									'#FLD_LIST#' => $strUniqueFieldsList,
									'#FLD_VALUES#' => $strUniqueFieldsValues
								)), self::ERR_DUP_UNIQUE);
							}
							return false;
						}
					}
				}
			}
		}
		$bContinueAfterEvent = $this->_onBeforeExecUpdate($arFields, $arCheckResult); if(!$bContinueAfterEvent) return false;
		$strUpdate = $DB->PrepareUpdate($mainEntityTableName, $arFields);
		$strUpdate = 'UPDATE `'
						.$mainEntityTableName
						.'` SET '.$strUpdate
						.' WHERE `'
							.$mainTablePrimaryKey.'` = \''.$DB->ForSql($arThatElement[$mainTablePrimaryKey]).'\';';
		$DB->Query($strUpdate, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		$bContinueAfterEvent = $this->_onAfterUpdate($arFields); if(!$bContinueAfterEvent) return false;
		return true;
	}

	protected function _onStartDelete(&$PRIMARY_KEY_VALUE) { return true; }
	protected function _onBeforeDelete(&$arItem) { return true; }
	protected function _onAfterDelete(&$arItem) { return true; }
	public function delete($PRIMARY_KEY_VALUE) {
		global $DB;

		$arTableList = $this->_arTableList;
		$arTableFields = $this->_arTableFields;
		$mainTableAlias = $this->_mainTable;
		$mainTableAutoIncrement = $this->_mainTableAutoIncrement;
		$mainTablePrimaryKey = $this->_mainTablePrimaryKey;
		$arLangMessages = $this->_arDBSimpleLangMessages;
		if($mainTablePrimaryKey == null) {
			$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_CANT_DEL_WITHOUT_PK', array(
				'#TABLE#' => $arTableList[$mainTableAlias]
			)), self::ERR_CANT_DEL_WITHOUT_PK);
			return false;
		}
		$bContinueAfterEvent = $this->_onStartDelete($PRIMARY_KEY_VALUE); if(!$bContinueAfterEvent) return false;
		if( $mainTableAutoIncrement == $mainTablePrimaryKey ) {
			$PRIMARY_KEY_VALUE = intval($PRIMARY_KEY_VALUE);
		}
		$arIDField = $arTableFields[$mainTablePrimaryKey];
		list($tableAS, $tblFieldName) = each($arIDField);
		$tableName = $arTableList[$tableAS];

		if(!$PRIMARY_KEY_VALUE) {
			if( array_key_exists('NOTHING_TO_DELETE', $arLangMessages) ) {
				$this->addError($arLangMessages['NOTHING_TO_DELETE']['TEXT'], $arLangMessages['NOTHING_TO_DELETE']['CODE']);
			}
			else {
				$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::ERR_NOTHING_TO_DELETE);
			}
			return false;
		}
		else {
			$arExists = $this->getByID($PRIMARY_KEY_VALUE);
			if( empty($arExists) ) {
				if( array_key_exists('NOTHING_TO_DELETE', $arLangMessages) ) {
					$this->addError($arLangMessages['NOTHING_TO_DELETE']['TEXT'], $arLangMessages['NOTHING_TO_DELETE']['CODE']);
				}
				else {
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::ERR_NOTHING_TO_DELETE);
				}
				return false;
			}
		}
		$bContinueAfterEvent = $this->_onBeforeDelete($arExists); if(!$bContinueAfterEvent) return false;
		$sqlDelete = 'DELETE FROM '.$tableName.' WHERE '.$tblFieldName.' = \''.$PRIMARY_KEY_VALUE.'\';';
		$DB->Query($sqlDelete, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		$bContinueAfterEvent = $this->_onAfterDelete($arExists); if(!$bContinueAfterEvent) return false;
		return true;
	}

	protected function _deleteByFilterPrepare(&$arFilter) {
		//TODO: implement prepareFields!
		global $DB;
		$arTableList = $this->_arTableList;
		$arTableFields = $this->_arTableFields;
		$arTableLinks = $this->_arTableLinks;

		$mainTableNameAlias = $this->_mainTable;
		$mainTableName  = $arTableList[$mainTableNameAlias];


		$arMainTableLinkStrings = array();
		foreach($arTableLinks as $arTableLink) {
			$arLeftField = $arTableLink[0];
			$arRightField = $arTableLink[1];
			list($leftTblAlias, $leftTblField) = each($arLeftField);
			list($rightTblAlias, $rightTblField) = each($arRightField);
			if($leftTblAlias == $mainTableNameAlias) {
				$arMainTableLinkStrings[$rightTblAlias.'.'.$rightTblField] = $leftTblField;
			}
			if($rightTblAlias == $mainTableNameAlias) {
				$arMainTableLinkStrings[$leftTblAlias.'.'.$leftTblField] = $rightTblField;
			}
		}

		$sWhereFilter = '';
		if( is_array($arFilter) && !empty($arFilter) ) {
			$bFirst = true;
			foreach( $arFilter as $fieldCode => $filterFieldValue) {
				$EQ = '=';
				$arrayFieldValueCond = 'OR';
				$fieldCodeCond1 = substr($fieldCode, 0, 1);
				$fieldCodeCond2 = substr($fieldCode, 0, 2);
				if( $fieldCodeCond1 == '!' ) {
					$fieldCode = substr($fieldCode, 1);
					$EQ = '<>';
					$arrayFieldValueCond = 'AND';
				}
				elseif( $fieldCodeCond1 == '<') {
					if($fieldCodeCond2 == '<=') {
						$fieldCode = substr($fieldCode, 2);
						$EQ = '<=';
					}
					else {
						$fieldCode = substr($fieldCode, 1);
						$EQ = '<';
					}
				}
				elseif( $fieldCodeCond1 == '>') {
					if($fieldCodeCond2 == '>=') {
						$fieldCode = substr($fieldCode, 2);
						$EQ = '>=';
					}
					else {
						$fieldCode = substr($fieldCode, 1);
						$EQ = '>';
					}
				}
				if(array_key_exists($fieldCode, $arTableFields)) {
					$arTblField = $arTableFields[$fieldCode];
					list($asName, $tblFieldName) = each($arTblField);
					if( $asName != $mainTableNameAlias ) {
						if( array_key_exists($asName.'.'.$tblFieldName, $arMainTableLinkStrings) ) {
							$tblFieldName = $arMainTableLinkStrings[$asName.'.'.$tblFieldName];
						}
						else {
							$tblFieldName = '';
						}
					}
					if( strlen($tblFieldName)>0 ) {
						if( !is_array($filterFieldValue) ) {
							$filterFieldValue = $DB->ForSql($filterFieldValue);
							$sWhereFilter .= "\n\t".(($bFirst)?'':'AND ').$tblFieldName.' '.$EQ.' \''.$filterFieldValue.'\'';
							$bFirst = false;
						}
						elseif( count($filterFieldValue)>0 ) {
							$sWhereFilter .= "\n\t".(($bFirst)?'':'AND ').'(';
							$bFirstFilterFieldPart = true;
							foreach($filterFieldValue as &$filterFieldValuePart) {
								$filterFieldValuePart = $DB->ForSql($filterFieldValuePart);
								$sWhereFilter .= "\n\t"
									.($bFirstFilterFieldPart ? "\t\t" : "\t".$arrayFieldValueCond.' ')
									.$tblFieldName.' '.$EQ.' \''.$filterFieldValuePart.'\''
								;
								$bFirstFilterFieldPart = false;
							}
							$sWhereFilter .= "\n\t)";
							$bFirst = false;
						}
					}
				}
			}
		}
		if( strlen($sWhereFilter)>0 ) {
			return array(
				'SQL_DELETE' => 'DELETE FROM '.$mainTableName.' WHERE'.$sWhereFilter,
				'TABLE_NAME' => $mainTableName,
				'WHERE_STRING' => $sWhereFilter
			);
		}
		return null;
	}

	protected function _onStartDeleteByFilter(&$arFilter, &$bCheckExistence) { return true; }
	protected function _onBeforeDeleteByFilter(&$arFilter, &$bCheckExistence, &$arDelete) { return true; }
	protected function _onAfterDeleteByFilter(&$arFilter, &$bCheckExistence) { return true; }
	public function deleteByFilter($arFilter, $bCheckExistence = true) {
		global $DB;

		$bContinueAfterEvent = $this->_onStartDeleteByFilter($arFilter, $bCheckExistence);
		if(!$bContinueAfterEvent) return false;
		$arDelete = $this->_deleteByFilterPrepare($arFilter);
		$arLangMessages = $this->_arDBSimpleLangMessages;
		if( empty($arDelete) || !is_array($arDelete) ) {
			if( array_key_exists('NOTHING_TO_DELETE', $arLangMessages) ) {
				$this->addError($arLangMessages['NOTHING_TO_DELETE']['TEXT'], $arLangMessages['NOTHING_TO_DELETE']['CODE']);
			}
			else {
				$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::ERR_NOTHING_TO_DELETE);
			}
			return false;
		}
		// check existence
		if($bCheckExistence) {
			$sqlExistence = 'SELECT * FROM '.$arDelete['TABLE_NAME'].' WHERE'.$arDelete['WHERE_STRING'];
			$rsExists = $DB->Query($sqlExistence);
			if(!$rsExists->Fetch()) {
				if( array_key_exists('NOTHING_TO_DELETE', $arLangMessages) ) {
					$this->addError($arLangMessages['NOTHING_TO_DELETE']['TEXT'], $arLangMessages['NOTHING_TO_DELETE']['CODE']);
				}
				else {
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::ERR_NOTHING_TO_DELETE);
				}
				return false;
			}
		}
		$bContinueAfterEvent = $this->_onBeforeDeleteByFilter($arFilter, $bCheckExistence, $arDelete);
		if(!$bContinueAfterEvent) return false;
		$sqlDelete = 'DELETE FROM '.$arDelete['TABLE_NAME'].' WHERE'.$arDelete['WHERE_STRING'];
		$DB->Query($sqlDelete, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		$bContinueAfterEvent = $this->_onAfterDeleteByFilter($arFilter, $bCheckExistence);
		if(!$bContinueAfterEvent) return false;
		return true;
	}

	public function getFieldNames($arSelect = null) {
		$arResult = array();
		$arFields = $this->_arTableFields;
		$arDefaults = $this->_arTableFieldsDefault;
		if(!is_array($arSelect)){
			$arSelect = array_keys($arFields);
		}
		foreach ($arFields as $key => $val) {
			if (!in_array($key,$arSelect))
				continue;

			if (isset($arDefaults[$key]) && strlen($arDefaults[$key]) > 0) {
				$resDefault = $arDefaults[$key];
			} else {
				$resDefault = '';
			}
			$arResult[$key] = $resDefault;
		}
		return $arResult;
	}

	public function getEditFields(){
		$arEditFields = $this->_arFieldsEditInAdmin;
		if (!is_array($arEditFields) || empty($arEditFields))
			$arEditFields = array_keys($this->_arTableFields);
		return $arEditFields;
	}

	public function getFieldsDescription() {
		$arResult = array();
		foreach($this->_arTableFields as $fieldCode => &$arTblFieldName) {
			if(
				isset($this->_arFieldsDescription[$fieldCode])
				&& isset($this->_arFieldsDescription[$fieldCode]['NAME'])
			)
			$arResult[$fieldCode] = $this->_arFieldsDescription[$fieldCode];
		}
		return $arResult[$fieldCode];
	}
}
?>