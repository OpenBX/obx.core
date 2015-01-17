<?php
/******************************************
 ** @product OpenBX:Core Bitrix Module   **
 ** @authors                             **
 **         Maksim S. Makarov            **
 ** @license Affero GPLv3                **
 ** @mailto rootfavell@gmail.com         **
 ** @copyright 2015 DevTop               **
 ******************************************/

/**
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 * @global \CUser $USER
 */




require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

IncludeModuleLangFile(__FILE__);
if (!$USER->IsAdmin()) {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);
$APPLICATION->SetTitle(GetMessage("OBX_MARKET_HEADER"));

require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');



require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
?>