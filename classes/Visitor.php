<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @License GPLv3                            **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

/*
 * Класс для работы с посетителями, в том числе и не авторизованными.
 *
 * Данные о посетителе хранятся в таблице
 *  obx_visitors
 *      ID (int 18)
 *      COOKIE_ID (varchar 32) - {md5 хеш от уникальных данных} - главный ключ. Он же и будет храниться в куках.
 *      USER_ID (int 18) - ID авторизованного пользователя битрикс, может быть 0 если не авторизован. Может повторяться для разных COOKIE_ID,
 *                          если пользователь несколько раз сбрасывал куки, затем заходил на сайт не авторизованным, затем авторизовывался.
 */

IncludeModuleLangFile(__FILE__);

class OBX_VisitorDBS extends OBX_DBSimple
{
	protected $_arTableList = array(
		'V' => 'obx_visitors'
	);
	protected $_arTableFields = array(
		'ID'				=> array('V' => 'ID'),
		'COOKIE_ID'			=> array('V' => 'COOKIE_ID'),
		'USER_ID'			=> array('V' => 'USER_ID'),
	);

	protected $_mainTable = 'V';
	protected $_mainTablePrimaryKey = 'ID';
	protected $_mainTableAutoIncrement = 'ID';

	protected $_arSelectDefault = array(
		'ID',
		'COOKIE_ID',
		'USER_ID'
	);
	protected $_arSortDefault = array('USER_ID' => 'ASC');
	protected $_arTableUnique = array("COOKIE_ID" => array("COOKIE_ID"));

	function __construct()
	{
		$this->_arTableFieldsDefault = array(
			'USER_ID' => 0,
		);
		$this->_arTableFieldsCheck = array(
			'ID' => self::FLD_T_INT | self::FLD_NOT_NULL,
			'COOKIE_ID' => self::FLD_T_STRING | self::FLD_REQUIRED,
			'USER_ID' => self::FLD_T_INT, // не self::FLD_T_USER_ID так как может быть 0 и проверка наличия в БД не нужна
		);
		$this->_arDBSimpleLangMessages = array(
			'REQ_FLD_COOKIE_ID' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_ERROR_REQ_FLD_COOKIE_ID'),
				'CODE' => 1
			),
			'DUP_UPD_COOKIE_ID' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_ERROR_DUP_UPD_COOKIE_ID'),
				'CODE' => 2
			),
			'DUP_ADD_ID' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_ERROR_DUP_ADD_ID'),
				'CODE' => 3
			),
			'DUP_ADD_COOKIE_ID' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_ERROR_DUP_ADD_COOKIE_ID'),
				'CODE' => 4
			),
			'NOTHING_TO_DELETE' => array(
				'TYPE' => 'M',
				'TEXT' => GetMessage('OBX_VISITORS_MESSAGE_NOTHING_TO_DELETE'),
				'CODE' => 5
			),
			'NOTHING_TO_UPDATE' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_ERROR_NOTHING_TO_UPDATE'),
				'CODE' => 6
			)
		);
		$this->_arFieldsDescription = array(
			'ID' => array(
				"NAME" => GetMessage("OBX_VISITORS_ID_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_ID_DESCR"),
			),
			'COOKIE_ID' => array(
				"NAME" => GetMessage("OBX_VISITORS_COOKIE_ID_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_COOKIE_ID_DESCR"),
			),
			'USER_ID' => array(
				"NAME" => GetMessage("OBX_VISITORS_USER_ID_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_USER_ID_DESCR"),
			),
		);
	}

	// Ограничим возможности обновления
	public function update($arFields) {
		return parent::update($arFields, true);
	}
}

class OBX_VisitorsList extends OBX_DBSimpleStatic {}
OBX_VisitorsList::__initDBSimple(OBX_VisitorDBS::getInstance());

/*
 * Посещения
 *  obx_visitors_hits
 *      ID (int 20)
 *      VISITOR_ID (int 18) - значение из поля ID таблицы obx_visitors
 *      DATE_HIT (datetime)
 *      SITE_ID (varchar 5)
 *      URL (text) - часть адреса после первого слеша /
 */
