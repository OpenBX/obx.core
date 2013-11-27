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
	protected $preserveGlobalState = false;

	/**
	 * Метод должен вернуть путь до текущей папки с тестами
	 * Для каждого свой
	 * реализация везде повторяется
	 * public function getCurDir() {
	 * 		return dirname(__FILE__);
	 * }
	 * @return string
	 */
	abstract static public function getCurDir();

	static protected $_bPathVarInit = false;
	static protected $_docRoot = '';
	static protected $_modulesDir = '';
	static protected $_arTestIBlockType = null;
	static protected $_arTestIBlocks = array();
	static protected $_arTestIBProps = array();
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

	final static protected function _initPathVar() {
		if(true !== self::$_bPathVarInit) {
			self::$_docRoot = str_replace('/bitrix/modules/obx.core/classes', '', __DIR__);
			//$_SERVER['DOCUMENT_ROOT'] = self::$_docRoot;
			self::$_modulesDir = self::$_docRoot.'/bitrix/modules';
			self::$_bPathVarInit = true;
		}
	}

	public function __construct($name = NULL, array $data = array(), $dataName = ''){
		//$this->setPreserveGlobalState(false);
		//$this->setBackupGlobals(false);
		self::_initPathVar();
		parent::__construct($name, $data, $dataName);
	}

	public function testSetMaxExecutionTime() {
		if(intval(ini_get('max_execution_time')) > 0) {
			set_time_limit(0);
		}
		$this->assertEquals(0, ini_get('max_execution_time'));
	}

	static public function includeLang($file) {
		$file = str_replace(array('\\', '//'), '/', $file);
		$fileName = substr($file, strrpos($file, '/'));
		$langFile = static::getCurDir().'/lang/'.LANGUAGE_ID.'/'.$fileName;
		if( file_exists($langFile) ) {
			__IncludeLang($langFile);
			return true;
		}
		return false;
	}

	protected function callTest($testCaseName, $testName) {
		$fileName = static::getCurDir().'/'.$testCaseName.'.php';
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
		/** @var \PHPUnit_Framework_TestCase $TestCase */
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
			$rsUser1 = \CUser::GetByLogin('__test_basket_user_1');
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
			$rsUser1 = \CUser::GetByLogin('__test_basket_user_2');
			if( $arUser2 = $rsUser1->Fetch() ) {
				$this->assertEquals('__test_basket_user_2', $arUser2['LOGIN']);
				self::$_arSomeOtherTestUser = $arUser2;
			}
			else {
				$this->fail('Error: can\'t get test user 2');
			}
		}
	}

	public function getBXLangList() {
		$rsLang = \CLanguage::GetList($by='sort', $sort='asc', $arLangFilter=array('ACTIVE' => 'Y'));
		$arLangList = array();
		while( $arLang = $rsLang->Fetch() ) {
			$arLangList[$arLang['ID']] = $arLang;
		}
		return $arLangList;
	}

	public function getBXSitesArray() {
		$rsSites = \CSite::GetList($by='sort', $order='desc', array(''));
		$arSites = array();
		while ($arSite = $rsSites->Fetch()) {
			$arSites[$arSite['LID']] = $arSite;
		}
		return $arSites;
	}
	public function getBXSitesList() {
		$arSites = $this->getBXSitesArray();
		return array_keys($arSites);
	}

	public function getTestIBlockType() {
		if(self::$_arTestIBlockType !== null) {
			return self::$_arTestIBlockType;
		}
		$testIBlockType = 'obx_test_type';
		$rsIBlockType = \CIBlockType::GetByID($testIBlockType);
		self::$_arTestIBlockType = $rsIBlockType->Fetch();
		if( !self::$_arTestIBlockType ) {
			$arIBlockTypeFields = array(
				'ID'=>$testIBlockType,
				'SECTIONS'=>'Y',
				'IN_RSS'=>'N',
				'SORT'=>1000,
				'LANG'=>Array(
					'en'=>Array(
						'NAME'=>'OpenBX: Test info blocks',
						'SECTION_NAME'=>'Sections',
						'ELEMENT_NAME'=>'Elements'
					),
					'ru'=>Array(
						'NAME'=>'OpenBX: Test info blocks',
						'SECTION_NAME'=>'Sections',
						'ELEMENT_NAME'=>'Elements'
					)
				)
			);
			$obBlockType = new \CIBlockType();
			global $DB;
			/** @global \CDatabase $DB */
			$DB->StartTransaction();
			$res = $obBlockType->Add($arIBlockTypeFields);
			if(!$res) {
				$DB->Rollback();
				$this->fail('Error: '.$obBlockType->LAST_ERROR);
			}
			else {
				$DB->Commit();
				$rsIBlockType = \CIBlockType::GetByID($testIBlockType);
				self::$_arTestIBlockType = $rsIBlockType->Fetch();
			}
		}
		return self::$_arTestIBlockType;
	}

	public function getTestIBlock($arIBlockFields, $bFailOnNonExist = false) {
		$arTestIBType = $this->getTestIBlockType();
		if( !array_key_exists('CODE', $arIBlockFields) ) {
			$this->fail('Error: can`t create ');
		}
		if( !array_key_exists('NAME', $arIBlockFields) ) {
			$arIBlockFields['NAME'] = $arIBlockFields['CODE'];
		}
		if( array_key_exists($arIBlockFields['CODE'], self::$_arTestIBlocks) ) {
			return self::$_arTestIBlocks[$arIBlockFields['CODE']];
		}
		$rsTestIBlock = \CIBlock::GetList(array(), array(
			'CODE' => $arIBlockFields['CODE'],
			'IBLOCK_TYPE_ID' => $arTestIBType['ID']
		));
		$arTestIBlock = $rsTestIBlock->Fetch();
		if(!$arTestIBlock) {
			if($bFailOnNonExist === true) {
				$this->fail('Error: infoblock "'.$arIBlockFields['CODE'].'" does not exist');
			}
			$arIBlockFieldsDef = array(
				'ACTIVE' => 'Y',
				'LIST_PAGE_URL' => '',
				'DETAIL_PAGE_URL' => '',
				'IBLOCK_TYPE_ID' => $arTestIBType['ID'],
				'SITE_ID' => $this->getBXSitesList(),
				'SORT' => 100,
				'DESCRIPTION' => 'OpenBX: infoblock for unit testing',
				'GROUP_ID' => Array('2'=>'W')
			);
			$arIBlockFields = array_merge($arIBlockFieldsDef, $arIBlockFields);
			/** @global \CDatabase $DB */
			global $DB;
			$DB->StartTransaction();
			$obIBlock = new \CIBlock();
			$newIBlockID = $obIBlock->Add($arIBlockFields);
			if(!$newIBlockID) {
				$this->fail('Error: '.$obIBlock->LAST_ERROR);
				$DB->Rollback();
			}
			else {
				$DB->Commit();
				$rsTestIBlock = \CIBlock::GetList(array(), array(
					'CODE' => $arIBlockFields['CODE'],
					'IBLOCK_TYPE_ID' => $arTestIBType['ID']
				));
				$arTestIBlock = $rsTestIBlock->Fetch();
				if(!$arTestIBlock) {
					$this->fail('Error: Can`t get just created infoblock');
				}
			}
		}
		self::$_arTestIBlocks[$arIBlockFields['CODE']] = $arTestIBlock;
		return self::$_arTestIBlocks[$arIBlockFields['CODE']];
	}

	/**
	 * @param int|string|array $iblockCode
	 * @param $arPropFields
	 */
	public function getIBlockProp($iblockCode, $arPropFields) {
		if(is_array($iblockCode)) {
			$arIBlockFields = $iblockCode;
		}
		elseif(is_numeric($iblockCode)) {
			$arIBlockFields = array('ID' => intval($iblockCode));
		}
		else {
			$arIBlockFields = array('CODE' => $iblockCode);
		}
		$arTestIBlock = $this->getTestIBlock($arIBlockFields, true);

		if(!array_key_exists('CODE', $arPropFields)) {
			$this->fail('Error: Infoblock property code is empty');
		}



		if(!array_key_exists('NAME', $arPropFields) || empty($arPropFields['NAME']) ) {
			$arIBlockFields['NAME'] = $arIBlockFields['CODE'];
		}
		if(array_key_exists('PROPERTY_TYPE', $arIBlockFields) && $arIBlockFields['PROPERTY_TYPE'] == 'L') {

		}
		//$arIBlockFields['']
		$arPropFieldsDef = array(
			'IBLOCK_ID' => $arTestIBlock['ID'],
			'ACTIVE' => 'Y',
			'SORT' => '100',
			'PROPERTY_TYPE' => 'S',
		);
	}
}
