<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\DBSimple;
use OBX\Core\MessagePoolDecorator;

IncludeModuleLangFile(__FILE__);

abstract class Entity extends MessagePoolDecorator implements IEntity
{
	protected function __construct() {}
	final protected function __clone() {}

	static protected $_arDBSimpleEntities = array();

	/**
	 * @final
	 * @static
	 * @return Entity
	 */
	final static public function getInstance() {
		$className = get_called_class();
		if( !isset(self::$_arDBSimpleEntities[$className]) ) {
			self::$_arDBSimpleEntities[$className] = new $className;
		}
		return self::$_arDBSimpleEntities[$className];
	}


	/*
	 * FIELD TYPES ���� ����� ����� ��� ������� $_arTableFieldsCheck
	 */
	const FLD_T_NO_CHECK = 1;				// ��� �������� - ������������ � FLD_CUSTOM_CK
	const FLD_T_INT = 2;					// �����
	const FLD_T_CHAR = 4;					// ���� ������
	const FLD_T_STRING = 8;					// ����� ������: htmlspecialcharsEx
	const FLD_T_CODE = 16;					// ���������� ���: ~^[a-zA-Z\_][a-z0-9A-Z\_]{0,15}$~
	const FLD_T_BCHAR = 32;					// ������������� ����� ������:) : 'Y' || 'N'
	const FLD_T_FLOAT = 64;					// ����������
	const FLD_T_IDENT = 128;				// ����� ������������� ~^[a-z0-9A-Z\_]{1,254}$~
	const FLD_T_DATETIME = 256;				// ���� � �����

	const FLD_T_BX_LANG_ID = 512;			// ������������� LID ��� �������
	const FLD_T_IBLOCK_ID = 1024;			// ID ���������. ��������� �������
	const FLD_T_IBLOCK_PROP_ID = 2048;		// ID �������� �������� ��. ��������� �������
	const FLD_T_IBLOCK_ELEMENT_ID = 4096;	// ID �������� ���������. ��������� �������
	const FLD_T_IBLOCK_SECTION_ID = 8192;	// ID ������ ��������. ��������� �������
	const FLD_T_USER_ID = 16384;			// ID ������������ �������
	const FLD_T_GROUP_ID = 32768;			// ID ������ ������������� �������

	/*
	 * FIELD ATTR
	 * ���� ���� ����������-���������
	 */

	/**
	 * ����������� ��������
	 * ����������� � ��������� � FLD_T_INT / FLD_T_FLOAT / FLD_T_BCHAR
	 * ��� FLD_T_INT FLD_T_FLOAT - ������ �������� �� ������������������
	 * FLD_T_BCHAR - � ���� ������ ������� ������ 'Y' ��� ��� �� ����� 'Y' ����� ���������
	 * @const
	 */
//32768
	const FLD_UNSIGNED = 65536;

	const FLD_NOT_ZERO = 131072;			// �� ���� ��� int � float � �� ������ ����� ��� string
	const FLD_NOT_NULL = 262144;		// �� NULL - ������ NULL ��� ��� ������ ����
	const FLD_DEFAULT = 524288;			// ������ �������� �� ������� ���� ���� - ��-� �� �����. � ������� $this->_arTableFieldsDefault
	const FLD_REQUIRED = 1048576;		// �������� ���� �������� ������������ ��� ���������� ����� ������
	const FLD_CUSTOM_CK = 2097152;		// ���� �-�� �������� ��������
	const FLD_UNSET = 4194304;			// �������� �������� �� arFields!




	/**
	 * ���������� �-�� self::add() / self::update() ����� ��������
	 * ���� �������� �� �������� ��� ��������� ���� � ������ ������ � ��� ������
	 * ������ ��������� � ��������� � self::FLD_CUSTOM_CK
	 * ��� ���� ��������� �� ������ ������ �������� ����������� � ������ __check_FIELD_NAME()
	 * @const
	 */

	const FLD_BRK_INCORR = 8388608;		// �������� ���������� �-��, ���� �������� �������

	const FLD_ATTR_ALL = 16711680;		// ��� FIELD ATTRs ������


