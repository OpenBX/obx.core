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
 *      COOKIE_ID (varchar 60) - {IP посетителя}_{md5 хеш от уникальных данных} - главный ключ. Он же и будет храниться в куках.
 *      USER_ID (int 18) - ID авторизованного пользователя битрикс, может быть 0 если не авторизован. Может повторяться для разных COOKIE_ID
 *
 * Посещения
 *  obx_visitors_hits
 *      ID (int 20)
 *      VISITOR_ID (int 18) - значение из поля ID таблицы obx_visitors
 *      DATE_HIT (datetime)
 *      SITE_ID (varchar 5)
 *      URL (text) - часть адреса после первого слеша /
 *
 */

IncludeModuleLangFile(__FILE__);

class OBX_Visitor extends OBX_DBSimple
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
            'DUP_ADD_ID' => array(
                'TYPE' => 'E',
                'TEXT' => GetMessage('OBX_VISITORS_ERROR_DUP_ADD_ID'),
                'CODE' => 2
            ),
            'DUP_ADD_COOKIE_ID' => array(
                'TYPE' => 'E',
                'TEXT' => GetMessage('OBX_VISITORS_ERROR_DUP_ADD_COOKIE_ID'),
                'CODE' => 3
            ),
            'NOTHING_TO_DELETE' => array(
                'TYPE' => 'M',
                'TEXT' => GetMessage('OBX_VISITORS_MESSAGE_NOTHING_TO_DELETE'),
                'CODE' => 4
            ),
            'NOTHING_TO_UPDATE' => array(
                'TYPE' => 'E',
                'TEXT' => GetMessage('OBX_VISITORS_ERROR_NOTHING_TO_UPDATE'),
                'CODE' => 5
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

    static public function getCookieID () {
        return $_SERVER["REMOTE_ADDR"]."_".md5($_SERVER["HTTP_USER_AGENT"].microtime().mt_rand());
    }
}

class OBX_VisitorHit extends OBX_DBSimple
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



/*
 * старое
 *
 *              'SESSION_ID'            => array('V' => 'SESSION_ID'),
-               'COOKIE_ID'                     => array('V' => 'COOKIE_ID'),
-               'LAST_VISIT'            => array('V' => 'LAST_VISIT'),
-               'USER_ID'                       => array('V' => 'USER_ID'), // если пользовтель уже зарегистрировался
-               'NICKNAME'                      => array('V' => 'NICKNAME'),
-               'FIRST_NAME'            => array('V' => 'FIRST_NAME'),
-               'LAST_NAME'                     => array('V' => 'LAST_NAME'),
-               'SECOND_NAME'           => array('V' => 'SECOND_NAME'),
-               'GENDER'                        => array('V' => 'GENDER'),
-               'EMAIL'                         => array('V' => 'EMAIL'),
-               'PHONE'                         => array('V' => 'PHONE'),
-               'SKYPE'                         => array('V' => 'SKYPE'),
-               'WWW'                           => array('V' => 'WWW'),
-               'ICQ'                           => array('V' => 'ICQ'),
-               'FACEBOOK'                      => array('V' => 'FACEBOOK'),
-               'VK'                            => array('V' => 'VK'),
-               'TWITTER'                       => array('V' => 'TWITTER'),
-               'JSON_USER_DATA'        => array('V' => 'JSON_USER_DATA'),
-               'JSON_CONTACT'          => array('V' => 'JSON_CONTACT'),
-               'JSON_SOCIAL'           => array('V' => 'JSON_SOCIAL'),
+               'USER_ID'                       => array('V' => 'USER_ID'),
 */