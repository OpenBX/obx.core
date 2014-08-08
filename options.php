<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

use OBX\Core\Settings\AdminPage as SettingsAdminPage;
use OBX\Core\Settings\Tab as SettingsTab;

/** @global \CUser $USER */
if(!$USER->IsAdmin())return;
if(!CModule::IncludeModule('obx.core')) return;

IncludeModuleLangFile(__FILE__);

?>
	<style type="text/css" rel="stylesheet">
		#obx_core_settings_lesscss_edit_table td.adm-detail-content-cell-l {
			width: 50%;
		}
	</style>
<?

$ModuleSettings = new SettingsAdminPage('OpenBXCoreModuleOptions');

$arOptions = array(
	'PRODUCTION_READY' => array(
		'NAME' => GetMessage('OBX_CORE_SETTINGS_LESSCSS_PROD_READY'),
		'TYPE' => 'CHECKBOX',
		'VALUE' => 'N',
		'INPUT_ATTR' => array(),
		'SORT' => 100,
		'GROUP' => 'OLD_API'
	)
);

/** @noinspection PhpDynamicAsStaticMethodCallInspection */
$rsSiteList = CSite::GetList($by='SORT', $order='ASC');
$iLessCssSitesSort = 110;
while($arSite = $rsSiteList->Fetch()) {
	$arOptions['PROD_READY_'.$arSite['ID']] = array(
		'NAME' => GetMessage('OBX_CORE_SETTINGS_LESSCSS__FOR_').' "'.$arSite['NAME'].'['.$arSite['ID'].']"',
		'TYPE' => 'CHECKBOX',
		'VALUE' => 'N',
		'INPUT_ATTR' => array(),
		'SORT' => $iLessCssSitesSort++,
		'GROUP' => 'LESS_CSS_4_SITES'
	);
}

$ModuleSettings->addTab(new SettingsTab(
	'obx.core',
	'LESSCSS',
	array(
		'DIV' => 'obx_core_settings_lesscss',
		'TAB' => GetMessage('OBX_CORE_SETTINGS_TAB_LESSCSS'),
		'ICON' => 'settings_currency',
		'TITLE' => GetMessage('OBX_MARKET_SETTINGS_TITLE_LESSCSS'),
		'GROUPS' => array(
			'OLD_API' => GetMessage('OBX_CORE_SETTINGS_LESSCSS_PROD_READY_OLD_API'),
			'LESS_CSS_4_SITES' => GetMessage('OBX_CORE_SETTINGS_LESSCSS_PROD_READY'),
		),
	),
	$arOptions
));

if($ModuleSettings->checkSaveRequest()) {
	$ModuleSettings->save();
}
if($ModuleSettings->checkRestoreRequest()) {
	$ModuleSettings->restoreDefaults();
}
$ModuleSettings->show();
?>