class OBX_VisitorHitDBS extends OBX_DBSimple
{
	protected $_arTableList = array(
		'H' => 'obx_visitors_hits'
	);
	protected $_arTableFields = array(
		'ID'				=> array('H' => 'ID'),
		'VISITOR_ID'		=> array('H' => 'VISITOR_ID'),
		'DATE_HIT'			=> array('H' => 'DATE_HIT'),
		'SITE_ID'		    => array('H' => 'SITE_ID'),
		'URL'			    => array('H' => 'URL'),
	);

	protected $_mainTable = 'H';
	protected $_mainTablePrimaryKey = 'ID';
	protected $_mainTableAutoIncrement = 'ID';

	protected $_arSelectDefault = array(
		'ID',
		'VISITOR_ID',
		'DATE_HIT',
		'SITE_ID',
		'URL'
	);
	protected $_arSortDefault = array('ID' => 'ASC');
	protected $_arTableUnique = array();

	function __construct()
	{
		$this->_arTableFieldsDefault = array();
		$this->_arTableFieldsCheck = array(
			'ID' => self::FLD_T_INT | self::FLD_NOT_NULL,
			'VISITOR_ID' => self::FLD_T_INT | self::FLD_NOT_NULL | self::FLD_REQUIRED,
			'DATE_HIT' => self::FLD_T_STRING,
			'SITE_ID' => self::FLD_T_STRING,
			'URL' => self::FLD_T_STRING
		);
		$this->_arDBSimpleLangMessages = array(
			'REQ_FLD_VISITOR_ID' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_HITS_ERROR_REQ_FLD_VISITOR_ID'),
				'CODE' => 1
			),
			'DUP_ADD_ID' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_HITS_ERROR_DUP_ADD_ID'),
				'CODE' => 2
			),
			'NOTHING_TO_DELETE' => array(
				'TYPE' => 'M',
				'TEXT' => GetMessage('OBX_VISITORS_HITS_MESSAGE_NOTHING_TO_DELETE'),
				'CODE' => 3
			),
			'NOTHING_TO_UPDATE' => array(
				'TYPE' => 'E',
				'TEXT' => GetMessage('OBX_VISITORS_HITS_ERROR_NOTHING_TO_UPDATE'),
				'CODE' => 4
			)
		);
		$this->_arFieldsDescription = array(
			'ID' => array(
				"NAME" => GetMessage("OBX_VISITORS_HITS_ID_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_HITS_ID_DESCR"),
			),
			'VISITOR_ID' => array(
				"NAME" => GetMessage("OBX_VISITORS_HITS_VISITOR_ID_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_HITS_VISITOR_ID_DESCR"),
			),
			'DATE_HIT' => array(
				"NAME" => GetMessage("OBX_VISITORS_HITS_DATE_HIT_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_HITS_DATE_HIT_DESCR"),
			),
			'SITE_ID' => array(
				"NAME" => GetMessage("OBX_VISITORS_HITS_SITE_ID_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_HITS_SITE_ID_DESCR"),
			),
			'URL' => array(
				"NAME" => GetMessage("OBX_VISITORS_HITS_URL_NAME"),
				"DESCR" => GetMessage("OBX_VISITORS_HITS_URL_DESCR"),
			),
		);
	}

	/**
	 * Обновлять нельзя.
	 * @param $arFields
	 * @return bool
	 */
	public function update($arFields) {
		return false;
	}
}

/*
 * Внимание! Необходимо учитывать, что у нескольких посетителей с разным COOKIE_ID может быть одинаковый USER_ID.
 * Это возможно, если пользователь очищал куки и снова зашел на сайт, получив новый COOKIE_ID, а затем авторизовался.
 */
class OBX_Visitor
{
	static private $visitor_cookie_name = "VISITOR_COOKIE_ID";

	private $_VisitorDBS;

	public function __construct() {
		$this->_VisitorDBS = OBX_VisitorDBS::getInstance();
	}

