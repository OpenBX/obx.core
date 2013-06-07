<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @License GPLv3                            **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


if( !CModule::IncludeModule('obx.core') ) {
	ShowError(GetMessage('OBX_CORE_IS_NOT_INSTALLED'));
	return;
}

$this->IncludeComponentTemplate();
?>