	/**
	 * ����������� ��� ���������� ��� int c ����������� ��������� ��� ���������� ����� ID
	 * self::FLD_T_INT
	 * | self::FLD_NOT_NULL
	 * | self::FLD_NOT_ZERO
	 * | self::FLD_UNSIGNED,
	 *
	 * //2 + 32768 + 65536 + 131072
	 * 2 + 262144 + 131072 + 65536
	 * @const
	 */
	const FLD_T_PK_ID = 458754;

	const E_NOTHING_TO_DELETE = 101;		// ���������� �������. ����� �� �������
	const E_DUP_PK = 102;					// ������ � ����� PRIMARY_KEY ��� ����
	const E_DUP_UNIQUE = 103;				// ������������ �������� ����������� �������
	const E_MISS_REQUIRED = 104;			// �� ��������� ������������ ����
	const E_NOTHING_TO_UPDATE = 105;		// ���������� ��������. ������ �� �������
	const E_CANT_DEL_WITHOUT_PK = 106;		// ���������� ������������ ����� delete ��� ������������� PrimaryKey
	//const WRN_
	//const MSG_
	
	const PREPARE_ADD = 3;
	const PREPARE_UPDATE = 5;

	/**
	 * @const FLD_CUSTOM_CK - ��� ������� ����� �-�� �������� ��������
	 * ��� ����������
	 * ���� � ���, ��� � ������-����������, ���� ������� ���� �������� ����,
	 * ��� ���������� $this->prepareFieldsData()
	 * ����� ��������� �-�� ������-���������� ���� __check_<���_����>($fieldValue, $arCheckData)
	 * ����� ������� ����� � ������ ����������� �������� ���� �������� ����
	 * ��� ������ $this->prepareFieldsData()
	 */

	/**
	 * @var bool
	 */
	protected $_bDistinctGetList = false;

