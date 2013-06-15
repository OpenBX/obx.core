<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

if( $this->StartResultCache() ) {
	if( !CModule::IncludeModule('iblock') ) {
		$this->AbortResultCache();
	}
	else {
		$arIBlockListFilter = array(
			'ID' => $arParams['IBLOCK_ID_LIST'],
			'ACTIVE' => 'Y'
		);
		$rsIBlockList = CIBlock::GetList(array('SORT' => 'ASC'), $arIBlockListFilter);
		$arResult['IBLOCK_LIST'] = array();
		while( $arIBlock = $rsIBlockList->Fetch() ) {
			$listPageUrl = $arIBlock['LIST_PAGE_URL'];
			if(array_key_exists('IBLOCK_LIST_PAGE_URL_'.$arIBlock['ID'], $arParams)) {
				$arParams['IBLOCK_LIST_PAGE_URL_'.$arIBlock['ID']] = trim($arParams['IBLOCK_LIST_PAGE_URL_'.$arIBlock['ID']]);
				if( strlen($arParams['IBLOCK_LIST_PAGE_URL_'.$arIBlock['ID']])>0 ) {
					$listPageUrl = $arParams['IBLOCK_LIST_PAGE_URL_'.$arIBlock['ID']];
				}
			}

			$listPageUrl = str_replace(
				array(
					'#SITE_DIR#',
					'#SERVER_NAME#',
					'#IBLOCK_TYPE_ID#',
					'#IBLOCK_ID#',
					'#IBLOCK_CODE#',
					'#IBLOCK_EXTERNAL_ID#',
					'//'
				),
				array(
					$arIBlock['LANG_DIR'],
					$arIBlock['SERVER_NAME'],
					$arIBlock['IBLOCK_TYPE_ID'],
					$arIBlock['ID'],
					$arIBlock['CODE'],
					$arIBlock['EXTERNAL_ID'],
					'/'
				),
				$listPageUrl
			);

			$arResult['IBLOCK_LIST'][] = array(
				'ID' => $arIBlock['ID'],
				'CODE' => $arIBlock['CODE'],
				'NAME' => $arIBlock['NAME'],
				'IBLOCK_TYPE_ID' => $arIBlock['IBLOCK_TYPE_ID'],
				'LIST_PAGE_URL' => $listPageUrl,
				'LID' => $arIBlock['LID'],
				'LANG_DIR' => $arIBlock['LANG_DIR'],
				'SERVER_NAME' => $arIBlock['SERVER_NAME'],
				'EXTERNAL_ID' => $arIBlock['EXTERNAL_ID']
			);
		}
		$this->EndResultCache();
	}
}


$arMenuLinks = array();

$menuIndex = 0;
foreach($arResult['IBLOCK_LIST'] as $arIBlock) {
	$arMenuLinks[$menuIndex++] = array(
		$arIBlock['NAME'],
		$arIBlock['LIST_PAGE_URL'],
		array(),
		array(),
	);
}

return $arMenuLinks;