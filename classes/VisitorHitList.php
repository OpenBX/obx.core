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

/*
 * Посещения
 *  obx_visitors_hits
 *      ID (int 20)
 *      VISITOR_ID (int 18) - значение из поля ID таблицы obx_visitors
 *      DATE_HIT (datetime)
 *      SITE_ID (varchar 5)
 *      URL (text) - часть адреса после первого слеша /
 */
class VisitorHitDBS extends DBSimple
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