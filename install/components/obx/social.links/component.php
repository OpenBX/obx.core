<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!CModule::IncludeModule('obx.core')) {
	ShowError(GetMessage('OBX_CORE_MODULE_NOT_INSTALLED'));
	return;
}

$arResult['VK_IS_SET'] = false;
if( strlen($arParams['VK']) > 0 ) {
	$arResult['VK_IS_SET'] = true;
	$arResult['VK_URL'] = trim($arParams['VK']);
}
$arResult['TW_IS_SET'] = false;
if( strlen($arParams['TW']) > 0 ) {
	$arResult['TW_IS_SET'] = true;
	$arResult['TW_URL'] = trim($arParams['TW']);
}
$arResult['FB_IS_SET'] = false;
if( strlen($arParams['FB']) > 0 ) {
	$arResult['FB_IS_SET'] = true;
	$arResult['FB_URL'] = trim($arParams['FB']);
}
$arResult['YT_IS_SET'] = false;
if( strlen($arParams['YT']) > 0 ) {
	$arResult['YT_IS_SET'] = true;
	$arResult['YT_URL'] = trim($arParams['YT']);
}
$arResult['OK_IS_SET'] = false;
if( strlen($arParams['OK']) > 0 ) {
	$arResult['OK_IS_SET'] = true;
	$arResult['OK_URL'] = trim($arParams['OK']);
}

$this->IncludeComponentTemplate();