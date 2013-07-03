<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Test;
abstract class TestCase extends \PHPUnit_Framework_TestCase {
	protected $backupGlobals = false;
	// pr0n1x: 2013-01-25:
	// если переменная $backupGlobals равна true то битрикс будет косячить на каждом тесте, где есть обращение к БД
	// косяк происходит в предшествующем тесте, в котором бэкапятся гблобальные перменные.
	// Потому !в каждом! тесте надо вызывать $this->setBackupGlobals(false);
	// Или просто сразу сделать переменную в false :)
	//
	// Этот метод тож имеет какое-то значение, но и без него работало нормально
	//$this->setPreserveGlobalState(false);
	//protected $preserveGlobalState = false;

	/**
	 * Метод должен вернуть путь до текущей папки с тестами
	 * Для каждого свой
	 * реализация везде повторяется
	 * public function getCurDir() {
	 * 		return dirname(__FILE__);
	 * }
	 * @return string
	 */
	abstract public function getCurDir();


	/**
	 * Идентификатор тестового пользователя
	 * @var int
	 * @static
	 * @access protected
	 */
	static protected $_arTestUser = array();
	/**
	 * Идентификатор ещё одого тестового пользователя
	 * @var int
	 * @static
	 * @access protected
	 */
	static protected $_arSomeOtherTestUser = array();

	static public function includeLang($file) {
		$file = str_replace(array('\\', '//'), '/', $file);
		$fileName = substr($file, strrpos($file, '/'));
		$langFile = __DIR__.'/lang/'.LANGUAGE_ID.'/'.$fileName;
		if( file_exists($langFile) ) {
			__IncludeLang($langFile);
			return true;
		}
		return false;
	}

	protected function callTest($testCaseName, $testName) {
		$fileName = dirname(__FILE__).'/'.$testCaseName.'.php';
		if( !file_exists($fileName) ) {
			$this->fail('ERROR: Can\'t invoke test. File not found');
		}
		require_once $fileName;
		if( substr($testCaseName, 0, 1) == '_' ) {
			$className = $testCaseName.'Lib';
		}
		if( strlen($className)<1 || !class_exists($className) ) {
			$this->fail('ERROR: Can\'t invoke test. TestCase Class not found');
		}
		$TestCase = new $className;
		if( strlen($testName)<1 || !method_exists($TestCase, $testName) ) {
			$this->fail('ERROR: Can\'t invoke test. TestCase Method not found');
		}
		$TestCase->setTestResultObject($this->getTestResultObject());
		$TestCase->setName($testName);
		$TestCase->runTest();
	}

	public function _getTestUser() {
		global $USER;
		$arFields = Array(
			'NAME'              => GetMessage('OBX_MARKET_TEST_USER_1_FNAME'),
			'LAST_NAME'         => GetMessage('OBX_MARKET_TEST_USER_1_LNAME'),
			'EMAIL'             => 'test@test.loc',
			'LID'               => 'ru',
			'ACTIVE'            => 'Y',
			'GROUP_ID'          => array(1,2),
			'PASSWORD'          => '123456',
			'CONFIRM_PASSWORD'  => '123456',
		);
		$rsUser1 = \CUser::GetByLogin('__test_basket_user_1');
		$rsUser2 = \CUser::GetByLogin('__test_basket_user_2');
		if( $arUser1 = $rsUser1->Fetch() ) {
			self::$_arTestUser = $arUser1;
		}
		else {
			$user = new \CUser;
			$arFields['LOGIN'] = '__test_basket_user_1';
			$ID = $user->Add($arFields);
			$this->assertGreaterThan(0, $ID, 'Error: can\'t create test user 1. text: '.$user->LAST_ERROR);
			$rsUser1 = CUser::GetByLogin('__test_basket_user_1');
			if( $arUser1 = $rsUser1->Fetch() ) {
				$this->assertEquals('__test_basket_user_1', $arUser1['LOGIN']);
				self::$_arTestUser = $arUser1;
			}
			else {
				$this->fail('Error: can\'t get test user 1');
			}
		}
		if( $arUser2 = $rsUser2->Fetch() ) {
			self::$_arSomeOtherTestUser = $arUser2;
		}
		else {
			$user = new \CUser;
			$arFields['LOGIN'] = '__test_basket_user_2';
			$ID = $user->Add($arFields);
			$this->assertGreaterThan(0, $ID, 'Error: can\'t create test user 2. text: '.$user->LAST_ERROR);
			$rsUser1 = CUser::GetByLogin('__test_basket_user_2');
			if( $arUser2 = $rsUser1->Fetch() ) {
				$this->assertEquals('__test_basket_user_2', $arUser2['LOGIN']);
				self::$_arSomeOtherTestUser = $arUser2;
			}
			else {
				$this->fail('Error: can\'t get test user 2');
			}
		}
	}

	protected function getBXLangList() {
		$rsLang = \CLanguage::GetList($by='sort', $sort='asc', $arLangFilter=array('ACTIVE' => 'Y'));
		$arLangList = array();
		while( $arLang = $rsLang->Fetch() ) {
			$arLangList[$arLang['ID']] = $arLang;
		}
		return $arLangList;
	}

	protected function getBXSitesArray() {
		$rsSites = \CSite::GetList($by='sort', $order='desc', array(''));
		$arSites = array();
		while ($arSite = $rsSites->Fetch()) {
			$arSites[$arSite['LID']] = $arSite;
		}
		return $arSites;
	}
	protected function getBXSitesList() {
		$arSites = $this->getBXSitesArray();
		return array_keys($arSites);
	}

}
