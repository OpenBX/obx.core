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
use OBX\Core\Test\TestCase;
use OBX\Core\Xml\Parser as XmlParser;
use OBX\Core\Xml\Exceptions\ParserError as XmlParserError;

class XmlParserAttr extends TestCase {
	public function getCurDir() {
		return dirname(__FILE__);
	}

	public function getXmlFilePath() {
		return array(
			array(
				$this->getCurDir().'/data/yml_catalog_example.xml'
			)
		);
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @expectedException \OBX\Core\Xml\Exceptions\ParserError
	 * @expectedExceptionCode \OBX\Core\Xml\Exceptions\ParserError::E_ADD_ATTR_ON_EXISTS_TBL
	 */
	public function testAddAttrAfterTableCreation($filePath) {
		$Parser = new XmlParser($filePath);
		$Parser->dropTempTables();
		$Parser->createTempTables();
		$Parser->addAttribute('some');
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @depends testAddAttrAfterTableCreation
	 */
	public function testAddAttr($filePath) {
		$Parser = new XmlParser($filePath);
		$Parser->dropTempTables();
		$Parser->addAttribute('available', 'offer', 4, true, true);
		$Parser->addAttribute('id:common_id', false, false, true, true);
		$Parser->createTempTables();
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @depends testAddAttr
	 */
	public function testGetAttributes($filePath) {
		$Parser = new XmlParser($filePath);
		$this->assertTrue($Parser->isTempTableCreated());
		$arAttributes = $Parser->getAttributes();
		$this->assertNotEmpty($arAttributes);
		foreach($arAttributes as &$arAttr) {
			$this->assertArrayHasKey('NAME', $arAttr);
			$this->assertArrayHasKey('NODE', $arAttr);
			$this->assertArrayHasKey('COL_NAME', $arAttr);
			$this->assertArrayHasKey('AUTO', $arAttr);
			$this->assertArrayHasKey('INDEX', $arAttr);
			$this->assertArrayHasKey('DEPTH_LEVEL', $arAttr);
			if( $arAttr['NAME'] == 'available' ) {
				$this->assertEquals(4, $arAttr['DEPTH_LEVEL']);
				$this->assertEquals('available', $arAttr['COL_NAME']);
				$this->assertTrue($arAttr['INDEX']);
				$this->assertTrue($arAttr['AUTO']);
			}
			if( $arAttr['NAME'] == 'id' ) {
				$this->assertFalse((bool) $arAttr['DEPTH_LEVEL']);
				$this->assertEquals('id', $arAttr['COL_NAME']);
				$this->assertTrue($arAttr['INDEX']);
				$this->assertTrue($arAttr['AUTO']);
			}
		}
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @depends testGetAttributes
	 */
	public function testIndexWithAttr($filePath) {
		/** @var XmlParser $Parser */
		/** @global \CDatabase $DB */
		global $DB;
		$Parser = new XmlParser($filePath);
		$Parser->indexTempTables();
		$this->assertTrue($DB->IndexExists($Parser->getTempTableName(), array('ATTR_id')));
		$this->assertTrue($DB->IndexExists($Parser->getTempTableName(), array('ATTR_available')));
	}


	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @depends testAddAttr
	 * @depends testGetAttributes
	 * @depends testIndexWithAttr
	 */
	public function testParser($filePath){
		$ITERATION = array();
		$Parser = new XmlParser($filePath);
		//$Parser->setReadSize(100);
		$Parser->setReadTimeLimit(1);
		$prevFilePosition = 0;
		while( !$Parser->readXML($ITERATION) ) {
			$this->assertGreaterThanOrEqual($prevFilePosition, $ITERATION['file_position']);
			$prevFilePosition = $ITERATION['file_position'];
		}
		$Parser->indexTempTables();
	}
}