	/**
	 * Создается строка CookieID из уникальных данных.
	 * @return string
	 */
	static public function generationCookieID () {
		return md5($_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"].microtime().mt_rand());
	}

	/**
	 * Получаем идентификатор из куков. Если нет, создаем и записываем в куки.
	 * Затем этот инентификатор возвращается.
	 * @return string
	 */
	public function getCurrentUserCookieID () {
		global $APPLICATION;
		$c_value = $APPLICATION->get_cookie(self::$visitor_cookie_name);
		if (strlen($c_value) == 0) {
			$c_value = self::generationCookieID();
			$APPLICATION->set_cookie(self::$visitor_cookie_name, $c_value);
		}
		return $c_value;
	}

	/**
	 * Добавление посетителя, с возможностью автоматического заполнения полей.
	 * @param array $arFields
	 * @param bool $reset_COOKIE_ID Флаг автоматической установки COOKIE_ID
	 * @param bool $reset_USER_ID Флаг автоматической установки USER_ID
	 * @return mixed
	 */
	public function add ($arFields = array(), $reset_COOKIE_ID = true, $reset_USER_ID = true) {
		if ($reset_COOKIE_ID) {
			$arFields["COOKIE_ID"] = $this->getCurrentUserCookieID();
		}
		if ($reset_USER_ID) {
			global $USER;
			$arFields["USER_ID"] = ($USER->IsAuthorized() ? $USER->GetID() : 0);
		}
		return $this->_VisitorDBS->add($arFields);
	}

	/**
	 * Обновление посетителя.
	 * @param $arFields
	 * @param bool $update_COOKIE_ID Если true, значение COOKIE_ID обновляется из $arFields, иначе остается прежним.
	 * @param bool $reset_USER_ID Если true, USER_ID берется из текущего пользователя, иначе из $arFields.
	 * @return mixed
	 */
	public function update ($arFields, $update_COOKIE_ID = false, $reset_USER_ID = true) {
		if (!$update_COOKIE_ID) unset($arFields["COOKIE_ID"]);
		if ($reset_USER_ID) {
			global $USER;
			$arFields["USER_ID"] = ($USER->IsAuthorized() ? $USER->GetID() : 0);
		}
		return $this->_VisitorDBS->update($arFields);
	}

	/**
	 * Удаление посетителя с указанным ID. Так же удаляются все его хиты (когда они будут реализованы).
	 * @param $ID
	 * @return bool
	 */
	public function delete ($ID) {
		return $this->_VisitorDBS->delete($ID);
	}

	/**
	 * Если у посетителя нет COOKIE_ID, он устанавливается. Затем создается запись в БД, если такого посетителя еще нет.
	 * Если посетитель есть, но у него другой USER_ID, он обновляется.
	 * Возвращает COOKIE_ID.
	 * @return string
	 */
	public function checkAddAndUpdate () {
		$cID = $this->getCurrentUserCookieID();
		$visitors = $this->_VisitorDBS->getListArray(null, array("COOKIE_ID" => $cID));
		if (count($visitors) == 0) {
			$this->add();
		} else {
			global $USER;
			if ($USER->IsAuthorized() and $visitors[0]["USER_ID"] != $USER->GetID())
				$this->update(array("ID" => $visitors[0]["ID"]));
		}
		return $cID;
	}
}


/*
 * старое
 *
 *                'SESSION_ID'            => array('V' => 'SESSION_ID'),
 *                'COOKIE_ID'                     => array('V' => 'COOKIE_ID'),
 *                'LAST_VISIT'            => array('V' => 'LAST_VISIT'),
 *                'USER_ID'                       => array('V' => 'USER_ID'), // если пользовтель уже зарегистрировался
 *                'NICKNAME'                      => array('V' => 'NICKNAME'),
 *                'FIRST_NAME'            => array('V' => 'FIRST_NAME'),
 *                'LAST_NAME'                     => array('V' => 'LAST_NAME'),
 *                'SECOND_NAME'           => array('V' => 'SECOND_NAME'),
 *                'GENDER'                        => array('V' => 'GENDER'),
 *                'EMAIL'                         => array('V' => 'EMAIL'),
 *                'PHONE'                         => array('V' => 'PHONE'),
 *                'SKYPE'                         => array('V' => 'SKYPE'),
 *                'WWW'                           => array('V' => 'WWW'),
 *                'ICQ'                           => array('V' => 'ICQ'),
 *                'FACEBOOK'                      => array('V' => 'FACEBOOK'),
 *                'VK'                            => array('V' => 'VK'),
 *                'TWITTER'                       => array('V' => 'TWITTER'),
 *                'JSON_USER_DATA'        => array('V' => 'JSON_USER_DATA'),
 *                'JSON_CONTACT'          => array('V' => 'JSON_CONTACT'),
 *                'JSON_SOCIAL'           => array('V' => 'JSON_SOCIAL'),
 *                'USER_ID'                       => array('V' => 'USER_ID'),
 */