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

final class OBX_Test_Visitor extends OBX_Core_TestCase {

	static private $_Visitors;
	static private $_arVisitorsData = array();

	public function testAddVisitor() {
		self::$_Visitors = new OBX_Visitor;
	}

	/**
	 * @depends testAddVisitor
	 */
	public function testVisitorsGetList() {

	}

	/**
	 * @depends testAddVisitor
	 */
	public function testUpdateVisitor() {

	}

	/**
	 * @depends testUpdateVisitor
	 */
	public function testGetVisitorsList() {

	}
}