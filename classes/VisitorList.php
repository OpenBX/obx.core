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

class VisitorDBS extends DBSimple
{
	protected $_arTableList = array(
		'V' => 'obx_visitors'
	);
	protected $_arTableFields = array(
		'ID'				=> array('V' => 'ID'),
		'COOKIE_ID'			=> array('V' => 'COOKIE_ID'),
		'USER_ID'			=> array('V' => 'USER_ID'),
//		[pr0n1x] избыточно, но может пригодится
//		'SESSION_ID'		=> array('V' => 'SESSION_ID'),
//		'LAST_VISIT'		=> array('V' => 'LAST_VISIT'),
//		'USER_ID'			=> array('V' => 'USER_ID'), // если пользовтель уже зарегистрировался
//		'NICKNAME'			=> array('V' => 'NICKNAME'),
//		'FIRST_NAME'		=> array('V' => 'FIRST_NAME'),
//		'LAST_NAME'			=> array('V' => 'LAST_NAME'),
//		'SECOND_NAME'		=> array('V' => 'SECOND_NAME'),
//		'GENDER'			=> array('V' => 'GENDER'),
//		'EMAIL'				=> array('V' => 'EMAIL'),
//		'PHONE'				=> array('V' => 'PHONE'),
//		'SKYPE'				=> array('V' => 'SKYPE'),
//		'WWW'				=> array('V' => 'WWW'),
//		'ICQ'				=> array('V' => 'ICQ'),
//		'FACEBOOK'			=> array('V' => 'FACEBOOK'),
//		'VK'				=> array('V' => 'VK'),
//		'TWITTER'			=> array('V' => 'TWITTER'),
//		'JSON_USER_DATA'	=> array('V' => 'JSON_USER_DATA'),
//		'JSON_CONTACT'		=> array('V' => 'JSON_CONTACT'),
//		'JSON_SOCIAL'		=> array('V' => 'JSON_SOCIAL'),
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

	public function __check_COOKIE_ID(&$value, &$arCheckData = null) {
		if(
			! is_string($value)
			||
			! preg_match('~[a-f0-9]{32}~', $value)
		) {
			if($arCheckData !== null) {
				$this->addError(GetMessage('OBX_VISITORS_ERROR_WRONG_COOKIE_ID', 7));
			}
			return false;
		}
		return true;
	}

	function __construct()
	{
		$this->_arTableFieldsDefault = array(
			'USER_ID' => 0,
		);
		$this->_arTableFieldsCheck = array(
			'ID' => self::FLD_T_INT | self::FLD_NOT_NULL,
			'COOKIE_ID' => self::FLD_T_NO_CHECK | self::FLD_CUSTOM_CK | self::FLD_BRK_INCORR | self::FLD_REQUIRED,
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

class VisitorList extends DBSimpleStatic {}
VisitorList::__initDBSimple(VisitorDBS::getInstance());