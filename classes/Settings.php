<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Settings;
use OBX\Core\CMessagePoolDecorator;

interface ISettings {
	public function __construct($moduleID, $settingsID, $arSettings);
	public function getSettingModuleID();
	public function getSettingsID();
	public function syncSettings();
	public function getSettings();
	public function getOption($optionCode, $bReturnOptionArray = false);
	public function saveSettings($arSettings);
	public function getOptionInput($optionCode, $arAttributes = array());
	public function saveSettingsRequestData();
}

class Settings implements ISettings {
	const SETT_INPUT_NAME_CONTAINER = 'obx_settings';
	protected $_settingsModuleID = null;
	protected $_settingsID = null;
	protected $_bSettingsInit = false;

	/**
	 * @var array
	 * array(
	 * 		'OPT_ID' => array(
	 * 			'NAME' => GetMessage(...)
	 * 			'DESCRIPTION' => GetMessage(...)
	 * 			'VALUE' => ...
	 * 		)
	 * 	....
	 * )
	 */
	protected $_arSettings = array();


	public function __construct($moduleID, $settingsID, $arSettings) {
		if( !IsModuleInstalled($moduleID) ) {
			return;
		}
		$this->_settingsModuleID = $moduleID;
		if (!preg_match('~^[a-zA-Z\_][a-zA-Z0-9\_]*$~', $settingsID)) {
			return;
		}
		$this->_settingsID = $settingsID;
		foreach($arSettings as $optionCode => &$arOption) {
			if (!preg_match('~^[a-zA-Z0-9\_]*$~', $optionCode)) {
				continue;
			}
			if(
				array_key_exists('NAME', $arOption) && !empty($arOption['NAME'])
				&& array_key_exists('TYPE', $arOption)
				&& (
					$arOption['TYPE'] == 'STRING'
					|| $arOption['TYPE'] == 'TEXT'
					|| $arOption['TYPE'] == 'CHECKBOX'
				)
			) {
				if($arOption['TYPE'] == 'CHECKBOX') {
					$arOption['VALUE'] = strtoupper(substr($arOption['VALUE'], 0, 1));
					$arOption['VALUE'] = ($arOption['VALUE'] !== 'N')?'Y':'N';
				}
				$this->_arSettings[$optionCode] = array(
					'NAME' => $arOption['NAME'],
					'DESCRIPTION' => (array_key_exists('DESCRIPTION', $arOption)?$arOption['DESCRIPTION']:''),
					'TYPE' => $arOption['TYPE'],
					'VALUE' => (array_key_exists('VALUE', $arOption)?$arOption['VALUE']:'')
				);
				if( array_key_exists('INPUT_ATTRIBUTES', $arOption) ) {
					// TODO: Добавить в настройки параметры вывода инпутов по умолчанию
				}
			}
		}
	}
	final protected function __clone() {}

	public function copySettings(ISettings $Settings){

	}

	public function getSettingModuleID() {
		if(empty($this->_settingsModuleID)) {
			return 'obx.core';
		}
		return $this->_settingsModuleID;
	}

	/**
	 * ! Желательно переопределять этот метод
	 * @return string
	 */
	public function getSettingsID() {
		return $this->_settingsID;
	}

	public function syncSettings() {
		foreach($this->_arSettings as $optionCode => &$arOption) {
			if( strlen($arOption['NAME']) > 0 ) {
				if( !array_key_exists('VALUE', $arOption) ) $arOption['VALUE'] = '';
				$arOption['VALUE'] = \COption::GetOptionString($this->getSettingModuleID(), $this->getSettingsID().'_'.$optionCode, $arOption['VALUE']);
			}
			else {
				unset($this->_arSettings[$optionCode]);
			}
		}
	}

	/**
	 * @return array
	 */
	public function getSettings() {
		$this->syncSettings();
		return $this->_arSettings;
	}

	/**
	 * Получить (значение) опцию(ии) настроек
	 * @param $optionCode - код опции
	 * @param bool $bReturnOptionArray - вернуть полный массив описания опции, иначе вернет только значение
	 * @return null
	 */
	public function getOption($optionCode, $bReturnOptionArray = false) {
		if( array_key_exists($optionCode, $this->_arSettings) ) {
			if(!$bReturnOptionArray) {
				if( !array_key_exists('VALUE', $this->_arSettings[$optionCode]) ) {
					return null;
				}
				return $this->_arSettings[$optionCode]['VALUE'];
			}
			else {
				return $this->_arSettings[$optionCode];
			}
		}
		return null;
	}

	/**
	 * @param $arSettings
	 */
	public function saveSettings($arSettings) {
		foreach ($arSettings as $optionCode => &$optionValue) {
			if( array_key_exists($optionCode, $this->_arSettings) ) {
				if( is_array($optionValue) ) {
					if( array_key_exists('VALUE', $optionValue) ) {
						$optionValue = $optionValue['VALUE'];
					}
					else {
						$optionValue = null;
					}
				}
				\COption::SetOptionString(
					$this->getSettingModuleID(),
					$this->getSettingsID().'_'.$optionCode,
					$optionValue,
					$this->_arSettings['DESCRIPTION']
				);
				$this->_arSettings[$optionCode]['VALUE'] = $optionValue;
			}
		}
	}

