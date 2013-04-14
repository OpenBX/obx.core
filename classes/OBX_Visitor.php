<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @License GPLv3                            **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

class OBX_Visitor extends OBX_DBSimple {
	protected $_arTableList = array(
		'V' => 'obx_visitors'
	);
	protected $_arTableFields = array(
		'ID'				=> array('V' => 'ID'),
		'SESSION_ID'		=> array('V' => 'SESSION_ID'),
		'COOKIE_ID'			=> array('V' => 'COOKIE_ID'),
		'LAST_VISIT'		=> array('V' => 'LAST_VISIT'),
		'USER_ID'			=> array('V' => 'USER_ID'), // если пользовтель уже зарегистрировался
		'NICKNAME'			=> array('V' => 'NICKNAME'),
		'FIRST_NAME'		=> array('V' => 'FIRST_NAME'),
		'LAST_NAME'			=> array('V' => 'LAST_NAME'),
		'SECOND_NAME'		=> array('V' => 'SECOND_NAME'),
		'GENDER'			=> array('V' => 'GENDER'),
		'EMAIL'				=> array('V' => 'EMAIL'),
		'PHONE'				=> array('V' => 'PHONE'),
		'SKYPE'				=> array('V' => 'SKYPE'),
		'WWW'				=> array('V' => 'WWW'),
		'ICQ'				=> array('V' => 'ICQ'),
		'FACEBOOK'			=> array('V' => 'FACEBOOK'),
		'VK'				=> array('V' => 'VK'),
		'TWITTER'			=> array('V' => 'TWITTER'),
		'JSON_USER_DATA'	=> array('V' => 'JSON_USER_DATA'),
		'JSON_CONTACT'		=> array('V' => 'JSON_CONTACT'),
		'JSON_SOCIAL'		=> array('V' => 'JSON_SOCIAL'),
	);
	function __construct() {

	}
}