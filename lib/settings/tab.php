<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core\Settings;

class Tab extends ATab implements ISettings {
	/** @var Settings */
	protected $_Settings = null;
	protected $_tabOptionGroups = array(
		'__DEFAULT__' => array(
			'NAME' => '',
			'CODE' => '__DEFAULT__',
			'SORT' => 10000,
			'ITEMS' => array()
		)
	);
	const DEF_OPTION_GROUP_SORT = 100;

	/**
	 * @param string $moduleID
	 * @param string $settingsID
	 * @param array $arTabConfig
	 * @param array|Settings $Settings
	 */
	public function __construct($moduleID, $settingsID, $arTabConfig, $Settings) {
		$arSettings = null;
		if( is_array($Settings) && !empty($Settings)) {
			$arSettings = $Settings;
			$Settings = new Settings($moduleID, $settingsID, $Settings);
		}
		elseif($Settings instanceof Settings) {
			$arSettings = $Settings->getSettings();
		}
		$this->initSettings($Settings);

		if( is_array($arTabConfig) && !array_key_exists('DIV', $arTabConfig) ) {
			$arTabConfig['DIV'] = strtoupper('sett_'.str_replace('.', '_', $moduleID).'_'.$settingsID);
		}
		$this->setTabConfig($arTabConfig);

		if(null !== $arSettings) {
			foreach($arSettings as $optionCode => &$arOption) {
				if( array_key_exists('GROUP', $arOption)
					&& array_key_exists($arOption['GROUP'], $this->_tabOptionGroups)
				) {
					$this->_tabOptionGroups[$arOption['GROUP']]['ITEMS'][$optionCode] =
						(array_key_exists('SORT', $arOption)?intval($arOption['SORT']):self::DEF_OPTION_GROUP_SORT);
					uasort($this->_tabOptionGroups[$arOption['GROUP']]['ITEMS'], array(__CLASS__, '__sortOptionInGroup'));
				}
				else {
					$this->_tabOptionGroups['__DEFAULT__']['ITEMS'][$optionCode] =
						(array_key_exists('SORT', $arOption)?intval($arOption['SORT']):self::DEF_OPTION_GROUP_SORT);
					uasort($this->_tabOptionGroups['__DEFAULT__']['ITEMS'], array(__CLASS__, '__sortOptionInGroup'));
				}
			}
		}
	}

	public function setTabConfig(array $arTabConfig) {
		if( !is_array($arTabConfig) ) {
			return $this;
		}
		parent::setTabConfig($arTabConfig);
		if( array_key_exists('GROUPS', $arTabConfig) ) {
			foreach($arTabConfig['GROUPS'] as $groupCode => &$arGroup) {
				if(is_string($arGroup)) {
					$arGroup = array('NAME' => $arGroup);
				}
				if(!array_key_exists('NAME', $arGroup)) {
					continue;
				}
				if($arGroup['NAME'] == '__DEFAULT__') {
					continue;
				}
				if(array_key_exists('SORT', $arGroup)) {
					$arGroup['SORT'] = intval($arGroup['SORT']);
				}
				else {
					$arGroup['SORT'] = self::DEF_OPTION_GROUP_SORT;
				}
				$this->_tabOptionGroups[$groupCode] = array(
					'NAME' => $arGroup['NAME'],
					'CODE' => $groupCode,
					'SORT' => $arGroup['SORT'],
					'ITEMS' => array()
				);
			}
			if(!empty($this->_tabOptionGroups)) {
				uasort($this->_tabOptionGroups, array(__CLASS__, '__sort'));
			}
		}
		return $this;
	}

	public function addOptionsGroup($groupCode, $groupName, $groupSort = self::DEF_OPTION_GROUP_SORT) {
		if (!preg_match('~^[a-zA-Z0-9\_]*$~', $groupCode)) {
			throw new \ErrorException('Wrong group code');
		}
		$groupSort = intval($groupSort);
		if(empty($groupName)) {
			throw new \ErrorException('Group name is empty');
		}
		$this->_tabOptionGroups[$groupCode] = array(
			'NAME' => $groupName,
			'CODE' => $groupCode,
			'SORT' => $groupSort,
			'ITEMS' => array()
		);
		uasort($this->_tabOptionGroups, array(__CLASS__, '__sort'));
		return $this;
	}

	public function initSettings(Settings $Settings) {
		if( $Settings instanceof Settings ) {
			$this->_Settings = $Settings;
			$Settings->setMessagePool($this->getMessagePool());
		}
		else {
			throw new \ErrorException('Settings initialization failed');
		}
	}

