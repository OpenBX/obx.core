<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core\Settings;
use OBX\Core\MessagePoolDecorator;
use OBX\Core\IMessagePool;

IncludeModuleLangFile(__FILE__);





class Settings extends MessagePoolDecorator implements ISettings {
	const SETT_INPUT_NAME_CONTAINER = 'obx_settings';

	const E_VALIDATION_FAILED = 1;

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


	public function __construct($moduleID, $settingsID, $arSettings = array()) {
		if( !IsModuleInstalled($moduleID) ) {
			throw new \ErrorException('Can\'t create Settings object. Wrong moduleID');
		}
		$this->_settingsModuleID = $moduleID;
		if (!preg_match('~^[a-zA-Z\_][a-zA-Z0-9\_]*$~', $settingsID)) {
			throw new \ErrorException('Can\'t create Settings object. Wrong settingsID');
		}
		$this->_settingsID = $settingsID;
		foreach($arSettings as $optionCode => &$arOption) {
			$this->_addOption($optionCode, $arOption);
		}
		$this->syncSettings();
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
			$this->_syncOption($optionCode, $arOption);
		}
	}

	public function syncOption($optionCode) {
		if( array_key_exists($optionCode, $this->_arSettings) ) {
			$arOption = &$this->_arSettings[$optionCode];
			$this->_syncOption($optionCode, $arOption);
		}
	}
	protected function _syncOption(&$optionCode, &$arOption) {
		if( strlen($arOption['NAME']) > 0 ) {
			if( !array_key_exists('VALUE', $arOption) ) $arOption['VALUE'] = '';
			$arOption['VALUE'] = \COption::GetOptionString(
				$this->getSettingModuleID(),
				$this->getSettingsID().'_'.$optionCode,
				$arOption['VALUE']
			);
		}
		else {
			unset($this->_arSettings[$optionCode]);
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
	 * @param $optionCode
	 * @param $arOption
	 * @return $this
	 */
	public function addOption($optionCode, $arOption) {
		return $this->_addOption($optionCode, $arOption);
	}

	public function __validatorNotEmpty($optionCode, &$arOption, Settings $Settings) {
		if(!array_key_exists('VALUE', $arOption) || strlen($arOption['VALUE'])<1) {
			$Settings->addError(GetMessage('OBX_CORE_SETTINGS_VALIDATION_NOT_EMPTY', array(
				'#OPTION#' => '"'.$arOption['NAME'].'" ('.$optionCode.')'
			)), self::E_VALIDATION_FAILED);
			return false;
		}
		return true;
	}
	public function __validatorBlank($optionCode, &$arOption, Settings $Settings) {
		return true;
	}
	static public function __sortSettings(array &$A, array &$B) {
		if ($A['SORT'] == $B['SORT']) {
			return 0;
		}
		return ($A['SORT'] < $B['SORT']) ? -1 : 1;
	}
	protected function _addOption(&$optionCode, &$arOption) {
		if (!preg_match('~^[a-zA-Z0-9\_]*$~', $optionCode)) {
			throw new \ErrorException('Wrong option code');
		}
		if( !array_key_exists('NAME', $arOption) && empty($arOption['NAME']) ) {
			throw new \ErrorException('Option name does not set');
		}
		if( !array_key_exists('TYPE', $arOption) || !(
				$arOption['TYPE'] == 'STRING'
				|| $arOption['TYPE'] == 'PASSWORD'
				|| $arOption['TYPE'] == 'TEXT'
				|| $arOption['TYPE'] == 'CHECKBOX'
				|| $arOption['TYPE'] == 'LIST'
			)
		) {
			throw new \ErrorException('Option type incorrect');
		}
		$defaultValidator = array($this, '__validatorBlank');
		if( !array_key_exists('REQUIRED', $arOption) ) {
			$arOption['REQUIRED'] = 'N';
		}
		if( 'N' !== $arOption['REQUIRED'] && false !== $arOption['REQUIRED']) {
			$arOption['REQUIRED'] = 'Y';
			$defaultValidator = array($this, '__validatorNotEmpty');
		}
		if($arOption['TYPE'] == 'CHECKBOX') {
			$arOption['VALUE'] = strtoupper(substr($arOption['VALUE'], 0, 1));
			$arOption['VALUE'] = ($arOption['VALUE'] !== 'N')?'Y':'N';
			$defaultValidator = array($this, '__validatorBlank');
		}
		if(!array_key_exists('CHECK_FUNC', $arOption)) {
			$arOption['CHECK_FUNC'] = $defaultValidator;
		}
		elseif(!is_callable($arOption['CHECK_FUNC'])) {
			$arOption['CHECK_FUNC'] = $defaultValidator;
		}
		if(!array_key_exists('VALUE', $arOption)) {
			$arOption['VALUE'] = '';
		}
		// Перед первой синхронизацией VALUE задает значение по умолчанию DEFAULT
		$arOption['DEFAULT'] = $arOption['VALUE'];
		if(!array_key_exists('SORT', $arOption)) {
			$arOption['SORT'] = 100;
		}
		$arOption['SORT'] = intval($arOption['SORT']);

		$this->_arSettings[$optionCode] = array(
			'NAME' => $arOption['NAME'],
			'DESCRIPTION' => (array_key_exists('DESCRIPTION', $arOption)?trim($arOption['DESCRIPTION']):''),
			'HINT' => (array_key_exists('HINT', $arOption)?trim($arOption['HINT']):''),
			'TYPE' => $arOption['TYPE'],
			'VALUE' => $arOption['VALUE'],
			'DEFAULT' => $arOption['DEFAULT'],
			'REQUIRED' => $arOption['REQUIRED'],
			'CHECK_FUNC' => $arOption['CHECK_FUNC'],
			'SORT' => $arOption['SORT']
		);
		uasort($this->_arSettings, array(__CLASS__, '__sortSettings'));
		if($arOption['TYPE'] == 'LIST') {
			if( !array_key_exists('VALUES', $arOption)
				|| !is_array($arOption['VALUES'])
				|| empty($arOption['VALUES'])
			) {
				throw new \ErrorException('Option list values have not set');
			}
			else {
				$this->_arSettings[$optionCode]['VALUES'] = $arOption['VALUES'];
			}
		}
		$this->_arSettings[$optionCode]['INPUT_ATTR'] = null;
		if( array_key_exists('INPUT_ATTR', $arOption) && !empty($arOption['INPUT_ATTR']) ) {
			$this->_arSettings[$optionCode]['INPUT_ATTR'] = array();
			foreach($arOption['INPUT_ATTR'] as $attr => $attrValue) {
				$this->_arSettings[$optionCode]['INPUT_ATTR'][$attr] = $attrValue;
			}
		}
		return $this;
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
	 * @return bool
	 */
	public function saveSettings($arSettings) {
		$bAllSuccess = true;
		foreach ($arSettings as $optionCode => &$optionValue) {
			$funcValidator = null;
			if( array_key_exists($optionCode, $this->_arSettings) ) {
				if( is_array($optionValue) ) {
					if( array_key_exists('CHECK_FUNC', $optionValue) && is_callable($optionValue['CHECK_FUNC'])) {
						$funcValidator = $optionValue['CHECK_FUNC'];
					}
					if( array_key_exists('VALUE', $optionValue) ) {
						$optionValue = $optionValue['VALUE'];
					}
					else {
						$optionValue = null;
					}
				}
				$arOption = $this->_arSettings[$optionCode];
				if($funcValidator !== null) {
					$arOption['CHECK_FUNC'] = $funcValidator;
				}
				$arOption['OLD_VALUE'] = $arOption['VALUE'];
				$arOption['VALUE'] = $optionValue;
				if( false === call_user_func_array($arOption['CHECK_FUNC'], array($optionCode, &$arOption, $this)) ) {
					$bAllSuccess = false;
					continue;
				}
				\COption::SetOptionString(
					$this->getSettingModuleID(),
					$this->getSettingsID().'_'.$optionCode,
					$arOption['VALUE'],
					$this->_arSettings['DESCRIPTION']
				);
				$this->_arSettings[$optionCode]['VALUE'] = $optionValue;
			}
		}
		return $bAllSuccess;
	}

	/**
	 *
	 */
	public function restoreDefaults() {
		$arDefaults = array();
		foreach($this->_arSettings as $optionCode => &$arOption) {
			$arDefaults[$optionCode] = array(
				'VALUE' => $arOption['DEFAULT'],
				'CHECK_FUNC' => array($this, '__validatorBlank')
			);
		}
		$this->saveSettings($arDefaults);
	}

	/**
	 * @param $optionCode
	 * @param array $arAttributes
	 * @return string
	 */
	public function getOptionInput($optionCode, $arAttributes = array()) {
		$arOption = $this->getOption($optionCode, true);
		$input = '';
		if( empty($arOption) ) {
			return $input;
		}
		if($arOption['INPUT_ATTR'] != null) {
			$arAttributes = array_merge($arAttributes, $arOption['INPUT_ATTR']);
		}
		switch ($arOption['TYPE']) {
			case 'STRING':
				$input = '<input type="text"'
						.$this->_getOptionInputName($optionCode)
						.' value="'.$arOption['VALUE'].'"'
						.$this->_implodeInputAttributes($arAttributes).' />';
				break;
			case 'PASSWORD':
				$input = '<input type="password"'
					.$this->_getOptionInputName($optionCode)
					.' value="'.$arOption['VALUE'].'"'
					.$this->_implodeInputAttributes($arAttributes).' />';
				break;
			case 'CHECKBOX':
				$input = '<input type="hidden" '.$this->_getOptionInputName($optionCode).' value="N" />'
					.'<input type="checkbox"'
						.$this->_getOptionInputName($optionCode)
						.' value="Y"'
						.(($arOption['VALUE']=='Y')?' checked="checked"':'')
						.$this->_implodeInputAttributes($arAttributes).' />'
				;
				break;
			case 'TEXT':
				$input = '<textarea'
						.$this->_getOptionInputName($optionCode)
						.$this->_implodeInputAttributes($arAttributes)
						.'>'.$arOption['VALUE'].'</textarea>';
				break;
			case 'LIST':
				if( array_key_exists('VALUES', $arOption) && is_array($arOption['VALUES']) ) {
					$input = '<select'
								.$this->_getOptionInputName($optionCode)
								.$this->_implodeInputAttributes($arAttributes)
							.'>';
					foreach($arOption['VALUES'] as $value => $printValue) {
						$valueSelected = '';
						if($arOption['VALUE'] !== null && $value == $arOption['VALUE'] ) {
							$valueSelected = ' selected="selected"';
						}
						$input .= '<option value="'.$value.'"'.$valueSelected.'>'.$printValue.'</option>';
					}
					$input .= '</select>';
				}
				break;
			default:
				$input = '';
				break;
		}
		return $input;
	}
	protected function _implodeInputAttributes(&$arAttributes) {
		$attrString = '';
		foreach($arAttributes as $attr => &$value) {
			$attrString .= ' '.$attr.'="'.htmlspecialchars($value).'"';
		}
		return $attrString;
	}

	protected function _getOptionInputName($optionCode) {
		return ' name="'.static::SETT_INPUT_NAME_CONTAINER.'['.$this->getSettingModuleID().']['.$this->getSettingsID().']['.$optionCode.']"';
	}

	/**
	 * @return bool
	 */
	public function saveSettingsRequestData() {
		if( !array_key_exists(static::SETT_INPUT_NAME_CONTAINER, $_REQUEST) ) {
			return false;
		}
		if( !array_key_exists($this->getSettingModuleID(), $_REQUEST[static::SETT_INPUT_NAME_CONTAINER]) ) {
			return false;
		}
		if( !array_key_exists($this->getSettingsID(), $_REQUEST[static::SETT_INPUT_NAME_CONTAINER][$this->getSettingModuleID()]) ) {
			return false;
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
		return $this->saveSettings($arSettings);
	}
}



