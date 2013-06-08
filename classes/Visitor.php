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
 * Внимание! Необходимо учитывать, что у нескольких посетителей с разным COOKIE_ID может быть одинаковый USER_ID.
 * Это возможно, если пользователь очищал куки и снова зашел на сайт, получив новый COOKIE_ID, а затем авторизовался.
 */
class Visitor extends CMessagePoolDecorator
{
	const VISITOR_COOKIE_NAME = "OBX_VISITOR_COOKIE_ID";

	/**
	 * @var VisitorDBS
	 * @access protected
	 */
	protected $_VisitorDBS;

	protected $_arFields = array();
	protected $_bDataIsOK = true;

	/**
	 * @param array $arFields
	 */
	public function __construct($arFields = array()) {
		global $USER;
		$this->_VisitorDBS = VisitorDBS::getInstance();
		$this->_resetFields();
		$arVisitor = array();
		$cookieID = null;
		// [lzv] таким образом проверять условия - сложно для понимания. Код должен быть таким, что бы его было легко поддерживать.
		// лучше сделать на обычных if else.
		/*switch(true) {
			case (!is_array($arFields)):
				break;
			case array_key_exists('ID', $arFields):
				$arVisitor = $this->_VisitorDBS->getByID($arFields['ID']);
				if( !empty($arVisitor) ) break;
			case array_key_exists('USER_ID', $arFields):
				$arVisitorsList = $this->_VisitorDBS->getListArray(null, array('USER_ID' => $arFields['USER_ID']));
				if( !empty($arVisitorsList) ) {
					$arVisitor = $arVisitorsList[0];
					break;
				}
			case array_key_exists('COOKIE_ID', $arFields):
				$arVisitorsList = $this->_VisitorDBS->getListArray(null, array('COOKIE_ID' => $arFields['COOKIE_ID']));
				$cookieID = $arFields['COOKIE_ID'];
				if( !empty($arVisitorsList) ) {
					$arVisitor = $arVisitorsList[0];
					break;
				}
		}//*/

		// [lzv] Если я правильно понял код выше, то в этом виде гораздо понятней. И строк меньше :)
		if (is_array($arFields)) {
			if (array_key_exists('ID', $arFields)) {
				$arVisitor = $this->_VisitorDBS->getByID($arFields['ID']);
			}
			if (empty($arVisitor) and array_key_exists('USER_ID', $arFields)) {
				$arVisitorsList = $this->_VisitorDBS->getListArray(null, array('USER_ID' => $arFields['USER_ID']));
				if( !empty($arVisitorsList) ) $arVisitor = $arVisitorsList[0];
			}
			if (empty($arVisitor) and array_key_exists('COOKIE_ID', $arFields)) {
				$arVisitorsList = $this->_VisitorDBS->getListArray(null, array('COOKIE_ID' => $arFields['COOKIE_ID']));
				$cookieID = $arFields['COOKIE_ID'];
				if( !empty($arVisitorsList) ) $arVisitor = $arVisitorsList[0];
			}
		}

		if( !empty($arVisitor) ) {
			$this->_arFields = $arVisitor;
		}
		else {
			$bAddNewVisitor = true;
			if($USER->IsAuthorized()) {
				$arVisitor['USER_ID'] = $USER->GetID();
				$arVisitorsList = $this->_VisitorDBS->getListArray(null, array('USER_ID' => $arVisitor['USER_ID']));
				if( !empty($arVisitorsList) ) {
					$this->_arFields = $arVisitorsList[0];
					$bAddNewVisitor = false;
				}
				else {
					$this->_arFields['USER_ID'] = $arVisitor['USER_ID'];
				}
			}
			else {
				/*
				 * [lzv]
				 * $cookieID будет не null, только если в параметрах к конструктору прибыл массив только с одним
				 * элементом COOKIE_ID, и при этом по этому COOKIE_ID не было найдено записи.
				 * В остальных случаях $cookieID будет равно null.
				*/
				if( $cookieID !== null && $this->_VisitorDBS->__check_COOKIE_ID($cookieID) ) {
					$arVisitor['COOKIE_ID'] = $cookieID;
				}
				else {
					$arVisitor['COOKIE_ID'] = $this->getCurrentUserCookieID();
				}
				$arVisitorsList = $this->_VisitorDBS->getListArray(null, array('COOKIE_ID' => $arVisitor['COOKIE_ID']));
				if( !empty($arVisitorsList) ) {
					$this->_arFields = $arVisitorsList[0];
					$bAddNewVisitor = false;
				}
				else {
					$this->_arFields['COOKIE_ID'] = $arVisitor['COOKIE_ID'];
				}
			}
			if($bAddNewVisitor) {
				$this->_arFields['ID'] = $this->_VisitorDBS->add($this->_arFields);
				if($this->_arFields['ID'] < 1) {
					$arError = $this->_VisitorDBS->popLastError('ARRAY');
					$this->addError($arError['TEXT'], $arError['CODE']);
					$this->_bDataIsOK = false;
				}
			}
		}
	}

	protected function _resetFields() {
		$this->_arFields = array(
			'ID' => null,
			'COOKIE_ID' => null,
			'USER_ID' => null
		);
	}

	public function getFields($key = null) {
		if( $key !== null ) {
			if( array_key_exists($key, $this->_arFields) ) {
				return $this->_arFields[$key];
			}
			else {
				return null;
			}
		}
		return $this->_arFields;
	}

	/**
	 * Создается строка CookieID из уникальных данных.
	 * @return string
	 */
	static public function generationCookieID () {
		if( !array_key_exists('REMOTE_ADDR', $_SERVER) ) {
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		}
		if( !array_key_exists('HTTP_USER_AGENT', $_SERVER) ) {
			$_SERVER['HTTP_USER_AGENT'] = 'local test user agent';
		}
		return md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].microtime().mt_rand());
	}

	/**
	 * Получаем идентификатор из куков. Если нет, создаем и записываем в куки.
	 * Затем этот инентификатор возвращается.
	 * @return string
	 */
	public function getCurrentUserCookieID() {
		/**
		 * var CMain $APPLICATION
		 */
		global $APPLICATION;
		$c_value = $APPLICATION->get_cookie(self::VISITOR_COOKIE_NAME);
		if (strlen($c_value) == 0) {
			$c_value = self::generationCookieID();
			$APPLICATION->set_cookie(self::VISITOR_COOKIE_NAME, $c_value);
		}
		return $c_value;
	}

	// [lzv] Если парметр null, метод ничего не возвращает! Нужно добавить return в нужных местах.
	public function checkAuth($userID = null) {
		global $USER;
		// [lzv] мой вариант. Переменная $bFetchUser только запутывает.
		if (($userID = intval($userID)) <= 0) {
			$userID = $USER->GetID();
		} else {
			$rsUser = CUser::GetByID($userID);
			if( !($arUser = $rsUser->GetNext()) ) {
				// TODO: Добавить языковый вывод ошибки
				$this->addWarning('Can\'t check user auth. User not found');
				return false;
			}
		}
		return $userID; /* [lzv] или тут просто return true? */

/*
		$bFetchUser = true;
		if($userID == null) {
			$userID = $USER->GetID();
			$bFetchUser = false;
		}
		if($bFetchUser) {
			$rsUser = CUser::GetByID($userID);
			if( !($arUser = $rsUser->GetNext()) ) {
				// TODO: Добавить языковый вывод ошибки
				$this->addWarning('Can\'t check user auth. User not found');
				return false;
			}
		}//*/
	}
}


