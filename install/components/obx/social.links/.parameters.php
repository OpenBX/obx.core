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
if( !CModule::IncludeModule('obx.core') ) return;


$arComponentParameters = array(
	'PARAMETERS' => array(
		'TITLE' => Array(
			'PARENT' => 'BASE',
			'NAME'=>GetMessage('OBX_CMP_PRM_SOC_LINKS_TITLE'),
			'TYPE'=>'STRING',
			'DEFAULT'=>GetMessage('OBX_CMP_PRM_SOC_LINKS_TITLE_DEF'),
		),
		'VK' => Array(
			'PARENT' => 'BASE',
			'NAME'=>'VKontakte',
			'TYPE'=>'STRING',
			'DEFAULT'=>'http://vk.com/',
		),
		'TW' => Array(
			'PARENT' => 'BASE',
			'NAME'=>'Twitter',
			'TYPE'=>'STRING',
			'DEFAULT'=>'http://twitter.com/',
		),
		'FB' => Array(
			'PARENT' => 'BASE',
			'NAME'=>'Facebook',
			'TYPE'=>'STRING',
			'DEFAULT'=>'http://facebook.com/',
		),
		'YT' => Array(
			'PARENT' => 'BASE',
			'NAME'=>'Youtube',
			'TYPE'=>'STRING',
			'DEFAULT'=>'http://youtube.com/',
		),
		'OK' => Array(
			'PARENT' => 'BASE',
			'NAME'=>'Odnoklassniki',
			'TYPE'=>'STRING',
			'DEFAULT'=>'http://odnoklassniki.com/',
		),
	)
);

