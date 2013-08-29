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

class XmlParserTest extends TestCase {
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
	 * @param string $filePath
	 * @expectedException \OBX\Core\Xml\Exceptions\ParserError
	 * @expectedExceptionCode \OBX\Core\Xml\Exceptions\ParserError::YML_FILE_NOT_FOUND
	 * @dataProvider getXmlFilePath
	 */
	public function testWrongFile($filePath) {
		$Parser = new XmlParser($filePath.'_');
	}

	/**
	 * @param string $filePath
	 * @throws \OBX\Core\Xml\Exceptions\ParserError
	 * @expectedException \OBX\Core\Xml\Exceptions\ParserError
	 * @expectedExceptionCode \OBX\Core\Xml\Exceptions\ParserError::YML_FILE_CANT_OPEN
	 * @dataProvider getXmlFilePath
	 */
	public function testWrongFilePrivileges($filePath) {
		@chmod($filePath, 0000);
		try {
			$Parser = new XmlParser($filePath);
		}
		catch(XmlParserError $e) {}
		@chmod($filePath, 0644);
		throw $e;
	}

	/**
	 * @param $filePath
	 * @dataProvider getXmlFilePath
	 */
	public function setRightPrivileges($filePath) {
		@chmod($filePath, 0644);
	}

	/**
	 * @param $filePath
	 * @expectedException \OBX\Core\Xml\Exceptions\ParserError
	 * @expectedExceptionCode \OBX\Core\Xml\Exceptions\ParserError::TMP_TBL_EXISTS
	 * @dataProvider getXmlFilePath
	 */
	public function testCreateTableExists($filePath) {
		$Parser = new XmlParser($filePath);
		$Parser->dropTempTables();
		$Parser->createTempTables();
		$Parser->createTempTables();
	}

	/**
	 * @param string $filePath
	 * @dataProvider getXmlFilePath
	 */
	public function testParser($filePath) {
		// Что бы сделать пошаговость в публичке надо использовать $NS через регистрацию данныз в:
		//bitrix/modules/main/tools.php
		//FormDecode();
		$ITERATION = array();
		$Parser = new XmlParser($filePath);
		//$Parser->setReadSize(100);
		$Parser->setReadTimeLimit(1);
		$Parser->dropTempTables();
		$Parser->createTempTables();
		$prevFilePosition = 0;
		while( !$Parser->readYML($ITERATION) ) {
			$this->assertGreaterThanOrEqual($prevFilePosition, $ITERATION['file_position']);
			$prevFilePosition = $ITERATION['file_position'];
		}
		//$Parser->dropTempTables();
	}

	public function _testBitrixXMLParser() {
		\CModule::IncludeModule('iblock');
		$BXParser = new \CIBlockXMLFile('dvt_yml_import_temp');
		$BXParser->CreateTemporaryTables();
		$NS = array();
		$fp = fopen($this->getCurDir().'/data/yml_catalog_example.xml', 'r');
		$BXParser->ReadXMLToDatabase($fp, $NS, 0, 10240);
	}
}