	public function getOptionInput($optionCode, $arAttributes = array()) {
		$arOption = $this->getOption($optionCode, true);
		if( empty($arOption) ) {
			return '';
		}
		switch ($arOption['TYPE']) {
			case 'STRING':
				echo '<input type="text"'
					.$this->_getOptionInputName($optionCode)
					.' value="'.$arOption['VALUE'].'"'
					.$this->_implodeInputAttributes($arAttributes).' />';
				break;
			case 'CHECKBOX':
				echo '<input type="checkbox"'
						.$this->_getOptionInputName($optionCode)
						.' value="Y"'
						.(($arOption['VALUE']=='Y')?' checked="checked"':'')
						.$this->_implodeInputAttributes($arAttributes).' />'
				;
				break;
			case 'TEXT':
				echo '<textarea'
					.$this->_getOptionInputName($optionCode)
					.$this->_implodeInputAttributes($arAttributes)
					.'>'.$arOption['VALUE'].'</textarea>';
			default:
				break;
		}
	}
	protected function _implodeInputAttributes(&$arAttributes) {
		$attrString = '';
		foreach($arAttributes as $attr => &$value) {
			$attrString .= ' '.$attr.'="'.htmlspecialchars($value).'"';
		}
	}

	protected function _getOptionInputName($optionCode) {
		return ' name="'.static::SETT_INPUT_NAME_CONTAINER.'['.$this->getSettingModuleID().']['.$this->getSettingsID().']['.$optionCode.']"';
	}

	public function saveSettingsRequestData() {
		if( !array_key_exists(static::SETT_INPUT_NAME_CONTAINER, $_REQUEST) ) {
			return ;
		}
		if( !array_key_exists($this->getSettingModuleID(), $_REQUEST[static::SETT_INPUT_NAME_CONTAINER]) ) {
			return ;
		}
		if( !array_key_exists($this->getSettingsID(), $_REQUEST[static::SETT_INPUT_NAME_CONTAINER][$this->getSettingModuleID()]) ) {
			return ;
		}
		$arRequestSettings = $_REQUEST[static::SETT_INPUT_NAME_CONTAINER][$this->getSettingModuleID()][$this->getSettingsID()];
		$arSettings = array();

		foreach($this->_arSettings as $optionCode => &$arOption) {
			if( array_key_exists($optionCode, $arRequestSettings) ) {
				$arSettings[$optionCode] = $arRequestSettings[$optionCode];
			}
			elseif( $arOption['TYPE'] == 'CHECKBOX' ) {
				if($arRequestSettings[$optionCode] === null || strtoupper($arRequestSettings[$optionCode]) != 'Y') {
					$arSettings[$optionCode] = 'N';
				}
				else {
					$arSettings[$optionCode] = 'Y';
				}
			}
		}
		$this->saveSettings($arSettings);
	}
}

interface ITab {
	public function showTabContent();
	public function showTabScripts();
	public function saveTabData();
	public function showMessages($colspan = -1);
	public function showWarnings($colspan = -1);
	public function showErrors($colspan = -1);
}

abstract class ATab extends CMessagePoolDecorator implements ITab {
	static protected $_arTabInstances = array();

	/**
	 * @param $tabClassName
	 * @return null | self
	 */
	static final public function GetTabController(ITab $tabClassName = null) {
		if($tabClassName === null) {
			$tabClassName = get_called_class();
		}
		if (!preg_match(
			'~^'
			.'(?:[a-zA-Z\_][a-zA-Z0-9\_]*){1}'
			.'(?:'
			.'(?:\\\\[a-zA-Z\_][a-zA-Z0-9\_]*){1}'
			.')*'
			.'$~', $tabClassName)) {
			return null;
		}
		if (!class_exists($tabClassName)) {
			return null;
		}
		/**
		 * @var self $TabContentObject
		 */
		if (empty(self::$_arTabInstances[$tabClassName])) {
			$TabContentObject = new $tabClassName;
			if ($TabContentObject instanceof self) {
				self::$_arTabInstances[$tabClassName] = $TabContentObject;
			}
			else {
				return null;
			}
		}
		return self::$_arTabInstances[$tabClassName];
	}

	abstract public function showTabContent();
	abstract public function showTabScripts();
	abstract public function saveTabData();

	public function showMessages($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arMessagesList = $this->getMessages();
		if (count($arMessagesList) > 0) {
			?>
			<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arMessagesList as $arMessage) {
					ShowNote($arMessage['TEXT']);
				}
				?></td>
			</tr><?
		}
	}

	public function showWarnings($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arWarningsList = $this->getWarnings();
		if (count($arWarningsList) > 0) {
			?>
			<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arWarningsList as $arWarning) {
					ShowNote($arWarning['TEXT']);
				}
				?></td>
			</tr><?
		}
	}

	public function showErrors($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = $this->listTableColumns;
		}
		$arErrorsList = $this->getErrors();
		if (count($arErrorsList) > 0) {
			?>
			<tr>
			<td<?if ($colspan > 1): ?> colspan="<?=$colspan?>"<? endif?>><?
				foreach ($arErrorsList as $arError) {
					ShowError($arError['TEXT']);
				}
				?></td>
			</tr><?
		}
	}
}



class Tab extends ATab /*implements Settings*/{
	protected $_Settings = null;

	public function __construct(ISettings $Settings) {

	}

	public function showTabContent() {

	}

	public function showTabScripts() {

	}

	public function saveTabData() {

	}
}
