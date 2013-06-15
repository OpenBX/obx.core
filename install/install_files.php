<?php
$bConnectEpilog = false;
if(!defined("BX_ROOT")) {
	$bConnectEpilog = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $USER;
	if( !$USER->IsAdmin() ) return false;
}

if(!function_exists("OBX_CopyDirFilesEx")) {
	function OBX_CopyDirFilesEx($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $strExclude = "") {
		$path_from = str_replace(array("\\", "//"), "/", $path_from);
		$path_to = str_replace(array("\\", "//"), "/", $path_to);
		if(is_file($path_from) && !is_file($path_to)) {
			if( CheckDirPath($path_to) ) {
				$file_name = substr($path_from, strrpos($path_from, "/")+1);
				$path_to .= $file_name;
				return CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
			}
		}
		if( is_dir($path_from) && substr($path_to, strlen($path_to)-1) == "/" ) {
			$folderName = substr($path_from, strrpos($path_from, "/")+1);
			$path_to .= $folderName;
		}
		return CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
	}
}
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.core/install/php_interface/event.d/obx.core.debug.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/event.d/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.core/install/php_interface/event.d/obx.core.parse_ini_string.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/event.d/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.core/install/php_interface/run_event.d.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.core/install/js/obx.core", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.core/install/components/obx/layout", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/obx/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.core/install/components/obx/breadcrumb.get", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/obx/", true, true);
OBX_CopyDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/obx.core/install/components/obx/menu.iblock.list", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/obx/", true, true);
if($bConnectEpilog) require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>