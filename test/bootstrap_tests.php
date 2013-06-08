<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

define('DBPersistent', true);
$curDir = dirname(__FILE__);
$wwwRootStrPos = strpos($curDir, '/bitrix/modules/obx.core');
if( $wwwRootStrPos === false ) {
	die('Can\'t find www-root');
}

$_SERVER['DOCUMENT_ROOT'] = substr($curDir, 0, $wwwRootStrPos);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
__IncludeLang(__DIR__.'/lang/'.LANGUAGE_ID.'/bootstrap_tests.php');
global $USER;
global $DB;
// Без этого фикса почему-то не работает. Не видит это значение в include.php модуля
global $DBType;
$DBType = strtolower($DB->type);

$USER->Authorize(1);
if( !CModule::IncludeModule('iblock') ) {
	die('Module iblock not installed');
}

if( !CModule::IncludeModule('obx.core') ) {
	die('Module OBX:Core not installed');
}

abstract class OBX_Core_TestCase extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = false;
	// Код скопирован из obx.market
	// pr0n1x: 2013-01-25:
	// если переменная $backupGlobals равна true то битрикс будет косячить на каждом тесте, где есть обращение к БД
	// косяк происходит в предшествующем тесте, в котором бэкапятся гблобальные перменные.
	// Потому !в каждом! тесте надо вызывать $this->setBackupGlobals(false);
	// Или просто сразу сделать переменную в false :)
	//
	// Этот метод тож имеет какое-то значение, но и без него работало нормально
	//$this->setPreserveGlobalState(false);
	//protected $preserveGlobalState = false;


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

	protected function setUp() {

	}

	protected function getBXLangList() {
		$rsLang = CLanguage::GetList($by='sort', $sort='asc', $arLangFilter=array('ACTIVE' => 'Y'));
		$arLangList = array();
		while( $arLang = $rsLang->Fetch() ) {
			$arLangList[$arLang['ID']] = $arLang;
		}
		return $arLangList;
	}

	protected function getBXSitesArray() {
		$rsSites = CSite::GetList($by='sort', $order='desc', array(''));
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
