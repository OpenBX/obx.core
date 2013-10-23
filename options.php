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

$ModuleSettings = new SettingsAdminPage('LessCssSettings');
$ModuleSettings->addTab(new SettingsTab(
	'obx.core',
	'LESSCSS',
	array(
		'DIV' => 'obx_core_settings_lesscss',
		'TAB' => GetMessage('OBX_CORE_SETTINGS_TAB_LESSCSS'),
		'ICON' => 'settings_currency',
		'TITLE' => GetMessage('OBX_MARKET_SETTINGS_TITLE_LESSCSS'),
	),
	array(
		'PRODUCTION_READY' => array(
			'NAME' => GetMessage('OBX_CORE_SETTINGS_LESSCSS_PROD_READY'),
			'TYPE' => 'CHECKBOX',
			'VALUE' => 'N',
			'INPUT_ATTR' => array(),
		)
	)
));

if($ModuleSettings->checkRequest()) {
	$ModuleSettings->save();
}
$ModuleSettings->show();
?>