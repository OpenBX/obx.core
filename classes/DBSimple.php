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

interface IDBSimple
{
	//static function getInstance();
	function add($arFields);
	function update($arFields, $bNotUpdateUniqueFields = false);
	function delete($PRIMARY_KEY_VALUE);
	function deleteByFilter($arFields);
	function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnCDBResult = false);
	function getLastQueryString();
}

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

abstract class DBSimpleStatic extends CMessagePoolStatic implements IDBSimpleStatic {
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
	 * @throws Exception
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
		throw new Exception("Static Class $className not initialized. May be in static decorator class used non static method. See Call-Stack");
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
}

abstract class DBSimple extends CMessagePoolDecorator
{
	protected function __construct() {}
	final protected function __clone() {}

	static protected $_arDBSimple = array();

	/**
	 * @final
	 * @static
	 * @return DBSimple
	 */
	final static public function getInstance() {
		$className = get_called_class();
		if( !isset(self::$_arDBSimple[$className]) ) {
			self::$_arDBSimple[$className] = new $className;
		}
		return self::$_arDBSimple[$className];
	}


	/*
	 * FIELD TYPES Типы полей полей для массива $_arTableFieldsCheck
	 */
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
	const FLD_T_USER_ID = 8192;			// ID пользвоателя битрикс
	const FLD_T_GROUP_ID = 16384;			// ID группы пользователей битрикс

	/*
	 * FIELD ATTR
	 * Ниже идут контсантны-аттрибуты
	 */

	/**
	 * Баззнаковое значение
	 * Применяется в сочетании с FLD_T_INT / FLD_T_FLOAT / FLD_T_BCHAR
	 * для FLD_T_INT FLD_T_FLOAT - просто проверка не наеотрицательность
	 * FLD_T_BCHAR - в этом случае пройдет только 'Y' все что не равно 'Y' будет отброшено
	 * @const
	 */
	const FLD_UNSIGNED = 32768;

	const FLD_NOT_ZERO = 65536;			// Не нуль для int и float и не пустая длина для string
	const FLD_NOT_NULL = 131072;		// Не NULL - именно NULL как тип данных СУБД
	const FLD_DEFAULT = 262144;			// задать значение по дефолту если нуль - зн-я по умолч. в массиве $this->_arTableFieldsDefault
	const FLD_REQUIRED = 524288;		// значение поля является обязательным при добавлении новой строки
	const FLD_CUSTOM_CK = 1048576;		// своя ф-ия проверки значения
	const FLD_UNSET = 2097152;			// выкинуть значение из arFields!

	/**
	 * Комплексный тип сочетающий тип int c аттрибутами типичнвми для первичного ключа ID
	 * self::FLD_T_INT
	 * | self::FLD_NOT_NULL
	 * | self::FLD_NOT_ZERO
	 * | self::FLD_UNSIGNED,
	 *
	 * 2 + 32768 + 65536 + 131072
	 * @const
	 */
	const FLD_T_PK_ID = 229378;


	/**
	 * Выполнение ф-ии self::add() / self::update() будет прервано
	 * если значение не корретно без занесения кода и текста ошибки в пул ошибок
	 * удобно применять в сочетании с self::FLD_CUSTOM_CK
	 * при этом сообщение об ошибке должен добавить программист в методе __check_FIELD_NAME()
	 * @const
	 */
	const FLD_BRK_INCORR = 4194304;		// прервать выполнение ф-ии, если значение неверно

	const FLD_ATTR_ALL = 8355840;		// все FIELD ATTRs вместе


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

	/**
	 * @var bool
	 */
	protected $_bDistinctGetList = false;

	/**
	 * Массив с описанием таблиц сущности
	 * В качестве ключа используется alias таблица (long_table_name as ARKEY)
	 * <code>
	 * 	<?php
	 * 		$this->_arTableList = array(
	 * 			'O' => 'obx_orders',
	 * 			'S' => 'obx_order_status',
	 * 			'I' => 'obx_basket_items',
	 * 			'U' => 'b_user'
	 * 		);
	 * 	?>
	 * </code>
	 * @var array
	 * @access protected
	 */
	protected $_arTableList = array();
	final public function getTableList() {
		return $this->_arTableList;
	}

	/**
	 * Массив с описанием полей сущнсти
	 * Данные поля будут использоваться а аргументе метода DBSimple::getList() в качестве $arSelect
	 * Имена не обязательно совпадает с именами полей таблиц
	 * Как видно из примера в каждм ключе содержится массив описывающий поле сущности
	 * Массив поля сущности содержит вложенный массив ключем которого является ALIAS таблицы,
	 * 	а значением - имя поля в соответствующей таблице.
	 * 	Так же возможны подзапросы на примере поля USER_NAME,
	 * 	Так же возможны сложные подзапросы. Пример можно посмотреть в модуле obx.market в классе OBX\OrdersList
	 * <code>
	 * 	<?php
	 * 		$this->_arTableFields = array(
	 * 			'ID' => array('O' => 'ID'),
	 * 			'DATE_CREATED' => array('O' => 'DATE_CREATED'),
	 * 			'TIMESTAMP_X' => array('O' => 'TIMESTAMP_X'),
	 * 			'USER_ID' => array('O' => 'USER_ID'),
	 * 			'USER_NAME' => array('U' => 'CONCAT(U.LAST_NAME," ",U.NAME)'),
	 * 			'STATUS_ID' => array('O' => 'STATUS_ID'),
	 * 			'STATUS_CODE' => array('S' => 'CODE'),
	 * 			'STATUS_NAME' => array('S' => 'NAME'),
	 * 		);
	 * 	?>
	 * </code>
	 *
	 * @var array
	 * @access protected
	 * @example bitrix/modules/obx.market/classes/OrdersList.php
	 */
	protected $_arTableFields = array();
	final public function getTableFields() {
		return $this->_arTableFields;
	}

	/**
	 * Языкозависимое описание полей заданных в $this->_arTableFields
	 * <code>
	 * 	<?php
	 * 		$this->_arFieldsDescription = array(
	 * 			'ID' => array(
	 * 				"NAME" => GetMessage("OBX_ORDERLIST_ID_NAME"),
	 * 				"DESCR" => GetMessage("OBX_ORDERLIST_ID_DESCR"),
	 * 			),
	 * 			//...
	 * 		);
	 * ?>
	 * </code>
	 * @var array
	 * @access protected
	 */
	protected $_arFieldsDescription = array();
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

