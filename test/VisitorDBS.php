<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maximum
 * Date: 26.04.13
 * Time: 16:34
 * To change this template use File | Settings | File Templates.
 */

final class OBX_Test_VisitorDBS extends OBX_Core_TestCase {

	static private $_VisitrosDBS = null;
	static private $_arVisitros = array();

	public function setUp() {
		self::$_VisitrosDBS = OBX_VisitorDBS::getInstance();
	}

	public function testAddVisitor() {
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		$_SERVER["HTTP_USER_AGENT"] = "phpunit cli version not like Gecko";
		for($i = 0; $i < 4; $i++) {
			$intNewVisitorID = self::$_VisitrosDBS->add();
			if(!$intNewVisitorID) {
				$arError = self::$_VisitrosDBS->popLastError();
				$this->assertTrue(false, $intNewVisitorID, 'Error: '.$arError['TEXT']);
				continue;
			}
			self::$_arVisitros[] = $intNewVisitorID;
		}
	}

	public function testVisitorsGetList() {
		$arVisitorsList = self::$_VisitrosDBS->getListArray(null, self::$_arVisitros);
		$this->assertGreaterThan(0, $arVisitorsList, 'Error: empty visitros list');
		foreach($arVisitorsList as &$arVisitor) {
			$this->assertArrayHasKey('ID', $arVisitor);
			$this->assertArrayHasKey('COOKIE_ID', $arVisitor);
			$this->assertArrayHasKey('USER_ID', $arVisitor);
		}
	}

	/**
	 * @depends testAddVisitor
	 */
	public function testUpdateVisitor() {
		$arVisitorsListBefore = self::$_VisitrosDBS->getListArray(null, self::$_arVisitros);
		foreach($arVisitorsListBefore as &$arVisitor) {

		}
	}

	/**
	 * @depends testUpdateVisitor
	 */
	public function testGetVisitorsList() {
		foreach(self::$_arVisitros as $intVisitorID) {
			$bSuccess = self::$_VisitrosDBS->delete($intVisitorID);
			if(!$bSuccess) {
				$arError = self::$_VisitrosDBS->popLastError();
				$this->assertTrue($bSuccess, 'Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
			}
		}
	}
}