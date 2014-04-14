<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('obx.core')) {
    ShowError(GetMessage('OBX_CORE_MODULE_NOT_INSTALLED'));
    return;
}
$arIgnoreParams = array('TITLE',"CACHE_TYPE");
foreach ($arParams as $id => $val) {
    if (strpos($id, "~") === false && !in_array($id,$arIgnoreParams)) {
        if (strlen(trim($val)) > 0) {
            $arResult[$id] = trim($val);
        }
    }
}
$this->IncludeComponentTemplate();