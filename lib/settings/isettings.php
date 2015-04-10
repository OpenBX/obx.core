<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core\Settings;

interface ISettings {
	function getSettingModuleID();
	function getSettingsID();
	function syncSettings();
	function syncOption($optionCode);
	function getSettings();
	function getOption($optionCode, $bReturnOptionArray = false);
	function saveSettings($arSettings);
	function restoreDefaults();
	function getOptionInput($optionCode, $arAttributes = array());
	function saveSettingsRequestData();
}