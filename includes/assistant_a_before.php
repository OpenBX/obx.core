<?php
if (isset($_REQUEST['work_start'])) {
	define("NO_AGENT_STATISTIC", true);
	define("NO_KEEP_STATISTIC", true);
}

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("main");
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm("Доступ запрещен");

CModule::IncludeModule('obx.core');