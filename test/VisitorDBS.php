<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 **         Artem P. Morozov  aka tashiro     **
 ** @License GPLv3                            **
 ** @mailto rootfavell@gmail.com              **
 ** @mailto tashiro@yandex.ru                 **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

final class OBX_Test_VisitorDBS extends OBX_Core_TestCase {

	static private $_VisitorsDBS = null;
	static private $_arVisitorsData = array();

	public static function setUpBeforeClass() {
		self::$_VisitorsDBS = OBX_VisitorDBS::getInstance();
		self::$_arVisitorsData = array(
			array(
				"COOKIE_ID" => "e4c83f93cbd45cbb1e487d0cc9b928aa",
				"USER_ID" => "",
			),
			array(
				"COOKIE_ID" => "fe14bf2a370d5d5695dae3bbd8201df4",
				"USER_ID" => "2",
			),
			array(
				"COOKIE_ID" => "de07963ee1f08c199a952eb55f8549da",
				"USER_ID" => "",
			),
			array(
				"COOKIE_ID" => "a76cc2b4be2c4d7f6a3fe9b8fdb19ca4",
				"USER_ID" => "1",
			),
		);
	}

	public function testAddVisitor() {
		foreach (self::$_arVisitorsData as $k => $arVisitor) {
			$intNewVisitorID = self::$_VisitorsDBS->add($arVisitor);
			if(!$intNewVisitorID) {
				$arError = self::$_VisitorsDBS->popLastError();
				$this->fail("ID: ".$intNewVisitorID."; Error: ".$arError['TEXT']);
				continue;
			}
			self::$_arVisitorsData[$k]["ID"] = $intNewVisitorID;
		}
	}

	/**
	 * @depends testAddVisitor
	 */
	public function testVisitorsGetList() {
		$arKeys = array("ID" => array());
		foreach (self::$_arVisitorsData as $arV) $arKeys["ID"][] = $arV["ID"];
		$arVisitorsList = self::$_VisitorsDBS->getListArray(null, $arKeys);
		$this->assertEquals(count($arKeys["ID"]), count($arVisitorsList), 'Error: not match count visitors list');
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
		self::$_arVisitorsData[0]["COOKIE_ID"] = "5ac83f93cb845cbb1e480d0cc9b92821";
		self::$_arVisitorsData[0]["USER_ID"] = "3";
		self::$_arVisitorsData[1]["COOKIE_ID"] = "9ac43f73cb359cbb1e196d0cc3b92827";
		self::$_arVisitorsData[1]["USER_ID"] = "1";
		for ($i = 0; $i < 2; $i++)
			if (! self::$_VisitorsDBS->update(self::$_arVisitorsData[$i])) {
				$arError = self::$_VisitorsDBS->popLastError();
				$this->fail("ID: ".self::$_arVisitorsData[$i]["ID"]."; Error: ".$arError['TEXT']);
			}
	}

	/**
	 * @depends testUpdateVisitor
	 */
	public function testGetVisitorsList() {
		foreach(self::$_arVisitorsData as $arV)
			if(!self::$_VisitorsDBS->delete($arV["ID"])) {
				$arError = self::$_VisitorsDBS->popLastError();
				$this->fail('Error: '.$arError['TEXT'].'; code: '.$arError['CODE']);
			}
	}
}