	static public function __sort(array &$A, array &$B) {
		if ($A['SORT'] == $B['SORT']) {
			return 0;
		}
		return ($A['SORT'] < $B['SORT']) ? -1 : 1;
	}

	// +++ ISettings implementation
	public function getSettingModuleID() {
		return $this->_Settings->getSettingModuleID();
	}
	public function getSettingsID() {
		return $this->_Settings->getSettingsID();
	}
	public function syncSettings() {
		$this->_Settings->syncSettings();
	}

	public function syncOption($optionCode) {
		$this->_Settings->syncOption($optionCode);
	}
	public function getSettings() {
		return $this->_Settings->getSettings();
	}

	/**
	 * @param $optionCode
	 * @param $arOption
	 * @return $this
	 */
	public function addOption($optionCode, $arOption) {
		$this->_Settings->addOption($optionCode, $arOption);
		if( array_key_exists('GROUP', $arOption)
			&& array_key_exists($arOption['GROUP'], $this->_tabOptionGroups)
		) {
			$this->_tabOptionGroups[$arOption['GROUP']]['ITEMS'][$optionCode] =
				(array_key_exists('SORT', $arOption)?intval($arOption['SORT']):self::DEF_OPTION_GROUP_SORT);
			uasort($this->_tabOptionGroups[$arOption['GROUP']]['ITEMS'], array(__CLASS__, '__sortOptionInGroup'));
		}
		else {
			$this->_tabOptionGroups['__DEFAULT__']['ITEMS'][$optionCode] =
				(array_key_exists('SORT', $arOption)?intval($arOption['SORT']):self::DEF_OPTION_GROUP_SORT);
			uasort($this->_tabOptionGroups['__DEFAULT__']['ITEMS'], array(__CLASS__, '__sortOptionInGroup'));
		}
		return $this;
	}

	static public function __sortOptionInGroup(&$A, &$B) {
		if ($A == $B) {
			return 0;
		}
		return ($A < $B) ? -1 : 1;
	}
	public function getOption($optionCode, $bReturnOptionArray = false) {
		return $this->_Settings->getOption($optionCode, $bReturnOptionArray);
	}
	public function saveSettings($arSettings) {
		$this->_Settings->saveSettings($arSettings);
	}
	public function restoreDefaults() {
		$this->_Settings->restoreDefaults();
	}
	public function getOptionInput($optionCode, $arAttributes = array()) {
		return $this->_Settings->getOptionInput($optionCode, $arAttributes);
	}
	/**
	 * @return bool
	 */
	public function saveSettingsRequestData() {
		return $this->_Settings->saveSettingsRequestData();
	}
	// ^^^ ISettings implementation

	// +++ ATab implementation
	public function showTabContent() {
		$arSettings = $this->_Settings->getSettings();
		$idPrefix = 'sett_'.str_replace('.', '_', $this->getSettingModuleID()).'_';
		foreach($this->_tabOptionGroups as $arGroup):?>
			<?if(!empty($arGroup['NAME']) && !empty($arGroup['ITEMS'])):?>
				<tr class="heading">
					<td colspan="2"><b><?=$arGroup['NAME']?></b></td>
				</tr>
			<?endif?>
			<?foreach($arGroup['ITEMS'] as $optionCode => $optionSort):
				$arOption = &$arSettings[$optionCode];
				$bWithDescription = (strlen(trim($arOption['DESCRIPTION']))>0)?true:false;
				$bWithHint = (strlen(trim($arOption['HINT']))>0)?true:false;
				?>
				<tr>
					<td width="<?=$this->_tableLeftColumnWidth?>%"<?if($bWithDescription):?> style="vertical-align: top;"<?endif?>>
						<label for="<?=$idPrefix.$optionCode?>"><?=$arOption['NAME']?></label>
						<?if( strlen($arOption['DESCRIPTION'])>0 ):?>
							<br /><small><?=$arOption['DESCRIPTION']?></small>
						<?endif?>
					</td>
					<td<?if($bWithDescription):?> style="vertical-align: top;"<?endif?>>
						<?=$this->_Settings->getOptionInput($optionCode, array('id' => $idPrefix.$optionCode))?>
						<?if($bWithHint):?>
							<img src="/bitrix/js/main/core/images/hint.gif" onmouseover="BX.hint(this, '<?=$arOption['HINT']?>')" />
						<?endif?>
					</td>
				</tr>
			<?endforeach;
		endforeach;
	}
	public function showTabScripts() {
		return '';
	}
	public function saveTabData() {
		$this->_Settings->saveSettingsRequestData();
	}
	// ^^^ ATab implementation
}