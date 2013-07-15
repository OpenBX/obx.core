<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

global $MESS;
require $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/obx.core/includes/'.LANGUAGE_ID.'/cmp_lang_desc.php';

$arComponentDescription = array(
	'NAME' => GetMessage('OBX_CMP_SERV_SOC_LINKS_NAME'),
	'DESCRIPTION' => GetMessage('OBX_CMP_SERV_SOC_LINKS_DESCRIPTION'),
	//'ICON' => '/images/menu_ext.gif',
	'CACHE_PATH' => 'Y',
	'PATH' => array(
		'ID' => 'service',
		'SORT' => 1000,
		'CHILD' => array(
			'ID' => 'obx.social',
			'NAME' => GetMessage('OBX_CMP_SERV_SOC_GRP_NAME'),
		),
	),
);