	/**
	 * Переменная содержит ALIAS основной таблицы сущности.
	 * Основная таблица сущности будет использована в методах:
	 * 	$this->add(), $this->update(), $this->delete()
	 * @var string
	 */
	protected $_mainTable = '';
	final public function getMainTable() {
		return $this->_mainTable;
	}

	/**
	 * Переменная содержит имя ПОЛЯ основной таблицы сущности,
	 * которое является первичным ключом
	 * @var string
	 * @access protected
	 */
	protected $_mainTablePrimaryKey = 'ID';
	final public function getMainTablePrimaryKey() {
		return $this->_mainTablePrimaryKey;
	}

	/**
	 * Переменная содержит имя ПОЛЯ основной таблицы сущности,
	 * которое является автоинкрементным
	 * @var string
	 * @access protected
	 */
	protected $_mainTableAutoIncrement = 'ID';
	final public function getMainTableAutoIncrement() {
		return $this->_mainTableAutoIncrement;
	}

	/**
	 * Массив содержащий связи полей таблиц
	 * Данные связи будут применяться для формирования условий в блоке WHERE.
	 * 	А так же в методе $this->getByID() возможна ситуация
	 * 	когда в arSelect указано поле имеющееся в основной таблице сущности, но явно указывает на связнуб таблица.
	 * 	В таких случаях метод $this->getByID() заглядывает в данный массив для того, что бы убедиться
	 * 	в том, что ссылка на данной поле имеется и поле межно применять сделав выборку из основной таблицы сущности.
	 * 	Примечание: Такое возникает когда применяются и JOIN-ы. В таких случаях надо заполнять
	 * 	и $this->_arTableLeftJoin и $this->_arTableLinks
	 *
	 * Даже если для реализации актуальны только JOIN, все равно зачастую важно звполнять массив связей
	 * @var array
	 * @access protected
	 */
	protected $_arTableLinks = array();
	final public function getTableLinks() {
		return $this->_arTableLinks;
	}

	/**
	 * Массив описывающий условия для LEFT JOIN
	 * <code>
	 * 	<?php
	 * 		$this->_arTableLeftJoin = array(
	 *
	 * 		);
	 * 	?>
	 * </code>
	 * @var array
	 * @access protected
	 */
	protected $_arTableLeftJoin = array();
	final public function getTableLeftJoin() {
		return $this->_arTableLeftJoin;
	}

	/**
	 *
	 * @var array
	 * @access protected
	 */
	protected $_arTableRightJoin = array();
	final public function getTableRightJoin() {
		return $this->_arTableRightJoin;
	}

	/**
	 * @var array
	 * @access protected
	 */
	protected $_arTableJoinNullFieldDefaults = array();
	final public function getTableJoinNullFieldDefaults() {
		return $this->_arTableJoinNullFieldDefaults;
	}

	/**
	 * Массив с опсанием индексов
	 * Пока не применяется
	 * @var array
	 * @access protected
	 */
	protected $_arTableIndex = array();
	final public function getTableIndex() {
		return $this->_arTableIndex;
	}

	/**
	 * Массив с описанием unique-индексов
	 * Заполнять обязательно.
	 * Методы $this->add() и $this->update() проверяют этот массив для предотвращения вставки дублей
	 * <code>
	 * 	<?php
	 * 		$_arTableUnique = array(
	 * 			'имя_уникального_индекса' => array('поле1', 'поле2')
	 * 		);
	 * 	?>
	 * </code>
	 * @var array
	 * @access protected
	 */
	protected $_arTableUnique = array();
	final public function getTableUnique(){
		return $this->_arTableUnique;
	}

	/**
	 * Значение указанных полей данного массива будут автоматически вставлены в arFilter метода GetList,
	 * если не будут указаны там явно.
	 * Важно понимать, что _arFilterDefault как правило заполняется в контрукторе
	 * и знаения этих будет актуальным в момент содания объекта DBSimple
	 * @var array
	 * @access protected
	 */
	protected $_arFilterDefault = array();
	final public function getFilterDefault() {
		return $this->_arFilterDefault;
	}

	/**
	 * arSelect по умолчанию
	 * Если в методе $this->getLis() не задан аргумент arSelect, то будет использован этот.
	 * Если в классе сущности не задан и этот массив,
	 * то в качестве arSelect будет принят полный список ключей массива $this->_arTableFields
	 * @var array
	 * @access protected
	 */
	protected $_arSelectDefault = array();
	final public function getSelectDefault() {
		return $this->_arSelectDefault;
	}

	/**
	 * Сорттировка по умолчанию
	 * Если в методе $this->getList() не указан аргумент arSort, то он будет наполнен из этого массива
	 * @var array
	 * @access protected
	 */
	protected $_arSortDefault = array('ID' => 'ASC');
	final public function getSortDefault() {
		return $this->_arSortDefault;
	}

	/**
	 * Типы и атрибуты полей основной таблицы сущности
	 * Используется для проверки входных данных в методах $this->add() и $this->update()
	 * Выше все контстанты используемые в этом массиве документированы
	 * Пример:
	 * <code>
	 * 	<?php
	 * 		$this->_arTableFieldsCheck = array(
	 * 			'ID' => self::FLD_T_INT | self::FLD_NOT_NULL,
	 * 			'DATE_CREATED' => self::FLD_T_NO_CHECK,
	 * 			'TIMESTAMP_X' => self::FLD_T_NO_CHECK,
	 * 			'USER_ID' => self::FLD_T_USER_ID | self::FLD_NOT_NULL | self::FLD_DEFAULT | self::FLD_REQUIRED,
	 * 			'STATUS_ID' => self::FLD_T_INT | self::FLD_NOT_NULL | self::FLD_DEFAULT | self::FLD_REQUIRED,
	 * 			'CURRENCY' => self::FLD_T_CODE | self::FLD_NOT_NULL | self::FLD_DEFAULT | self::FLD_REQUIRED,
	 * 			'DELIVERY_ID' => self::FLD_T_INT,
	 * 			'DELIVERY_COST' => self::FLD_T_FLOAT,
	 * 			'PAY_ID' => self::FLD_T_INT,
	 * 			'PAY_TAX_VALUE' => self::FLD_T_FLOAT,
	 * 			'DISCOUNT_ID' => self::FLD_T_INT,
	 * 			'DISCOUNT_VALUE' => self::FLD_T_FLOAT
	 * 		);
	 * 	?>
	 * </code>
	 * @var array
	 * @access protected
	 */
	protected $_arTableFieldsCheck = array();
	final public function getTableFieldsCheck() {
		return $this->_arTableFieldsCheck;
	}

