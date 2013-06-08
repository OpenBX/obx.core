<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

final class OBX_Test_Visitor extends OBX_Core_TestCase {

	static private $_Visitors;
	static private $_arVisitorsData = array();
	static private $_arVisitorsIDs = array();

	public static function setUpBeforeClass() {
		self::$_Visitors = new OBX_Visitor;
	}

	public function testAddVisitor() {
		global $USER;
		if ($USER->IsAuthorized()) $USER->Logout();
		for ($i = 0; $i < 4; $i++) {
			$intNewVisitorID = self::$_Visitors->add();
			if (!$intNewVisitorID) {
				$this->fail("Visitor add error");
				continue;
			}
			self::$_arVisitorsIDs[] = $intNewVisitorID;
			if ($i == 1) {
				$USER->Authorize(1);
				if (!$USER->IsAuthorized()) $this->fail("No bitrix user with ID 1");
			}
		}
	}

	/**
	 * @depends testAddVisitor
	 */
	public function testVisitorsGetList() {
		$arVisitorsList = self::$_Visitors->getListArray(array("ID" => "ASC"), self::$_arVisitorsIDs);
		$this->assertEquals(count(self::$_arVisitorsIDs), count($arVisitorsList), 'Error: not match count visitors');
		foreach($arVisitorsList as $k => &$arVisitor) {
			$this->assertArrayHasKey('ID', $arVisitor);
			$this->assertArrayHasKey('COOKIE_ID', $arVisitor);
			$this->assertEquals(32, strlen($arVisitor["COOKIE_ID"]), 'Error: not valid COOKIE_ID');
			$this->assertArrayHasKey('USER_ID', $arVisitor);
			$this->assertEquals(($k <= 1 ? 0 : 1), $arVisitor["USER_ID"], 'Error: not match USER_ID');
			self::$_arVisitorsData[$arVisitor["ID"]] = $arVisitor;
		}
	}

	/**
	 * @depends testVisitorsGetList
	 */
	public function testUpdateVisitor() {
		global $USER;
		if ($USER->IsAuthorized()) $USER->Logout();
		$USER->Authorize(2);
		if (!$USER->IsAuthorized()) $this->fail("No bitrix user with ID 2");
		foreach (self::$_arVisitorsData as $arVisitor)
			if (!self::$_Visitors->update(array("ID" => $arVisitor["ID"]))) {
				$arError = self::$_Visitors->popLastError();
				$this->fail("Update visitor error: ".$arError['TEXT']);
			}
		$arVisitorsList = self::$_Visitors->getListArray(array("ID" => "ASC"), self::$_arVisitorsIDs);
		$this->assertEquals(count(self::$_arVisitorsData), count($arVisitorsList), 'Error: not match count visitors');
		foreach($arVisitorsList as &$arVisitor) {
			$this->assertEquals($arVisitor["COOKIE_ID"], self::$_arVisitorsData[$arVisitor["ID"]]["COOKIE_ID"], 'Error: not match COOKIE_ID after update');
			$this->assertEquals(2, $arVisitor["USER_ID"], 'Error: not change USER_ID after authorize');
		}
	}

	/**
	 * @depends testAddVisitor
	 */
	public function testGetVisitorsList() {
		foreach (self::$_arVisitorsIDs as $vID)
			if (!self::$_Visitors->delete($vID)) {
				$arError = self::$_Visitors->popLastError();
				$this->fail("Delete visitor error: ".$arError['TEXT']);
			}
	}
}