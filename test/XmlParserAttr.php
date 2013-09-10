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
	 */
	public function testAddAttr($filePath) {
		$Parser = new XmlParser($filePath);
		$Parser->dropTempTables();
		$Parser->addAttribute('available', 'offer', 4);
		$Parser->createTempTables();
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 */
	public function testgGetAttributes($filePath) {
		$Parser = new XmlParser($filePath);
		$this->assertTrue($Parser->isTempTableCreated());
		$arAttributes = $Parser->getAttributes();
		$this->assertNotEmpty($arAttributes);
	}
}