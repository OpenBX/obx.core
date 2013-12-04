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
use OBX\Core\Curl\Request;
use OBX\Core\Curl\RequestBXFile;

require_once __DIR__.'/_Request.php';

class TestRequestBXFile extends _Request {



	public function testSaveToBXFile() {
		$Request = new RequestBXFile(self::$_urlTestFiles.'/favicon.png');
		$Request->download();
		$fileID = $Request->saveToBXFile('/upload//obx.core/test/RequestBXFile');
		$this->assertTrue(($fileID > 0));
		$arFile = \CFile::GetFileArray($fileID);
		$this->assertEquals('favicon.png', $arFile['ORIGINAL_NAME']);
		$this->assertEquals('image/png', $arFile['CONTENT_TYPE']);
		$this->assertFileExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
		\CFile::DoDelete($arFile['ID']);
		$this->assertFileNotExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
	}

	public function testSaveToBXFileAfterGetContent() {
		$Request = new RequestBXFile(self::$_urlTestFiles.'/favicon.png');
		$Request->send();
		$this->assertEquals('PNG', substr($Request->getBody(), 1, 3));
		$fileID = $Request->saveToBXFile('/upload//obx.core/test/RequestBXFile');
		$this->assertTrue(($fileID > 0));
		$arFile = \CFile::GetFileArray($fileID);
		$this->assertEquals('favicon.png', $arFile['ORIGINAL_NAME']);
		$this->assertEquals('image/png', $arFile['CONTENT_TYPE']);
		$this->assertFileExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
		\CFile::DoDelete($arFile['ID']);
		$this->assertFileNotExists(self::$_docRoot.'/upload/'.$arFile['SUBDIR'].'/'.$arFile['FILE_NAME']);
	}

	public function testSaveToIBElement() {
		$Request = new RequestBXFile(self::$_urlTestFiles.'/favicon.png');
		$Request->download();
		$arTestIBlock = $this->getTestIBlock(array('CODE' => 'testRequest'));
		$obElement = new \CIBlockElement();
		$eltID = $obElement->Add(array(
			'IBLOCK_ID' => $arTestIBlock['ID'],
			'NAME' => 'test'
		));
		$this->assertFalse(empty($eltID));
		$this->assertTrue(is_numeric($eltID));
		$eltID = intval($eltID);
		$bSuccessDetail = $Request->saveToIBElement($eltID);
		$bSuccessPreview = $Request->saveToIBElement($eltID, RequestBXFile::F_IB_IMG_PREVIEW);
		$this->assertTrue($bSuccessDetail);
		$this->assertTrue($bSuccessPreview);
		$rsElement = \CIBlockElement::GetByID($eltID);
		$arElement = $rsElement->Fetch();
		$this->assertTrue(is_array($arElement));
		$this->assertEquals($eltID, intval($arElement['ID']));
		$this->assertTrue(is_numeric($arElement['PREVIEW_PICTURE']));
		$this->assertTrue(is_numeric($arElement['DETAIL_PICTURE']));
		$this->assertGreaterThan(0, intval($arElement['PREVIEW_PICTURE']));
		$this->assertGreaterThan(0, intval($arElement['DETAIL_PICTURE']));
		$arPreviewPicture = \CFile::GetFileArray($arElement['PREVIEW_PICTURE']);
		$arDetailPicture = \CFile::GetFileArray($arElement['DETAIL_PICTURE']);
		$this->assertTrue(is_array($arPreviewPicture));
		$this->assertEquals(16, $arPreviewPicture['WIDTH']);
		$this->assertEquals(16, $arPreviewPicture['HEIGHT']);
		$this->assertEquals('image/png', $arPreviewPicture['CONTENT_TYPE']);
		$this->assertTrue(is_array($arDetailPicture));
		$this->assertEquals(16, $arDetailPicture['WIDTH']);
		$this->assertEquals(16, $arDetailPicture['HEIGHT']);
		$this->assertEquals('image/png', $arDetailPicture['CONTENT_TYPE']);
		$this->assertTrue(\CIBlockElement::Delete($eltID));
	}

	public function testSaveToIBProp() {
		$RequestPng = new RequestBXFile(self::$_urlTestFiles.'/favicon.png');
		$RequestPng->download();
		$RequestJpg = new RequestBXFile(self::$_urlTestFiles.'/favicon.jpg');
		$RequestJpg->download();
		$arTestIBlock = $this->getTestIBlock(array('CODE' => 'testRequest'));
		$arTestProperty = $this->getTestIBlockProp('testRequest', array(
			'PROPERTY_TYPE' => 'F',
			'CODE' => 'GALLERY',
			'MULTIPLE' => 'Y'
		));
		$obElement = new \CIBlockElement();
		$eltID = $obElement->Add(array(
			'IBLOCK_ID' => $arTestIBlock['ID'],
			'NAME' => 'test'
		));
		$this->assertFalse(empty($eltID));
		//$eltID = 22155;
		$bSuccessPng = $RequestPng->saveToIBProp(
			$arTestIBlock['ID'],
			$eltID,
			$arTestProperty['CODE'],
			RequestBXFile::F_IB_IMG_PROP_REPLACE
		);
		$bSuccessJpg = $RequestJpg->saveToIBProp(
			$arTestIBlock['ID'],
			$eltID,
			$arTestProperty['CODE'],
			RequestBXFile::F_IB_IMG_PROP_APPEND
		);
		$this->assertTrue($bSuccessPng);
		$this->assertTrue($bSuccessJpg);
		$rsElement = \CIBlockElement::GetByID($eltID);
		$RecElement = $rsElement->GetNextElement();
		$arElement = $RecElement->GetFields();
		$arElement['PROPERTIES'] = $RecElement->GetProperties();
		$this->assertArrayHasKey($arTestProperty['CODE'], $arElement['PROPERTIES']);
		$this->assertEquals($arTestProperty['ID'], $arElement['PROPERTIES'][$arTestProperty['CODE']]['ID']);
		$this->assertEquals(2, count($arElement['PROPERTIES'][$arTestProperty['CODE']]['VALUE']));
		foreach($arElement['PROPERTIES'][$arTestProperty['CODE']]['VALUE'] as $fileID) {
			$arFile = \CFile::GetFileArray($fileID);
			$this->assertEquals(16, $arFile['WIDTH']);
			$this->assertEquals(16, $arFile['HEIGHT']);
		}
		$this->assertTrue(\CIBlockElement::Delete($eltID));
	}

}
 