	/**
	 * ������ � ��������� ������ ��������
	 * � �������� ����� ������������ alias ������� (long_table_name as ARKEY)
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
	 * ������ � ��������� ����� �������
	 * ������ ���� ����� �������������� � ��������� ������ Entity::getList() � �������� $arSelect
	 * ����� �� ����������� ��������� � ������� ����� ������
	 * ��� ����� �� ������� � ����� ����� ���������� ������ ����������� ���� ��������
	 * ������ ���� �������� �������� ��������� ������ ������ �������� �������� ALIAS �������,
	 * 	� ��������� - ��� ���� � ��������������� �������.
	 * 	��� �� �������� ���������� �� ������� ���� USER_NAME,
	 * 	��� �� �������� ������� ����������. ������ ����� ���������� � ������ obx.market � ������ OBX\OrdersList
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
	 *
	 * ���������� ���������� ����������� ���� ���� DATETIME
	 * ��� ��� � ������������ �������� ���������� ���������� ��������� ���
	 * <code>
	 * $this->_arTableFields['START_DATE'] = array('E' => '('.$DB->DateToCharFunction('E.START_DATE').')');
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
	 * �������������� �������� ����� �������� � $this->_arTableFields
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
		/** @noinspection PhpUnusedLocalVariableInspection */
		foreach($this->_arTableFields as $fieldCode => &$arTblFieldName) {
			if(
				isset($this->_arFieldsDescription[$fieldCode])
				&& isset($this->_arFieldsDescription[$fieldCode]['NAME'])
			)
				$arResult[$fieldCode] = $this->_arFieldsDescription[$fieldCode];
		}
		return $arResult;
	}

	/**
	 * ���������� �������� ALIAS �������� ������� ��������.
	 * �������� ������� �������� ����� ������������ � �������:
	 * 	$this->add(), $this->update(), $this->delete()
	 * @var string
	 */
	protected $_mainTable = '';
	final public function getMainTable() {
		return $this->_mainTable;
	}

	/**
	 * ���������� �������� ��� ���� �������� ������� ��������,
	 * ������� �������� ��������� ������
	 * @var string
	 * @access protected
	 */
	protected $_mainTablePrimaryKey = 'ID';
	final public function getMainTablePrimaryKey() {
		return $this->_mainTablePrimaryKey;
	}

	/**
	 * ���������� �������� ��� ���� �������� ������� ��������,
	 * ������� �������� ����������������
	 * @var string
	 * @access protected
	 */
	protected $_mainTableAutoIncrement = 'ID';
	final public function getMainTableAutoIncrement() {
		return $this->_mainTableAutoIncrement;
	}

	/**
	 * ������ ���������� ����� ����� ������
	 * ������ ����� ����� ����������� ��� ������������ ������� � ����� WHERE.
	 * 	� ��� �� � ������ $this->getByID() �������� ��������
	 * 	����� � arSelect ������� ���� ��������� � �������� ������� ��������, �� ���� ��������� �� ������� �������.
	 * 	� ����� ������� ����� $this->getByID() ����������� � ������ ������ ��� ����, ��� �� ���������
	 * 	� ���, ��� ������ �� ������ ���� ������� � ���� ����� ��������� ������ ������� �� �������� ������� ��������.
	 * 	����������: ����� ��������� ����� ����������� � JOIN-�. � ����� ������� ���� ���������
	 * 	� $this->_arTableLeftJoin � $this->_arTableLinks
	 *
	 * ���� ���� ��� ���������� ��������� ������ JOIN, ��� ����� �������� ����� ��������� ������ ������
	 * @var array
	 * @access protected
	 */
	protected $_arTableLinks = array();
	final public function getTableLinks() {
		return $this->_arTableLinks;
	}

	/**
	 * ������ ����������� ������� ��� LEFT JOIN
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
	 * ������ � �������� ��������
	 * ���� �� �����������
	 * @var array
	 * @access protected
	 * @deprecated ����������� �� �����
	 */
	protected $_arTableIndex = array();
	/** @deprecated ����������� �� ����� */
	final public function getTableIndex() {
		/** @noinspection PhpDeprecationInspection */
		return $this->_arTableIndex;
	}

	/**
	 * ������ � ��������� unique-��������
	 * ��������� �����������.
	 * ������ $this->add() � $this->update() ��������� ���� ������ ��� �������������� ������� ������
	 * <code>
	 * 	<?php
	 * 		$_arTableUnique = array(
	 * 			'���_�����������_�������' => array('����1', '����2')
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
	 * �������� ��������� ����� ������� ������� ����� ������������� ��������� � arFilter ������ GetList,
	 * ���� �� ����� ������� ��� ����.
	 * ����� ��������, ��� _arFilterDefault ��� ������� ����������� � �����������
	 * � ������� ���� ����� ���������� � ������ ������� ������� Entity
	 * @var array
	 * @access protected
	 */
	protected $_arFilterDefault = array();
	final public function getFilterDefault() {
		return $this->_arFilterDefault;
	}

	/**
	 * arSelect �� ���������
	 * ���� � ������ $this->getLis() �� ����� �������� arSelect, �� ����� ����������� ����.
	 * ���� � ������ �������� �� ����� � ���� ������,
	 * �� � �������� arSelect ����� ������ ������ ������ ������ ������� $this->_arTableFields
	 * @var array
	 * @access protected
	 */
	protected $_arSelectDefault = array();
	final public function getSelectDefault() {
		return $this->_arSelectDefault;
	}

	/**
	 * ����������� �� ���������
	 * ���� � ������ $this->getList() �� ������ �������� arSort, �� �� ����� �������� �� ����� �������
	 * @var array
	 * @access protected
	 */
	protected $_arSortDefault = array('ID' => 'ASC');
	final public function getSortDefault() {
		return $this->_arSortDefault;
	}

	/**
	 * ���� � �������� ����� �������� ������� ��������
	 * ������������ ��� �������� ������� ������ � ������� $this->add() � $this->update()
	 * ���� ��� ���������� ������������ � ���� ������� ���������������
	 * ������:
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
	 * �������� ��������� ��� ������ ������
	 * �� ������� ������� ����� �������� ������ � ��������������
	 * �������� ������� ���������������� � ���������� �� ���������� ������ �������
	 * REQ_FLD_���_���� - �������� ������� ���� � ��������� $arFields ������ $this->add($arFields)
	 * 				�� ��������� ���� "���_����"
	 * DUP_ADD_���_UNIQUE_������� - �������� ������� ���� � ��������� $arFields ������ $this->add($arFields)
	 * 				������ ���� ����������� ������� ��� ������������ ��� ������ � ������� ��
	 * DUP_UPD_���_UNIQUE_������� - �������� ������� ���� � ��������� $arFields ������ $this->update($arFields)
	 * 				������ ���� ����������� ������� ��� ������������ ��� ������ � ������� ��
	 * NOTHING_TO_DELETE - �������� ������� ���� � ����� $this->delete() �� ����� ������ ��� ��������
	 * 				�� ��������� ���� "���_����"
	 * NOTHING_TO_UPDATE - �������� ������� ���� � ����� $this->update() �� ����� ������ ��� ����������
	 * 				�� ��������� ���� "���_����"
	 *
	 * ������ �������� �������� ��������� �����
	 * 		'TYPE' - ����� ��������� ��������
	 * 			����������: � ����������� �� ����� ���� ����� ������ ��������������� ����� ������� CMessagePool
	 * 			'E' - Error - ������ - MessagePool::addError()
	 * 			'W' - Warning - �������������� - MessagePool::addError()
	 * 			'M' - Message��������� - MessagePool::addMessage()
	 * 		'TEXT' - ����� �������
	 * 		'CODE' - ��� �������
	 * 		��� ������� ����������� 'E'
	 * ������:
	 * <code>
	 * 	<?php
	 * 		$this->_arDBSimpleLangMessages = array(
	 *			'REQ_FLD_���_����' =>  array(
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
	 * ������ �������� �������� �� ��������� ��� ����� ��������� arFields ������ $this->add()
	 * @var array
	 * @access protected
	 */
	protected $_arTableFieldsDefault = array();
	final public function getTableFieldsDefault(){
		return $this->_arTableFieldsDefault;
	}

	/**
	 * ����������� �� ���������
	 * @var array
	 * @access protected
	 */
	protected $_arGroupByFields = array();
	final public function getGroupByFields() {
		return $this->_arGroupByFields;
	}

	/**
	 * @var null | string
	 * ������ � �������� ����������� ��������
	 */
	protected $_entityModuleID = null;

	/**
	 * @var null | string
	 * ������������� ������������ ��� �������� ������� ������������ � ��������
	 * ������ ������������ �������
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
	protected $_entityID = null;
	/**
	 * @var null|string
	 * @deprecated
	 */
	protected $_entityEventsID = null;

	/**
	 * @var array
	 * ������ ������� ��������
	 */
	protected $_arEntityEvents = array();

	/**
	 * @var bool
	 * ������� ����, ��� ����������� ������� �������� ����������������
	 */
	protected $_bEntityEventsInit = false;

	/**
	 * ������ �������� ����� ����� ������� ��������, ������� �������� ��� ��������������� � ���������������� ������
	 * @var array
	 * @access protected
	 */
	protected $_arFieldsEditInAdmin = array();

	protected $_lastQueryString = '';
	final public function getLastQueryString() {
		return $this->_lastQueryString;
	}

	/**
	 * �������� � �������� ������ ������ �������
	 * ��� �������������� ��������� ������������ ������ �������
	 * ������ ����� ������ ���� �������� � ������������ ��������
	 * @return void
	 */
	protected function _getEntityEvents() {
		/** @noinspection PhpDeprecationInspection */
		if($this->_entityID === null && $this->_entityEventsID !== null) {
			/** @noinspection PhpDeprecationInspection */
			$this->_entityID = $this->_entityEventsID;
		}
		if($this->_entityModuleID === null || $this->_entityID === null) {
			return;
		}
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartAdd'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityID.'Add',
			true
		);
		$this->_arEntityEvents['onBeforeAdd'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityID.'Add',
			true
		);
		$this->_arEntityEvents['onAfterAdd'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityID.'Add',
			true
		);
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityID.'Update',
			true
		);
		$this->_arEntityEvents['onBeforeUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityID.'Update',
			true
		);
		$this->_arEntityEvents['onBeforeExecUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBeforeExec'.$this->_entityID.'Update',
			true
		);
		$this->_arEntityEvents['onAfterUpdate'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityID.'Update',
			true
		);
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartDelete'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityID.'Delete',
			true
		);
		$this->_arEntityEvents['onBeforeDelete'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityID.'Delete',
			true
		);
		$this->_arEntityEvents['onAfterDelete'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityID.'Delete',
			true
		);
		////////////////////////////////////////////////////////////////////
		$this->_arEntityEvents['onStartDeleteByFilter'] = GetModuleEvents(
			$this->_entityModuleID,
			'onStart'.$this->_entityID.'DeleteByFilter',
			true
		);
		$this->_arEntityEvents['onBeforeDeleteByFilter'] = GetModuleEvents(
			$this->_entityModuleID,
			'onBefore'.$this->_entityID.'DeleteByFilter',
			true
		);
		$this->_arEntityEvents['onAfterDeleteByFilter'] = GetModuleEvents(
			$this->_entityModuleID,
			'onAfter'.$this->_entityID.'DeleteByFilter',
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
	 * ����� ���������� ������
	 * ����������� � $this->add() � $this->update()
	 * ���������� �������� ����� �� ������� $this->_arTableFieldsCheck ��� �������� ������� ���������� ������
	 * @param int $prepareType - ����� ��������� ��� ������� self::PREPARE_ADD ��� self::PREPARE_UPDATE
	 * @param array $arFields - �������� ����� �������� ������� ��������
	 * @param null|array $arTableFieldsCheck - ���� �����, �� �������������� ������� $this->_arTableFieldsCheck
	 * @param null|array $arTableFieldsDefault - ���� �����, �� �������������� ������� $this->_arTableFieldsDefault
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
						case self::FLD_T_DATETIME:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_DATETIME';
							$arCheckResult[$fieldName]['FIELD_TYPE'] = self::FLD_T_DATETIME;
							$fieldValue = trim($fieldValue);
							$bValueIsCorrect = true;
							// TODO: ��� ���� �������� ��������� ��� � �������
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
							/** @noinspection PhpDynamicAsStaticMethodCallInspection */
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
							/** @noinspection PhpDynamicAsStaticMethodCallInspection */
							$rs = \CUser::GetByID($fieldValue);
							if( ($arData = $rs->GetNext()) ) {
								$arCheckResult[$fieldName]['CHECK_DATA'] = $arData;
								$bValueIsCorrect = true;
							}
							break;
						case self::FLD_T_GROUP_ID:
							$arCheckResult[$fieldName]['FIELD_TYPE'] = 'FLD_T_GROUP_ID';
							$arCheckResult[$fieldName]['FIELD_TYPE_MASK'] = self::FLD_T_GROUP_ID;

							/** @noinspection PhpDynamicAsStaticMethodCallInspection */
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
	 * ����� ������� �-�� ��� ������������� ����� $this->prepareFieldsData
	 * $this->prepareFieldsData �������� ������ ��� ���������� ������,
	 * � ��� -��� ��������� �� �� ��� ������� ����������� ��� �������� � ��
	 * ��������:
	 *		prepareFieldsData ����� ������������ ���� CODE ������� �� ������ ���������,
	 * 		���� ��� ���� ��������� �������� ������������� �������(self::FLD_REQUIRED), �� ������ �-�� ������
	 * 		������ ���� � �������������� �������
	 * @param array &$arFields ������ - ���� ���������� � ���������
	 * @param array &$arCheckResult
	 * @param array|null $arTableFieldsCheck
	 * @param array|null $arTableFieldsDefault  - �������� ����� �� ���������, ���� ���� ��������, �� ���� ��������� ��������, ����� ����������� ���
	 * @return array ������ ����������� ������������ ��������
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
		foreach($arTableFieldsCheck as $fldAlias => &$fieldAttr) {
			$bRequired = ($fieldAttr & self::FLD_REQUIRED)?true:false;
			if( $bRequired && !array_key_exists($fldAlias, $arFields) ) {
				if( ($fieldAttr & self::FLD_DEFAULT)
					&& ( !isset($arFields[$fldAlias]) || $arCheckResult[$fldAlias]['IS_EMPTY'] )
					&& array_key_exists($fldAlias, $arTableFieldsDefault)
				) {
					$arFields[$fldAlias] = $arTableFieldsDefault[$fldAlias];
				}
				else {
					$arMessedFields[] = $fldAlias;
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
	 * @param $bLogicOrInsideSubFilter - ����������� ������ ������� ��� ����� � ������� OR
	 * @param string $aws - additional white space - �������������� ������ - ��� �������� ������� :)
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
					/** @noinspection PhpUnusedLocalVariableInspection */
					$sqlField = $asName.'.'.$tblFieldName;
					// ������ ������� ������ �� ����, ������� �������� �����������
					if($isSubQuery) {
						// [pronix:2013-06-19]���� ������� �� ������� ����� ��������� ���������� ��� ������� :)
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
						$strNot = '';
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
							$strNot = '';
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
	 * ���������� ������ ������� ��������
	 * @param null | array $arSort - ���� � ������� ����������
	 * @param null | array $arFilter - ������ �����
	 * @param null | array $arGroupBy - ����������� �� �����
	 * @param null | array $arPagination - ������ ��� ������������ ������������ ���������
	 * @param null | array $arSelect - ���������� ����
	 * @param bool $bShowNullFields - ����������� NULL �������� - �.�. ��������� �� ���������� JOIN
	 * @return bool | DBResult
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
			// ���� SELECT ������
			$arSelectDefault = $this->_arSelectDefault;
			if( count($arSelectDefault)>0 ) {
				$arSelect = $arSelectDefault;
			}
			else {
				$arSelect = array_keys($arTableFields);
			}
		}
		$bFirst = true;
		$arSubQueryFields = array();
		foreach($arSelect as $fieldCode) {
			if(array_key_exists($fieldCode, $arTableFields) ) {
				$arTblField = $arTableFields[$fieldCode];
				$this->_checkRequiredTablesByField($arSelectFromTables, $arTableFields, $fieldCode);
				list($tblAlias, $tblFieldName) = each($arTblField);
				$arDateFormat = null;
				if(!empty($arTblField['FORMAT_DATE'])) {
					$arDateFormat = array(
						'TYPE' => (array_key_exists('TYPE', $arTblField['FORMAT_DATE'])?$arTblField['FORMAT_DATE']['TYPE']:'FULL'),
						'SITE_ID' => (array_key_exists('SITE_ID', $arTblField['FORMAT_DATE'])?$arTblField['FORMAT_DATE']['SITE_ID']:SITE_ID),
						'ONLY_SITE_LANG' => (array_key_exists('ONLY_SITE_LANG', $arTblField['FORMAT_DATE'])?$arTblField['FORMAT_DATE']['ONLY_SITE_LANG']:false),
					);
				}
				elseif( array_key_exists($fieldCode, $this->_arTableFieldsCheck)
					&& self::FLD_T_DATETIME === ($this->_arTableFieldsCheck[$fieldCode] & ~self::FLD_ATTR_ALL)
				) {
					$arDateFormat = array(
						'TYPE' => 'FULL',
						'SITE_ID' => SITE_ID,
						'ONLY_SITE_LANG' => false
					);
				}

				$arSubQueryFields[$fieldCode] = (strpos($tblFieldName,'(')!==false);
				if(!$arSubQueryFields[$fieldCode]){
					$sqlField = $tblAlias.'.'.$tblFieldName;
				}
				else{
					$sqlField = $tblFieldName;
				}
				if(null !== $arDateFormat) {
					$sqlField = '('.$DB->DateToCharFunction($tblAlias.'.'.$tblFieldName).')';
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
			if( count($this->_arSortDefault)>0 ) {
				$arSort = $this->_arSortDefault;
			}
			else {
				$arSort = array();
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
					if(!isset($arSubQueryFields[$fieldCode])) {
						$arSubQueryFields[$fieldCode] = (strpos($tblFieldName,'(')!==false);
					}
					if (!$arSubQueryFields[$fieldCode]){
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

		// ����������
		$arGroupByFields = $this->_arGroupByFields;
		if( !empty($arGroupBy) && is_array($arGroupBy) ) {
			foreach ($arGroupBy as $fieldCode){
				if( array_key_exists($fieldCode, $arTableFields) ) {
					$arTblField = $arTableFields[$fieldCode];
					$this->_checkRequiredTablesByField($arSelectFromTables, $arTableFields, $fieldCode);
					list($tblAlias, $tblFieldName) = each($arTblField);
					if(!isset($arSubQueryFields[$fieldCode])) {
						$arSubQueryFields[$fieldCode] = (strpos($tblFieldName,'(')!==false);
					}
					if($arSubQueryFields[$fieldCode]) {
						continue;
					}
					if( !in_array($tblAlias.'.'.$tblFieldName, $arGroupByFields) ) {
						$arGroupByFields[] = $tblAlias.'.'.$tblFieldName;
					}
				}
			}
		}
		$sGroupBy = '';
		if( !empty($arGroupByFields) > 0 ) {
			$sGroupBy = "\nGROUP BY ( ".implode(", ",$arGroupByFields)." )";
		}

		// ����� WHERE � ������� ��������� �������
		foreach($arTableLinks as $linkKey => $arTblLink) {
			$arLeftField = $arTblLink[0];
			$arRightField = $arTblLink[1];
			list($asLeftTblName, $leftFieldName) = each($arLeftField);
			list($asRightTblName, $rightFieldName) = each($arRightField);
			if( $bShowNullFields
				&& (   isset($arTableLeftJoin[$asLeftTblName])
					|| isset($arTableLeftJoin[$asRightTblName])
					|| isset($arTableRightJoin[$asLeftTblName])
					|| isset($arTableRightJoin[$asRightTblName]))
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
		/** @noinspection PhpUnusedLocalVariableInspection */
		foreach($arTableLeftJoinTables as $sdTblName => &$bJoinThisTable) {
			$bJoinThisTable = false;
		}
		$arTableRightJoinTables = $arTableRightJoin;
		/** @noinspection PhpUnusedLocalVariableInspection */
		foreach($arTableRightJoinTables as $sdTblName => &$bJoinThisTable) {
			$bJoinThisTable = false;
		}
		// �� ����� ������ �������� | ����� ������� �������
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
			$res = new DBResult($this);

			$res->NavQuery($sqlList, $res_cnt["C"], $arPagination);
		}
		else {
			$sqlList = 'SELECT '.$strDistinct.$sqlList;
			$res = $DB->Query($sqlList, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);
			$res = new DBResult($this, $res);
		}
		$this->_lastQueryString = $sqlList;
		//$res = $DB->Query($sqlList, false, 'File: '.__FILE__."<br />\nLine: ".__LINE__);

		return $res;
	}

	/**
	 * �� �� ��� � $this->getList() ������ ���������� �� CDBResult, � array
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
	 * ����� ��������� �������� ������ ���� �� �������� ������� ��������
	 * ��� � ������� ������ ���� �� ������ ������,
	 * �� ������ � ��� ������ ���� ��� ��������� � ������� $this->_arTableLinks
	 * ����-���������� � $arSelect ��� �� ����� ���������������
	 * @param string |int | float $PRIMARY_KEY_VALUE
	 * @param array | null $arSelect
	 * @param bool $bReturnDBSResult
	 * @return array | DBResult
	 */
	public function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnDBSResult = false) {
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
			// ���� SELECT ������
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
				// TODO: ��� ����� ��������� � ����� ������. ����������� ����� ������� ������. ����� ��������� ������������� ����������
				$isSubQuery = ((strpos($tblFieldName,'(')===false)?false:true);
				if($isSubQuery) {
					continue;
				}
				$arDateFormat = null;
				if(array_key_exists('FORMAT_DATE', $arTblField)) {
					$arDateFormat = array(
						'TYPE' => (array_key_exists('TYPE', $arTblField['FORMAT_DATE'])?$arTblField['FORMAT_DATE']['TYPE']:'FULL'),
						'SITE_ID' => (array_key_exists('SITE_ID', $arTblField['FORMAT_DATE'])?$arTblField['FORMAT_DATE']['SITE_ID']:SITE_ID),
						'ONLY_SITE_LANG' => (array_key_exists('ONLY_SITE_LANG', $arTblField['FORMAT_DATE'])?$arTblField['FORMAT_DATE']['ONLY_SITE_LANG']:false),
					);
				}
				elseif( array_key_exists($fieldCode, $this->_arTableFieldsCheck)
					&& self::FLD_T_DATETIME === ($this->_arTableFieldsCheck[$fieldCode] & ~self::FLD_ATTR_ALL)
				) {
					$arDateFormat = array(
						'TYPE' => 'FULL',
						'SITE_ID' => SITE_ID,
						'ONLY_SITE_LANG' => false
					);
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
				$arAlreadySelected[$sqlField] = true;
				if(null !== $arDateFormat) {
					$sqlField = '('.$DB->DateToCharFunction(
							$asName.'.'.$tblFieldName,
							$arDateFormat['TYPE'],
							$arDateFormat['SITE_ID'],
							$arDateFormat['ONLY_SITE_LANG']
					).')';
				}
				$sFields .= (($bFirst)?"\n\t":", \n\t").$sqlField.' AS '.$fieldCode;
				$bFirst = false;
				$arSelectFromTables[$asName] = true;
			}
		}
		// ����� WHERE � ������� ��������� �������
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
		if(!$bReturnDBSResult) {
			if( ($arElement = $rsList->Fetch()) ) {
				return $arElement;
			}
			return array();
		}
		$rsList = new DBResult($this, $rsList);
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
					// �������� ������� ��� ����� � lang-����������
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
						case 'N':
							$this->addNotice($arLangMessage['TEXT'], $arLangMessage['CODE']);
							break;
					}
				}
				else {
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_ADD_MISS_FIELD', array(
						'#FIELD#' => $fieldName
					)), self::E_MISS_REQUIRED);
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
					)), self::E_DUP_PK);
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
							)), self::E_DUP_UNIQUE);
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

		$returnValue = true;
		if($mainTablePrimaryKey !== null) {
			if($mainTablePrimaryKey == $mainTableAutoIncrement ) {
				$arFields[$mainTablePrimaryKey] = $DB->LastID();
			}
			$returnValue = $arFields[$mainTablePrimaryKey];
		}
		$bContinueAfterEvent = ($this->_onAfterAdd($arFields)!==false); if(!$bContinueAfterEvent) return 0;
		return $returnValue;
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
	 * @return bool
	 */
	public function update($arFields) {
		global $DB;
		$bContinueAfterEvent = ($this->_onStartUpdate($arFields)!==false);
		if(!$bContinueAfterEvent) return false;
		$bNotUpdateUniqueFields = false;
		if(!empty($arFields['__NOT_UPDATE_UNIQUE_FIELDS'])) {
			$bNotUpdateUniqueFields = true;
			unset($arFields['__NOT_UPDATE_UNIQUE_FIELDS']);
		}

		$arCheckResult = $this->prepareFieldsData(self::PREPARE_UPDATE, $arFields);
		if($arCheckResult['__BREAK']) return false;

		$bContinueAfterEvent = ($this->_onBeforeUpdate($arFields, $arCheckResult)!==false);
		if(!$bContinueAfterEvent) return false;

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
		// ���� PK �� �����, �� ����� ��������� �������� �� ����� �������� � arFields unique ��������
		// � �� ���� ����� �������� PK
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
							// TODO: �������������� ���-�� �������� � ��
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
				$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_UPD_NOTHING_TO_UPDATE'), self::E_NOTHING_TO_UPDATE);
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
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_UPD_NOTHING_TO_UPDATE'), self::E_NOTHING_TO_UPDATE);
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
					// ���� ��������� ��������� ���� �������� � ���������� ������
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
								)), self::E_DUP_UNIQUE);
							}
							return false;
						}
					}
				}
			}
		}

		$arFields[$mainTablePrimaryKey] = $arThatElement[$mainTablePrimaryKey];
		$bContinueAfterEvent = ($this->_onBeforeExecUpdate($arFields, $arCheckResult)!==false);
		if(!$bContinueAfterEvent) return false;
		unset($arFields[$mainTablePrimaryKey]);

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
		$arFields[$mainTablePrimaryKey] = $DB->ForSql($arThatElement[$mainTablePrimaryKey]);

		$bContinueAfterEvent = ($this->_onAfterUpdate($arFields)!==false);
		if(!$bContinueAfterEvent) return false;

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
			)), self::E_CANT_DEL_WITHOUT_PK);
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
				$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::E_NOTHING_TO_DELETE);
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
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::E_NOTHING_TO_DELETE);
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
				$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::E_NOTHING_TO_DELETE);
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
					$this->addError(GetMessage('OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE'), self::E_NOTHING_TO_DELETE);
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
	 * @param DBResult $rs
	 * @param array $arErrors
	 * @return bool
	 */
	public function deleteByDBResult(DBResult $rs, Array &$arErrors = null) {
		$bResult = false;
		$bSuccess = false;
		$iCount = 0;
		if(
			$rs instanceof DBResult
			&& $rs->getDBSimpleEntity() === $this
		) {
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
				// TODO: ��� �������� ���� ����������� ������� � �� ��� ����� �������� deleteByFilter
			}
		}
		else {
			// TODO: ��� ���������� ������. ������ ��� ������ ������� ������ �������� ��������� � ������� ������ ������ ��������
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
