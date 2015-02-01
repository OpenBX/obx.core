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
use OBX\Core\Xml\Parser as XmlParser;

class XmlParserAttr extends TestCase {
	const _DIR_ = __DIR__;

	public function getXmlFilePath() {
		return array(
			array(
				static::_DIR_.'/data/yml_catalog_example.xml'
			)
		);
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @expectedException \OBX\Core\Exceptions\Xml\ParserError
	 * @expectedExceptionCode \OBX\Core\Exceptions\Xml\ParserError::E_WRONG_ATTR_NAME
	 */
	public function testAddAttrWrongName($filePath) {
		$Parser = new XmlParser($filePath);
		$Parser->dropTempTables();
		$Parser->addAttribute('123', 'offer', 3, true, true);
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @expectedException \OBX\Core\Exceptions\Xml\ParserError
	 * @expectedExceptionCode \OBX\Core\Exceptions\Xml\ParserError::E_ATTR_EXISTS
	 */
	public function testAddExistsAttr($filePath) {
		$Parser = new XmlParser($filePath);
		$Parser->dropTempTables();
		$Parser->addAttribute('some', 'offer', 3, true, true);
		$Parser->addAttribute('some', 'offer', 3, true, true);
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @expectedException \OBX\Core\Exceptions\Xml\ParserError
	 * @expectedExceptionCode \OBX\Core\Exceptions\Xml\ParserError::E_ADD_ATTR_ON_EXISTS_TBL
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
		$Parser->addAttribute('available:offer_avail', 'offer', 3, true, true);
		$Parser->addAttribute('id:common_id', false, false, true, true);
		$Parser->addAttribute('id:offer_id', 'offer', 3, true, true);
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
			if( $arAttr['COL_NAME'] == 'offer_avail' ) {
				$this->assertEquals(3, $arAttr['DEPTH_LEVEL']);
				$this->assertEquals('available', $arAttr['NAME']);
				$this->assertTrue($arAttr['INDEX']);
				$this->assertTrue($arAttr['AUTO']);
			}
			if( $arAttr['COL_NAME'] == 'common_id' ) {
				$this->assertFalse((bool) $arAttr['DEPTH_LEVEL']);
				$this->assertEquals('id', $arAttr['NAME']);
				$this->assertTrue($arAttr['INDEX']);
				$this->assertTrue($arAttr['AUTO']);
			}
			if( $arAttr['COL_NAME'] == 'offer_id' ) {
				$this->assertEquals(3, $arAttr['DEPTH_LEVEL']);
				$this->assertEquals('id', $arAttr['NAME']);
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
		$this->assertTrue($DB->IndexExists($Parser->getTempTableName(), array('ATTR_common_id')));
		$this->assertTrue($DB->IndexExists($Parser->getTempTableName(), array('ATTR_offer_avail')));
		$this->assertTrue($DB->IndexExists($Parser->getTempTableName(), array('ATTR_offer_id')));
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

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @depends testParser
	 */
	public function testGetOffersByAttrOfferID($filePath) {
		$Parser = new XmlParser($filePath);
		$rsNodes = $Parser->getList(array(), array('ATTR'=>array('!offer_id' => null)));
		while($arNode = $rsNodes->Fetch()) {
			$this->assertEquals('offer', $arNode['NAME']);
		}
	}
	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 * @depends testParser
	 */
	public function testGetNodesbyCommonID($filePath) {
		$Parser = new XmlParser($filePath);
		$rsNodes = $Parser->getList(array(), array('ATTR' => array('!offer_id' => null)));
		while($arNode = $rsNodes->Fetch()) {
			$this->assertTrue(
				($arNode['NAME'] == 'offer' || $arNode['NAME'] == 'category' || $arNode['NAME'] == 'currency')
			);
		}
	}
}