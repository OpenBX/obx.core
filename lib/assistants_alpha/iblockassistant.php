<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Mikhail Medvedev aka r3c130n
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Salerman
 */

namespace OBX\Core\Assistants_Alpha;

/**
 * Class IblockAssistant
 * @package OBX\Core\Assistant
 *
 * @example
 * <code>
 * <?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/obx.core/includes/assistant_a_before.php');
 *
 * class ElCodeUpd extends OBX\Core\Assistant\IblockAssistant {
 *      public function doAction ($arItem, $ob) {
 *          // execute actions
 *          return $arItem['ID'];
 *      }
 * }
 *
 * ElCodeUpd::getFilterValues();
 *
 * $assistant = new ElCodeUpd("Обработка инфоблоков", $_REQUEST['IBLOCK_ID']);
 *
 * if (isset($_REQUEST['ACTIVE'])) {
 *      $assistant->setFilter(Array('ACTIVE' => $_REQUEST['ACTIVE']));
 * }
 *
 * $assistant->addFilterInput(ElCodeUpd::FILTER_INPUT_TYPE_SELECT, 'IBLOCK_ID', 'Из какого ИБ', 3, Array('3' => 'Клиенты', '4' => 'Команда'));
 *
 * $assistant->addFilterInput(ElCodeUpd::FILTER_INPUT_TYPE_RADIO, 'ACTIVE', 'Показывать: ', "Y", Array('Y' => "активных", 'N' => "неактивных"));
 *
 * $assistant->Run();
 *
 * require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/obx.core/includes/assistant_a_after.php');
 * </code>
 */
class IBlockAssistant extends BasicAssistant {

	protected $message = "Обработал элемент с ID: #ID#";

	public $iblock_id = 0;

	public function __construct ($title, $iblockId) {
		global $APPLICATION;

		$this->iblock_id = intVal ($iblockId);

		\CModule::IncludeModule("iblock");

		if ($_REQUEST['work_start'] && check_bitrix_sessid()) {
			$this->lastID = intVal ($_REQUEST['lastid']);
		}

		$this->arSelect = array(
			"ID",
			"NAME",
			"CODE",
			"IBLOCK_ID",
		);

		$this->title = $title;
		$APPLICATION->SetTitle( $this->title );
	}

	public function setSelect ($arSelect) {
		$this->arSelect = $arSelect;
	}

	protected function getCurrentPercent () {
		$qty = $this->getQty();
		if ($qty == 0) {
			$this->setMessage("Элементов с заданным фильтром не найдено.");
			$this->currentPercent = 100;
		} else {
			$this->currentPercent = round( 100 * $this->getLeftBorderQty() / $qty, 2);
		}
		return $this->currentPercent;
	}

	public function executeStep () {
		$arFilter = array(
			"IBLOCK_ID" => $this->iblock_id,
			"INCLUDE_SUBSECTIONS" => "Y",
			'>ID' => $_REQUEST["lastid"]
		);

		$arSort = Array('ID' => 'ASC');

		$res = \CIBlockElement::GetList($arSort, array_merge ($arFilter, $this->arFilter), false, array("nTopCount" => $this->limit), $this->arSelect);
		while ($ob = $res->GetNextElement()) {
			$arItem = $ob->GetFields();

			$this->setMessage("Обработал элемент [" . $arItem['ID'] . "] " . $arItem['NAME']);

			$id = $this->doAction($arItem, $ob);
			if ($id > 0) {
				$this->lastID = $id;
			}

		}
	}

	public function doAction ($arItem, $ob) {
		return $arItem['ID'];
	}

	public function setFilter ($arFilter) {
		$this->arFilter = $arFilter;
	}

	public function getLeftBorderQty () {
		$rsLeftBorder = \CIBlockElement::GetList(array("ID" => "ASC"), array_merge(array("IBLOCK_ID" => $this->iblock_id, "<=ID" => $this->lastID), $this->arFilter));
		$this->leftBorderCnt = $rsLeftBorder->SelectedRowsCount();

		return $this->leftBorderCnt;
	}

	public function getQty () {
		$rsAll = \CIBlockElement::GetList(array("ID" => "ASC"), array_merge(array("IBLOCK_ID" => $this->iblock_id), $this->arFilter));
		$this->qty = $rsAll->SelectedRowsCount();

		return $this->qty;
	}

}