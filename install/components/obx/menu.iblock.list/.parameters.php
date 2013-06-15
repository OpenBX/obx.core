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
if( !CModule::IncludeModule('iblock') ) return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(Array('__all__'=>GetMessage('OBXCMPP_MIL_ALL_IBLOCKS')));

$arIBlockFilter = array();
if( isset($_REQUEST['site']) ) {
	$arIBlockFilter['SITE_ID'] = $_REQUEST['site'];
}
$bAddTypeName2IBlocks = false;
if($arCurrentValues['IBLOCK_TYPE'] != '__all__') {
	$arIBlockFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
else {
	$bAddTypeName2IBlocks = true;
}

$arIBlocks=array();
$arSelectedIBlockList = array();
$db_iblock = CIBlock::GetList(Array('SORT'=>'ASC'), $arIBlockFilter);
while( $arRes = $db_iblock->Fetch() ) {
	$arIBlocks[$arRes['ID']] = (($bAddTypeName2IBlocks)?$arTypesEx[$arRes['IBLOCK_TYPE_ID']].': ':'').$arRes['NAME'];
	if( in_array($arRes['ID'], $arCurrentValues['IBLOCK_ID_LIST']) ) {
		$arSelectedIBlockList[$arRes['ID']] = $arRes;
	}
}

$arComponentParameters = array(
	'GROUPS' => array(
		'URL_TEMPLATES' => array(
			'NAME' => GetMessage("OBXCMPP_MIL_LIST_IBLOCK_PAGE_URL")
		)
	),
	'PARAMETERS' => array(
		'IBLOCK_TYPE' => Array(
			'PARENT' => 'BASE',
			'NAME'=>GetMessage('OBXCMPP_MIL_IBLOCK_TYPE'),
			'TYPE'=>'LIST',
			'VALUES'=>$arTypesEx,
			'DEFAULT'=>'catalog',
			'ADDITIONAL_VALUES'=>'N',
			'REFRESH' => 'Y',
		),
		'IBLOCK_ID_LIST' => Array(
			'PARENT' => 'BASE',
			'NAME'=>GetMessage('OBXCMPP_MIL_IBLOCK_ID_LIST'),
			'TYPE'=>'LIST',
			'VALUES'=>$arIBlocks,
			'DEFAULT'=>'1',
			'MULTIPLE'=>'Y',
			'ADDITIONAL_VALUES'=>'N',
			'REFRESH' => 'Y',
		),

	)
);

foreach($arSelectedIBlockList as $arIBlock) {
	$arComponentParameters['PARAMETERS']['IBLOCK_LIST_PAGE_URL_'.$arIBlock['ID']] = CIBlockParameters::GetPathTemplateParam(
		"BASE",
		"LIST_PAGE_URL",
		(($bAddTypeName2IBlocks)?'['.$arTypesEx[$arIBlock['IBLOCK_TYPE_ID']].'] ':'').$arIBlock['NAME'],
		"",
		"URL_TEMPLATES"
	);
}