	/**
	 * Языковые сообщения при выводе ошибок
	 * Из данного массива будут получены ошибки и предупреждения
	 * Ключевые события стандартизировны и закреплены за префиксами ключей массива
	 * REQ_FLD_ИМЯ_ПОЛЯ - описание события если в аргументе $arFields метода $this->add($arFields)
	 * 				не заполнено поле "ИМЯ_ПОЛЯ"
	 * DUP_ADD_ИМЯ_UNIQUE_ИНДЕКСА - описание события если в аргументе $arFields метода $this->add($arFields)
	 * 				заданы поля уникального индекса уже существующие для записи в таблице БД
	 * DUP_UPD_ИМЯ_UNIQUE_ИНДЕКСА - описание события если в аргументе $arFields метода $this->update($arFields)
	 * 				заданы поля уникального индекса уже существующие для записи в таблице БД
	 * NOTHING_TO_DELETE - описание события если в метод $this->delete() на нашел запись для удаления
	 * 				не заполнено поле "ИМЯ_ПОЛЯ"
	 * NOTHING_TO_UPDATE - описание события если в метод $this->update() на нашел запись для обновления
	 * 				не заполнено поле "ИМЯ_ПОЛЯ"
	 *
	 * Каждое описание содержит следующие ключи
	 * 		'TYPE' - может принимать значения
	 * 			Примечение: В зависимости от этого типа будет вызван соответствующий метод объекта CMessagePool
	 * 			'E' - Error - ошибка - CMessagePool::addError()
	 * 			'W' - Warning - предупреждение - CMessagePool::addError()
	 * 			'M' - MessageСообщение - CMessagePool::addMessage()
	 * 		'TEXT' - текст события
	 * 		'CODE' - код события
	 * 		Как правило применяется 'E'
	 * Пример:
	 * <code>
	 * 	<?php
	 * 		$this->_arDBSimpleLangMessages = array(
	 *			'REQ_FLD_ИМЯ_ПОЛЯ' =>  array(
	 * 				'TYPE' => 'E',
	 * 				'TEXT' => GetMessage('OBX_ORDER_STATUS_ERROR_1'),
	 * 				'CODE' => 1
	 * 			),
	 * 		);
	 * ?>
	 * </code>
	 * @var array
	 * @access protected
	 */
	protected $_arDBSimpleLangMessages = array();
	final public function getDBSimpleLangMessages(){
		return $this->_arDBSimpleLangMessages;
	}

	/**
	 * Массив сожержит значения по умолчанию для полей аргумента arFields метода $this->add()
	 * @var array
	 * @access protected
	 */
	protected $_arTableFieldsDefault = array();
	final public function getTableFieldsDefault(){
		return $this->_arTableFieldsDefault;
	}

	/**
	 * Группировка по умолчанию
	 * Пока не используется
	 * @var array
	 * @access protected
	 */
	protected $_arGroupByFields = array();
	final public function getGroupByFields() {
		return $this->_arGroupByFields;
	}

	/**
	 * @var null | string
	 * Модуль к которому принадлежит сущность
	 */
	protected $_entityModuleID = null;

	/**
	 * @var null | string
	 * Идентификатор используемый для создания событий приисходящих в сущности
	 * список генерируемых событий
	 * on<EventsID>StartAdd
	 * on<EventsID>BeforeAdd
	 * on<EventsID>AfterAdd
	 * ---
	 * on<EventsID>StartUpdate
	 * on<EventsID>BeforeUpdate
	 * on<EventsID>BeforeExecUpdate
	 * on<EventsID>AfterUpdate
	 * ---
	 * on<EventsID>StartDelete
	 * on<EventsID>BeforeDelete
	 * on<EventsID>AfterDelete
	 * ---
	 * on<EventsID>StartDeleteByFilter
	 * on<EventsID>BeforeDeleteByFilter
	 * on<EventsID>AfterDeleteByFilter
	 */
	protected $_entityEventsID = null;

	/**
	 * @var array
	 * Список событий сущности
	 */
	protected $_arEntityEvents = array();

	/**
	 * @var bool
	 * Признак того, что стандартные события сущности инициализированы
	 */
	protected $_bEntityEventsInit = false;

	/**
	 * Массив содержит имена полей таблицы сущности, которые доступны для редактрирования в административной панели
	 * @var array
	 * @access protected
	 */
	protected $_arFieldsEditInAdmin = array();

	protected $_lastQueryString = '';
	final public function getLastQueryString() {
		return $this->_lastQueryString;
	}

