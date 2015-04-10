<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Test;
use OBX\Core\DBSimple\Record;
use OBX\Market\BasketItem;
use OBX\Market\BasketItemDBS;

class ActiveRecordUse extends TestCase
{
	const _DIR_ = __DIR__;

	public function testIncludeOBXMarket() {
		$this->assertTrue(
			\CModule::IncludeModule('obx.market'),
			'Module obx.market not installed'
		);
	}

	/**
	 * @expectedException \OBX\Core\Exceptions\DBSimple\RecordError
	 * @expectedExceptionCode \OBX\Core\Exceptions\DBSimple\RecordError::E_GET_WRONG_FIELD
	 * @expectedExceptionMessage BASKET_ID
	 */
	public function testGetUndefinedField() {
		$result = BasketItem::getList(null, null, null, null, array('ID'));
		$record = $result->fetchRecord();
		/** @noinspection PhpUndefinedFieldInspection */
		$record->BASKET_ID;
	}

	/**
	 * @expectedException \OBX\Core\Exceptions\DBSimple\RecordError
	 * @expectedExceptionCode \OBX\Core\Exceptions\DBSimple\RecordError::E_READ_NO_IDENTITY_FIELD
	 */
	public function testTryToGetRecordWithoutSetPkOrUnique() {
		$result = BasketItem::getList(null, null, null, null, array('BASKET_ID'));
		$record = $result->fetchRecord();
	}

	public function autoLoadByUniqueIndex() {
		$result = BasketItem::getList(null, null, null, null, array('BASKET_ID', 'PRODUCT_ID', 'PRICE_ID'));
		$record = $result->fetchRecord();
		$result = BasketItem::getList(null, null, null, null, array('BASKET_ID', 'PRODUCT_ID', 'PRICE_ID'));
		$arResult = $result->Fetch();
		$this->assertTrue(isset($record->BASKET_ID));
		$this->assertTrue(isset($record->PRODUCT_ID));
		$this->assertTrue(isset($record->PRICE_ID));
		$this->assertArrayHasKey('BASKET_ID', $arResult);
		$this->assertArrayHasKey('PRODUCT_ID', $arResult);
		$this->assertArrayHasKey('PRICE_ID', $arResult);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($arResult['BASKET_ID'], $record->BASKET_ID);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($arResult['BASKET_ID'], $record->PRODUCT_ID);
		/** @noinspection PhpUndefinedFieldInspection */
		$this->assertEquals($arResult['BASKET_ID'], $record->PRICE_ID);
	}

	public function testLazyLoadByPrimaryKey() {
		$result = BasketItem::getList(null, null, null, null, array('ID'));
		$record = $result->fetchRecord(true);
		/** @noinspection PhpUndefinedFieldInspection */
		$itemID = $record->ID;
		/** @noinspection PhpUndefinedFieldInspection */
		$basketID = $record->BASKET_ID; // lazy load of basket ID
		$arItemWithBasketId = BasketItem::getByID($itemID, array('ID', 'BASKET_ID'));
		$this->assertArrayHasKey('ID', $arItemWithBasketId);
		$this->assertGreaterThan(0, $arItemWithBasketId['ID']);
		$this->assertArrayHasKey('BASKET_ID', $arItemWithBasketId);
		$this->assertGreaterThan(0, $arItemWithBasketId['BASKET_ID']);
		$this->assertEquals($arItemWithBasketId['ID'], $itemID);
		$this->assertEquals($arItemWithBasketId['BASKET_ID'], $basketID); // check value of lazy loaded value
	}

	public function testLazyLoadByUniqueIndex() {
		$result = BasketItem::getList(null, null, null, null, array('BASKET_ID', 'PRODUCT_ID', 'PRICE_ID'));
		$record = $result->fetchRecord(true);
		/** @noinspection PhpUndefinedFieldInspection */
		$itemID = $record->ID;
		/** @noinspection PhpUndefinedFieldInspection */
		$basketID = $record->BASKET_ID; // lazy load of basket ID
		$arItemWithBasketId = BasketItem::getByID($itemID, array('ID', 'BASKET_ID'));
		$this->assertArrayHasKey('ID', $arItemWithBasketId);
		$this->assertGreaterThan(0, $arItemWithBasketId['ID']);
		$this->assertArrayHasKey('BASKET_ID', $arItemWithBasketId);
		$this->assertGreaterThan(0, $arItemWithBasketId['BASKET_ID']);
		$this->assertEquals($arItemWithBasketId['ID'], $itemID);
		$this->assertEquals($arItemWithBasketId['BASKET_ID'], $basketID); // check value of lazy loaded value
	}
}

