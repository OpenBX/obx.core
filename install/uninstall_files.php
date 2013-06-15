<?php
$bConnectEpilog = false;
if(!defined("BX_ROOT")) {
	$bConnectEpilog = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $USER;
	if( !$USER->IsAdmin() ) return false;
}

DeleteDirFilesEx("/bitrix/php_interface/event.d/obx.core.debug.php");
DeleteDirFilesEx("/bitrix/php_interface/event.d/obx.core.parse_ini_string.php");
DeleteDirFilesEx("/bitrix/js/obx.core");
DeleteDirFilesEx("/bitrix/components/obx/layout");
DeleteDirFilesEx("/bitrix/components/obx/breadcrumb.get");
DeleteDirFilesEx("/bitrix/components/obx/menu.iblock.list");
if($bConnectEpilog) require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>