	/**
	 * Получить в сущности объект списка событий
	 * Для автоматической генерации стандартного списка собйтий
	 * данный метод должен быть выполнен в конструкторе сущности
	 */
	protected function _getEntityEvents() {
		if($this->_entityModuleID === null || $this->_entityEventsID === null) {
			return false;
		}
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartAdd'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityEventsID.'Add',
			true
		);
		$this->_arEntityEvents['onBeforeAdd'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityEventsID.'Add',
			true
		);
		$this->_arEntityEvents['onAfterAdd'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityEventsID.'Add',
			true
		);
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityEventsID.'Update',
			true
		);
		$this->_arEntityEvents['onBeforeUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityEventsID.'Update',
			true
		);
		$this->_arEntityEvents['onBeforeExecUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBeforeExec'.$this->_entityEventsID.'Update',
			true
		);
		$this->_arEntityEvents['onAfterUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityEventsID.'Update',
			true
		);
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartDelete'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityEventsID.'Delete',
			true
		);
		$this->_arEntityEvents['onBeforeDelete'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityEventsID.'Delete',
			true
		);
		$this->_arEntityEvents['onAfterDelete'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityEventsID.'Delete',
			true
		);
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartDeleteByFilter'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityEventsID.'DeleteByFilter',
			true
		);
		$this->_arEntityEvents['onBeforeDeleteByFilter'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityEventsID.'DeleteByFilter',
			true
		);
		$this->_arEntityEvents['onAfterDeleteByFilter'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityEventsID.'DeleteByFilter',
			true
		);
		$this->_bEntityEventsInit = true;
	}

	protected function __checkNumericField($bFloat = false, &$fieldValue, &$bUnsignedType, &$bNotNull, &$bNotZero) {
		$bValueIsCorrect = false;
		$bPassNull = !$bNotNull;
		//$bPassZero = !$bNotZero;
		if( $bPassNull && ($fieldValue===null || !is_numeric($fieldValue)) ) {
			$fieldValue = null;
			$bValueIsCorrect = true;
		}
		else {
			if($bFloat) {
				$fieldValue = floatval($fieldValue);
			}
			else {
				$fieldValue = intval($fieldValue);
			}
			if( $bUnsignedType ) {
				if($bNotZero) {
					if($fieldValue > 0) $bValueIsCorrect = true;
				}
				else {
					if($fieldValue >= 0) $bValueIsCorrect = true;
				}
			}
			else {
				if($bNotZero) {
					if($fieldValue != 0) $bValueIsCorrect = true;
				}
				else {
					$bValueIsCorrect = true;
				}
			}
		}
		return $bValueIsCorrect;
	}
	/**
	 * Метод подготовки данных
	 * Применяется в $this->add() и $this->update()
	 * Использует атрибуты полей из массива $this->_arTableFieldsCheck для проверки входных параметров метода
	 * @param int $prepareType - может принимать для зачения self::PREPARE_ADD или self::PREPARE_ADD
	 * @param array $arFields - значения полей основной таблицы сущности
	 * @param null|array $arTableFieldsCheck - если задан, то переопределяет штатный $this->_arTableFieldsCheck
	 * @param null|array $arTableFieldsDefault - если задан, то переопределяет штатный $this->_arTableFieldsDefault
	 * @return array
	 */
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
			'__BREAK' => false,
			'__MAGIC_WORD' => false,
		);
		if( array_key_exists(OBX_MAGIC_WORD, $arFields) ) {
			$arCheckResult['__MAGIC_WORD'] = true;
			unset($arFields[OBX_MAGIC_WORD]);
		}
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
					'IS_NULL' => false,
					'IS_CORRECT' => false,
					'FROM_DEFAULTS' => false,
					'CHECK_DATA' => array()
				);
				$fieldType = $arTableFieldsCheck[$fieldName];
				$bValueIsCorrect = false;
				$bNotNull = false;
				$bNotZero = false;
				$bDefaultIfNull = false;
				$bUnsignedType = false;
				if( $fieldType & self::FLD_UNSIGNED ) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_UNSIGNED'] = self::FLD_UNSIGNED;
					$bUnsignedType = true;
				}
				if( $fieldType & self::FLD_NOT_NULL) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_NOT_NULL'] = self::FLD_NOT_NULL;
					$bNotNull = true;
				}
				$bPassNull = !$bNotNull;
				if( $fieldType & self::FLD_NOT_ZERO ) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_NOT_ZERO'] = self::FLD_NOT_ZERO;
					$bNotZero = true;
				}
				$bPassZero = !$bNotZero;
				if( ($fieldType & self::FLD_DEFAULT) ) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_DEFAULT'] = self::FLD_DEFAULT;
					if( $prepareType == self::PREPARE_ADD ) {
						$bDefaultIfNull = true;
					}
				}
				if( $fieldType & self::FLD_REQUIRED ) {
					$arCheckResult[$fieldName]['FIELD_ATTR']['FLD_REQUIRED'] = self::FLD_REQUIRED;
				}
				$bValueIsEmpty = empty($fieldValue);
				if($bValueIsEmpty) {
					$arCheckResult[$fieldName]['IS_EMPTY'] = true;
				}
				if($fieldValue === null && $bPassNull) {
					$bValueIsCorrect = true;
					$bValueIsEmpty = true;
					$arCheckResult[$fieldName]['IS_NULL'] = true;
					$arCheckResult[$fieldName]['IS_EMPTY'] = true;
					$arCheckResult[$fieldName]['IS_CORRECT'] = true;
				}
				else {
					switch( ($fieldType & ~self::FLD_ATTR_ALL) ) {
						case self::FLD_T_NO_CHECK:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_NO_CHECK';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_NO_CHECK;
							$bValueIsCorrect = true;
							break;
						case self::FLD_T_CHAR:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_CHAR';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_CHAR;
							if( $bPassNull && $bValueIsEmpty ) {
								$fieldValue = null;
							}
							elseif( !$bValueIsEmpty ) {
								$fieldValue = substr($fieldValue, 0 ,1);
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_INT:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_INT';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_INT;
							$bValueIsCorrect = $this->__checkNumericField(false, $fieldValue, $bUnsignedType, $bNotNull, $bNotZero);
							break;
						case self::FLD_T_STRING:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_STRING';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_STRING;
							$valStrLen = strlen($fieldValue);
							if( $valStrLen>0 ) {
								$fieldValue = $DB->ForSql(htmlspecialcharsEx($fieldValue));
								$bValueIsCorrect = true;
							}
							elseif($bPassZero) {
								$fieldValue = '';
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_CODE:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_CODE';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_CODE;
							$fieldValue = trim($fieldValue);
							if( preg_match('~^[a-zA-Z\_][a-z0-9A-Z\_]{0,15}$~', $fieldValue) ) {
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_BCHAR:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_BCHAR';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_BCHAR;
							$fieldValue = strtoupper(substr($fieldValue, 0, 1));
							if( $fieldValue == 'Y' || ( !$bUnsignedType && $fieldValue == 'N') ) {
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_FLOAT:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_FLOAT';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_FLOAT;
							$bValueIsCorrect = $this->__checkNumericField(true, $fieldValue, $bUnsignedType, $bNotNull, $bNotZero);
							break;
						case self::FLD_T_IDENT:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IDENT';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IDENT;
							$fieldValue = trim($fieldValue);
							if(
								( is_numeric($fieldValue) && ($fieldValue = intval($fieldValue))>0 )
								||
								( preg_match('~^[a-z0-9A-Z\_]{1,255}$~', $fieldValue) )
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
							$rs = \CIBlock::GetByID($fieldValue);
							if( ($arData = $rs->GetNext()) ) {
								$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_IBLOCK_PROP_ID:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IBLOCK_PROP_ID';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IBLOCK_PROP_ID;
							$rs = \CIBlockProperty::GetByID($fieldValue);
							if( ($arData = $rs->GetNext()) ) {
								$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_IBLOCK_ELEMENT_ID:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IBLOCK_ELEMENT_ID';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IBLOCK_ELEMENT_ID;
							$rs = \CIBlockElement::GetByID($fieldValue);
							if( ($arData = $rs->GetNext()) ) {
								$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_IBLOCK_SECTION_ID:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_IBLOCK_SECTION_ID';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_IBLOCK_SECTION_ID;
							$rs = \CIBlockSection::GetByID($fieldValue);
							if( ($arData = $rs->GetNext()) ) {
								$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_USER_ID:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_USER_ID';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_USER_ID;
							$rs = \CUser::GetByID($fieldValue);
							if( ($arData = $rs->GetNext()) ) {
								$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_GROUP_ID:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_GROUP_ID';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_GROUP_ID;
							$rs = \CGroup::GetByID($fieldValue);
							if( ($arData = $rs->GetNext()) ) {
								$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
								$bValueIsCorrect = true;
							}
							break;
					}
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

				if($bValueIsEmpty && $bDefaultIfNull) {
					if(array_key_exists($fieldName, $arTableFieldsDefault)) {
						$arCheckResult[$fieldName]['FROM_DEFAULTS'] = true;
						$arFieldsPrepared[$fieldName] = $arTableFieldsDefault[$fieldName];
					}
				}
				elseif($bValueIsCorrect) {
					$arCheckResult[$fieldName]['IS_CORRECT'] = true;
					$arCheckResult[$fieldName]['VALUE'] = $fieldValue;
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
	 * @param array &$arFields ссылка - поля переданные в аргументе
	 * @param array &$arCheckResult
	 * @param array|null $arTableFieldsCheck
	 * @param array|null $arTableFieldsDefault  - значения полей по умолчанию, если поле потеряно, но есть дефолтное значение, будет подставлено оно
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

	/**
	 * @param $arFilter
	 * @param $arSelectFromTables
	 * @param $bLogicOrInsideSubFilter - фомирование строки фильтра для блока с логикой OR
	 * @param string $aws - additional white space - дополнительный отступ - для удобства отладки :)
	 * @return string
	 */
	private function _getWhereSQL(&$arFilter, &$arSelectFromTables, $bLogicOrInsideSubFilter = false, $aws = '') {
		global $DB;
		$arTableFields = $this->_arTableFields;
		$sWhereFilter = '';
		foreach( $arFilter as $fieldCode => $filterFieldValue) {
			if( $filterFieldValue == '__undefined__' || $filterFieldValue == '__skip__' ) {
				continue;
			}
			if(
				$fieldCode == 'OR' || substr($fieldCode, 0, 3) == 'OR_'
				|| $fieldCode == 'AND_OR' || substr($fieldCode, 0, 7) == 'AND_OR_'
			) {
				if(!is_array($filterFieldValue)) continue;
					foreach($filterFieldValue as &$arSubFilter) {
						if( !is_array($arSubFilter) ) continue;
						$sWhereFilter .= "\n\tAND ((1<>1)";
						$sWhereFilter .= $this->_getWhereSQL($arSubFilter, $arSelectFromTables, true, $aws."\t");
						$sWhereFilter .= "\n\t)";
					}
			}
			if( $fieldCode == 'OR_AND' || substr($fieldCode, 0, 7) == 'OR_AND_' ) {
				if(!is_array($filterFieldValue)) continue;
				foreach($filterFieldValue as &$arSubFilter) {
					if( !is_array($arSubFilter) ) continue;
					$sWhereFilter .= "\n\tOR ((1==1)";
					$sWhereFilter .= $this->_getWhereSQL($arSubFilter, $arSelectFromTables, false, $aws."\t");
					$sWhereFilter .= "\n\t)";
				}
			}
			else {
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
					$this->_checkRequiredTablesByField($arSelectFromTables, $arTableFields, $fieldCode);
					list($asName, $tblFieldName) = each($arTblField);
					$isSubQuery = (strpos($tblFieldName,'(')!==false);
					$sqlField = $asName.'.'.$tblFieldName;
					// Нельзя сделать фильтр по полю, которое является подзапросом
					if($isSubQuery) {
						// [pronix:2013-06-19]если конечно не указать явный подзапрос специально для фильтра :)
						if( !array_key_exists('GET_LIST_FILTER', $arTblField) ) {
							continue;
						}
						else {
							if($arTblField['GET_LIST_FILTER'] === true) {
								$sqlField = '('.$tblFieldName.')';
							}
							elseif( is_string($arTblField['GET_LIST_FILTER'])) {
								$sqlField = '('.$arTblField['GET_LIST_FILTER'].')';
							}
							else {
								continue;
							}
						}
					}
					else {
						$sqlField = $asName.'.'.$tblFieldName;
					}

					if( !is_array($filterFieldValue) ) {
						$bFieldValueNullCheck = false;
						if( $filterFieldValue === null || $filterFieldValue == '__null__' ) {
							$bFieldValueNullCheck = true;
							$strNot = ($EQ=='<>')?' NOT':'';
						}
						$filterFieldValue = $DB->ForSql($filterFieldValue);
						$sWhereFilter .= "\n\t".$aws.($bLogicOrInsideSubFilter?'OR':'AND').' ('
							.(
								($bFieldValueNullCheck)
								?($sqlField.' IS'.$strNot.' NULL')
								:($sqlField.' '.$EQ.' \''.$filterFieldValue.'\'')
							)
						.')';
					}
					elseif( count($filterFieldValue)>0 ) {
						$sWhereFilter .= "\n\t".$aws.($bLogicOrInsideSubFilter?'OR':'AND').' (';
						$bFirstFilterFieldPart = true;
						foreach($filterFieldValue as &$filterFieldValuePart) {
							$bFieldValueNullCheck = false;
							if( $filterFieldValuePart === null || $filterFieldValuePart == '__null__' ) {
								$bFieldValueNullCheck = true;
								$strNot = ($EQ=='<>')?' NOT':'';
							}
							$filterFieldValuePart = $DB->ForSql($filterFieldValuePart);
							$sWhereFilter .= "\n\t".$aws;
							$sWhereFilter .= ($bFirstFilterFieldPart?("\t\t".$aws):("\t".$aws.$arrayFieldValueCond.' '));
							$sWhereFilter .=(
								($bFieldValueNullCheck)
								?($sqlField.' IS'.$strNot.' NULL')
								:($sqlField.' '.$EQ.' \''.$filterFieldValuePart.'\'')
							);
							$bFirstFilterFieldPart = false;
						}
						$sWhereFilter .= "\n\t".$aws.')';
					}
					$arSelectFromTables[$asName] = true;
				}
			}

		}
		return $sWhereFilter;
	}

	protected function _checkRequiredTablesByField(&$arSelectFromTables, &$arTableFields, &$fieldCode) {
		$arTblField = $arTableFields[$fieldCode];
		if( array_key_exists('REQUIRED_TABLES', $arTblField) ) {
			if( is_array($arTblField['REQUIRED_TABLES']) ) {
				foreach($arTblField['REQUIRED_TABLES'] as &$requiredTableAlias) {
					$arSelectFromTables[$requiredTableAlias] = true;
				} unset($requiredTableAlias);
			}
			elseif( is_string($arTblField['REQUIRED_TABLES']) ) {
				$arSelectFromTables[$arTblField['REQUIRED_TABLES']] = true;
			}
		}
	}

	/**
	 * Возвращает список записей сущности
	 * @param null | array $arSort - поля и порядок сортировки
	 * @param null | array $arFilter - фильтр полей
	 * @param null | array $arGroupBy - грпиировать по полям
	 * @param null | array $arPagination - массив для формирования постраничной навигации
	 * @param null | array $arSelect - выбираемые поля
	 * @param bool $bShowNullFields - показыввать NULL значения - т.е. разрешить ли применение JOIN
	 * @return bool | DBSResult
	 */
	public function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true) {
		global $DB;

		$arTableList = $this->_arTableList;
		$arTableLinks = $this->_arTableLinks;
		$arTableFields = $this->_arTableFields;
		$arTableLeftJoin = $this->_arTableLeftJoin;
		$arTableRightJoin = $this->_arTableRightJoin;

		$bUsePagination = is_array($arPagination);

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
				$this->_checkRequiredTablesByField($arSelectFromTables, $arTableFields, $fieldCode);
				list($tblAlias, $tblFieldName) = each($arTblField);
				$isSubQuery = (strpos($tblFieldName,'(')!==false);
				if(!$isSubQuery){
					$sqlField = $tblAlias.'.'.$tblFieldName;
				}
				else{
					$sqlField = $tblFieldName;
				}

				$sFields .= (($bFirst)?"\n\t":", \n\t").$sqlField.' AS '.$fieldCode;
				$bFirst = false;
				$arSelectFromTables[$tblAlias] = true;
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
			$sWhereFilter = $this->_getWhereSQL($arFilter, $arSelectFromTables);
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
					$this->_checkRequiredTablesByField($arSelectFromTables, $arTableFields, $fieldCode);
					list($tblAlias, $tblFieldName) = each($arTblField);
					$isSubQuery = (strpos($tblFieldName,'(')!==false);
					if (!$isSubQuery){
						$sqlField = $tblAlias.'.'.$tblFieldName;
					}else{
						$sqlField = $fieldCode;
					}
					$sSort .= (($bFirst)?"\nORDER BY \n\t":", \n\t").$sqlField.' '.$orAscDesc;
					$bFirst = false;
					$arSelectFromTables[$tblAlias] = true;
				}
			}
		}

		// Группируем
		$arGroupByFields = $this->_arGroupByFields;
		if( is_array($arGroupBy) && count($arGroupBy) > 0 ) {
			foreach ($arGroupBy as $fieldCode){
				if( isset($arTableFields[$fieldCode]) ) {
					$arTblField = $arTableFields[$fieldCode];
					list($tblAlias, $tblFieldName) = each($arTblField);
					if( !array_key_exists($tblAlias, $arGroupByFields) ) {
						$arGroupByFields[$tblAlias] = $tblFieldName;
					}
				}
			}
		}
		$sGroupBy = '';
		$arSqlGroupedByField = array();
		foreach($arGroupByFields as $tblAlias => $tblFieldName) {
			$arSqlGroupedByField[] = $tblAlias.'.'.$tblFieldName;
		}
		if( count($arSqlGroupedByField) > 0 ) {
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
		$arTableRightJoinTables = $arTableRightJoin;
		foreach($arTableRightJoinTables as $sdTblName => &$bJoinThisTable) {
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
				elseif( $bShowNullFields && array_key_exists($asTblName, $arTableRightJoinTables) ) {
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
		$sWhere = $sWhereTblLink.$sWhereFilter;
		if( !empty($sSelectFrom) && !empty($sWhere) ) {
			$sWhere = "\nWHERE (1=1)".$sWhereTblLink.$sWhereFilter;
		}

		$sqlList = $sFields."\nFROM ".$sSelectFrom.$sJoin.$sWhere.$sGroupBy.$sSort;

		$strDistinct = $this->_bDistinctGetList?'DISTINCT ':'';
		if($bUsePagination && $this->_mainTablePrimaryKey !== null) {
			$sqlList = 'SELECT '.$strDistinct.$sqlList;
			$sqlCount = 'SELECT COUNT(*) as C '
						.'FROM ('.$sqlList.') as SELECTION';
			$res_cnt = $DB->Query($sqlCount);
			$res_cnt = $res_cnt->Fetch();
			$res = new DBSResult();

			$res->NavQuery($sqlList, $res_cnt["C"], $arPagination);
		}
		else {
			$sqlList = 'SELECT '.$strDistinct.$sqlList;
			$res = $DB->Query($sqlList, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
			$res = new DBSResult($res);
		}
		$this->_lastQueryString = $sqlList;
		//$res = $DB->Query($sqlList, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);

		$res->setAbstractionName(get_called_class());
		return $res;
	}

	/**
	 * То же что и $this->getList() только возвращает не CDBResult, а array
	 * @param null | array $arSort
	 * @param null | array $arFilter
	 * @param null | array $arGroupBy
	 * @param null | array $arPagination
	 * @param null | array  $arSelect
	 * @param bool $bShowNullFields
	 * @return array
	 */
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

	/**
	 * Метод позволяет получить только поля из основной таблицы сущности
	 * или в крайнем случае поля из других таблиц,
	 * но только в том случае если они прописаны в массиве $this->_arTableLinks
	 * Поля-подзапросы в $arSelect так же будут проигнорированы
	 * @param string |int | float $PRIMARY_KEY_VALUE
	 * @param array | null $arSelect
	 * @param bool $bReturnCDBResult
	 * @return array | DBSResult
	 */
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
				// TODO: это может сломаться в любой момент. Разобраться Очень спорный момент. Нужно аккуратно проектировать подзапросы
				$isSubQuery = ((strpos($tblFieldName,'(')===false)?false:true);
				if($isSubQuery){
					continue;
				}
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
		$this->_lastQueryString = $sqlByPrimaryKey;
		$rsList = $DB->Query($sqlByPrimaryKey, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		if(!$bReturnCDBResult) {
			if( ($arElement = $rsList->Fetch()) ) {
				return $arElement;
			}
			return array();
		}
		$rsList = new DBSResult($rsList);
		$rsList->setAbstractionName(get_called_class());
		return $rsList;
	}

	static protected function _executeModuleEvents(&$arEventList, $arParams) {
		$bSuccess = true;
		foreach($arEventList as &$arEvent) {
			$bSuccess = (ExecuteModuleEventEx($arEvent, $arParams)!==false) && $bSuccess;
		}
		return $bSuccess;
	}
	
	protected function _onStartAdd(&$arFields) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents($this->_arEntityEvents['onStartAdd'], array(&$arFields, &$this->MessagePool));
		}
		return $bSuccess;
	}
	protected function _onBeforeAdd(&$arFields, &$arCheckResult) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents(
				$this->_arEntityEvents['onBeforeAdd'],
				array(&$arFields, &$arCheckResult, &$this->MessagePool)
			);
		}
		return $bSuccess;
	}
	protected function _onAfterAdd(&$arFields) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents($this->_arEntityEvents['onAfterAdd'], array(&$arFields, &$this->MessagePool));
		}
		return $bSuccess;
	}

	protected function _getLangMessageReplace($field, $bReturn2Arrays4StrReplace = true) {
		if( $bReturn2Arrays4StrReplace ) {
			$arLangReplace = array(
				'TARGET' => array('#FIELD#'),
				'VALUE' => array($field)
			);
		}
		else {
			$arLangReplace = array(
				'#FIELD#' => $field
			);
		}

		$arFieldsDescription = $this->_arFieldsDescription;
		if( is_array($arFieldsDescription) && count($arFieldsDescription)>0 ) {
			if( array_key_exists($field, $arFieldsDescription) ) {
				if($bReturn2Arrays4StrReplace) {
					if( array_key_exists('NAME', $arFieldsDescription[$field]) ) {
						$arLangReplace['TARGET'][] = '#'.$field.'_NAME#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['NAME'];
						$arLangReplace['TARGET'][] = '#FIELD_NAME#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['NAME'];
					}
					if( array_key_exists('DESC', $arFieldsDescription[$field]) ) {
						$arLangReplace['TARGET'][] = '#'.$field.'_DESCRIPTION#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['DESC'];
						$arLangReplace['TARGET'][] = '#FIELD_DESCRIPTION#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['DESC'];
					}
					if( array_key_exists('DESCR', $arFieldsDescription[$field]) ) {
						$arLangReplace['TARGET'][] = '#'.$field.'_DESCRIPTION#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['DESCR'];
						$arLangReplace['TARGET'][] = '#FIELD_DESCRIPTION#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['DESCR'];
					}
					if( array_key_exists('DESCRIPTION', $arFieldsDescription[$field]) ) {
						$arLangReplace['TARGET'][] = '#'.$field.'_DESCRIPTION#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['DESCRIPTION'];
						$arLangReplace['TARGET'][] = '#FIELD_DESCRIPTION#';
						$arLangReplace['VALUE'][] = $arFieldsDescription[$field]['DESCRIPTION'];
					}
				}
				else {
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
		}
		return $arLangReplace;
	}
//	public function getLangMessage($field = 'ALL', $bReplaceMacroses = false) {
//
//	}

	/**
	 * @param array $arFields
	 * @return int | bool
	 */
	public function add($arFields) {
		global $DB;

		$bContinueAfterEvent = ($this->_onStartAdd($arFields)!==false); if(!$bContinueAfterEvent) return 0;
		$mainTableAutoIncrement = $this->_mainTableAutoIncrement;
		$mainTablePrimaryKey = $this->_mainTablePrimaryKey;
		if( $mainTableAutoIncrement != null && isset($arFields[$mainTableAutoIncrement]) ) {
			unset($arFields[$mainTableAutoIncrement]);
		}
		$arCheckResult = $this->prepareFieldsData(self::PREPARE_ADD, $arFields);
		if($arCheckResult['__BREAK']) return 0;

		$bContinueAfterEvent = ($this->_onBeforeAdd($arFields, $arCheckResult)!==false); if(!$bContinueAfterEvent) return 0;

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
						$arLangMessage['TEXT'] = str_replace($arLangReplace['TARGET'], $arLangReplace['VALUE'], $arLangMessage['TEXT']);
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
						$arLangMessages['DUP_PK']['TEXT'] = str_replace(
							$arLangReplace['TARGET'],
							$arLangReplace['VALUE'],
							$arLangMessages['DUP_PK']['TEXT']
						);
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
			$arTableFieldsDefault = $this->_arTableFieldsDefault;
			foreach( $arTableUnique as $udxName => $arUniqueFields ) {
				$arUniqueFilter = array();
				$arInUniqueMacrosNames = array();
				$arInUniqueMacrosValues = array();
				$strUniqueFieldsList = '';
				$strUniqueFieldsValues = '';
				$bFirstUniqueField = true;
				foreach($arUniqueFields as $inUniqueFieldName) {
					if( array_key_exists($inUniqueFieldName, $arFields) ) {
						$arUniqueFilter[$inUniqueFieldName] = $arFields[$inUniqueFieldName];
					}
					elseif(array_key_exists($inUniqueFieldName, $arTableFieldsDefault)) {
						$arUniqueFilter[$inUniqueFieldName] = $arTableFieldsDefault[$inUniqueFieldName];
					}
					else {
						$arUniqueFilter[$inUniqueFieldName] = null;
					}

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
					$rsExistsList = $this->getList(null, $arUniqueFilter, null, null, null, false);
					if( $rsExistsList->Fetch() ) {
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
		$this->_lastQueryString = $sqlInsert;
		$DB->Query($sqlInsert, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);

		$bContinueAfterEvent = ($this->_onAfterAdd($arFields)!==false); if(!$bContinueAfterEvent) return 0;

		if($mainTablePrimaryKey !== null) {
			if($mainTablePrimaryKey == $mainTableAutoIncrement ) {
				$arFields[$mainTablePrimaryKey] = $DB->LastID();
			}
			return $arFields[$mainTablePrimaryKey];
		}
		return true;
	}

	protected function _onStartUpdate(&$arFields) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents($this->_arEntityEvents['onStartUpdate'], array(&$arFields, &$this->MessagePool));
		}
		return $bSuccess;
	}
	protected function _onBeforeUpdate(&$arFields, &$arCheckResult) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents(
				$this->_arEntityEvents['onBeforeUpdate'],
				array(&$arFields, &$arCheckResult, &$this->MessagePool)
			);
		}
		return $bSuccess;
	}
	protected function _onBeforeExecUpdate(&$arFields, &$arCheckResult) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents(
				$this->_arEntityEvents['onBeforeExecUpdate'],
				array(&$arFields, &$arCheckResult, &$this->MessagePool)
			);
		}
		return $bSuccess;
	}
	protected function _onAfterUpdate(&$arFields) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents($this->_arEntityEvents['onAfterUpdate'], array(&$arFields, &$this->MessagePool));
		}
		return $bSuccess;
	}

	/**
	 * @param array $arFields
	 * @param bool $bNotUpdateUniqueFields
	 * @return bool
	 */
	public function update($arFields, $bNotUpdateUniqueFields = false) {
		global $DB;
		$bContinueAfterEvent = ($this->_onStartUpdate($arFields)!==false); if(!$bContinueAfterEvent) return false;
		$arCheckResult = $this->prepareFieldsData(self::PREPARE_UPDATE, $arFields);
		if($arCheckResult['__BREAK']) return false;
		$bContinueAfterEvent = ($this->_onBeforeUpdate($arFields, $arCheckResult)!==false); if(!$bContinueAfterEvent) return false;

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
		$bContinueAfterEvent = ($this->_onBeforeExecUpdate($arFields, $arCheckResult)!==false); if(!$bContinueAfterEvent) return false;
		$strUpdate = $DB->PrepareUpdate($mainEntityTableName, $arFields);
		$strUpdateSetNullFields = '';
		$bFirstI = true;
		$strUpdateLen = strlen($strUpdate);
		foreach($arFields as $fieldName => &$fieldValue) {
			if($fieldValue === null) {
				$strUpdateSetNullFields .= (($strUpdateLen<1&&$bFirstI)?' ':', ').'`'.$fieldName.'` = NULL';
				$bFirstI = false;
			}
		}
		$strUpdate = 'UPDATE `'
						.$mainEntityTableName
						.'` SET '.$strUpdate
						.$strUpdateSetNullFields
						.' WHERE `'
							.$mainTablePrimaryKey
							.'` = '
							.('\''.$DB->ForSql($arThatElement[$mainTablePrimaryKey]).'\'')
			.';';
		$this->_lastQueryString = $strUpdate;
		$DB->Query($strUpdate, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		$bContinueAfterEvent = ($this->_onAfterUpdate($arFields)!==false); if(!$bContinueAfterEvent) return false;
		return true;
	}

	protected function _onStartDelete(&$PRIMARY_KEY_VALUE) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents($this->_arEntityEvents['onStartDelete'], array($PRIMARY_KEY_VALUE, &$this->MessagePool));
		}
		return $bSuccess;
	}
	protected function _onBeforeDelete(&$arItem) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents($this->_arEntityEvents['onBeforeDelete'], array(&$arItem, &$this->MessagePool));
		}
		return $bSuccess;
	}
	protected function _onAfterDelete(&$arItem) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents($this->_arEntityEvents['onAfterDelete'], array(&$arItem, &$this->MessagePool));
		}
		return $bSuccess;
	}

	/**
	 * @param $PRIMARY_KEY_VALUE
	 * @return bool
	 */
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
		$bContinueAfterEvent = ($this->_onStartDelete($PRIMARY_KEY_VALUE)!==false); if(!$bContinueAfterEvent) return false;
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
		$bContinueAfterEvent = ($this->_onBeforeDelete($arExists)!==false); if(!$bContinueAfterEvent) return false;
		$sqlDelete = 'DELETE FROM '.$tableName.' WHERE '.$tblFieldName.' = \''.$PRIMARY_KEY_VALUE.'\';';
		$this->_lastQueryString = $sqlDelete;
		$DB->Query($sqlDelete, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		$bContinueAfterEvent = ($this->_onAfterDelete($arExists)!==false); if(!$bContinueAfterEvent) return false;
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

	protected function _onStartDeleteByFilter(&$arFilter, &$bCheckExistence) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents(
				$this->_arEntityEvents['onStartDeleteByFilter'],
				array(&$arFilter, &$bCheckExistence, &$this->MessagePool)
			);
		}
		return $bSuccess;
	}
	protected function _onBeforeDeleteByFilter(&$arFilter, &$bCheckExistence, &$arDelete) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents(
				$this->_arEntityEvents['onBeforeDeleteByFilter'],
				array(&$arFilter, &$bCheckExistence, &$this->MessagePool)
			);
		}
		return $bSuccess;
	}
	protected function _onAfterDeleteByFilter(&$arFilter, &$bCheckExistence) {
		if(!$this->_bEntityEventsInit) {
			$bSuccess = true;
		}
		else {
			$bSuccess = self::_executeModuleEvents(
				$this->_arEntityEvents['onAfterDeleteByFilter'],
				array(&$arFilter, &$bCheckExistence, &$this->MessagePool)
			);
		}
		return $bSuccess;
	}

	/**
	 * @param array $arFilter
	 * @param bool $bCheckExistence
	 * @return bool
	 */
	public function deleteByFilter($arFilter, $bCheckExistence = true) {
		global $DB;

		$bContinueAfterEvent = ($this->_onStartDeleteByFilter($arFilter, $bCheckExistence)!==false);
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
		$bContinueAfterEvent = ($this->_onBeforeDeleteByFilter($arFilter, $bCheckExistence, $arDelete)!==false);
		if(!$bContinueAfterEvent) return false;
		$sqlDelete = 'DELETE FROM '.$arDelete['TABLE_NAME'].' WHERE'.$arDelete['WHERE_STRING'];
		$this->_lastQueryString = $sqlDelete;
		$DB->Query($sqlDelete, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
		$bContinueAfterEvent = ($this->_onAfterDeleteByFilter($arFilter, $bCheckExistence)!==false);
		if(!$bContinueAfterEvent) return false;
		return true;
	}

	/**
	 * @param DBSResult $rs
	 * @param array $arErrors
	 * @return bool
	 */
	public function deleteByDBResult(DBSResult $rs, Array &$arErrors = null) {
		$bResult = false;
		$bSuccess = false;
		$iCount = 0;
		if( get_called_class() == $rs->getAbstractionName() ) {
			if($this->_mainTablePrimaryKey !== null) {
				while($arRow = $rs->Fetch()) {
					$iCount++;
					$bSuccess = false;
					if( array_key_exists($this->_mainTablePrimaryKey, $arRow) ) {
						$bSuccess = $this->delete($arRow[$this->_mainTablePrimaryKey]);
						if(!$bSuccess && $arErrors !== null) {
							$arErrors[] = $this->getLastError('ARRAY');
						}
					}
					$bResult = $bResult && $bSuccess;
				}
			}
			else {
				// TODO: Тут получаем поля уникального индекса и по его полям вызываем deleteByFilter
			}
		}
		else {
			// TODO: Тут выкидываем ошибку. Потому что нельзя удалять записи сущности плученные с помощью класса другой сущности
		}
		return $bResult;
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
}
