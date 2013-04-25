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
 *      ID (varchar 32) - md5 хеш от уникальных данных, главный ключ. Он же и будет храниться в куках.
 *      USER_ID (int 18) - ID авторизованного пользователя битрикс, может быть 0 если не авторизован. Может повторяться для разных ID
 *
 * Посещения
 *  obx_visitors_hits
 *      ID (int 20)
 *      VISITOR_ID (varchar 32) - привязка к таблице obx_visitors
 *      DATE_HIT (datetime)
 *      SITE_ID (varchar 5)
 *      URL (text) - часть адреса после первого слеша /
 *
 */

class OBX_VisitorHits extends OBX_DBSimple
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
    function __construct() {

    }
}

class OBX_Visitor extends OBX_DBSimple
{
	protected $_arTableList = array(
		'V' => 'obx_visitors'
	);
	protected $_arTableFields = array(
		'ID'				=> array('V' => 'ID'),
		'USER_ID'			=> array('V' => 'USER_ID'),
	);
	function __construct() {

	}
}