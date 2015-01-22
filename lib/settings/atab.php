<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\Settings;

use OBX\Core\MessagePoolDecorator;

abstract class ATab extends MessagePoolDecorator implements ITab {
	static protected $_arTabInstances = array();
	protected $_tabName = '';
	protected $_tabTitle = '';
	protected $_tabDescription = '';
	protected $_tabHtmlContainer = '';
	protected $_tabIconPath = '';
	protected $_tableLeftColumnWidth = 40;

	public function __construct($arTabConfig = null) {
		if($arTabConfig !== null) {
			$this->setTabConfig($arTabConfig);
		}
	}

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

	public function setTabConfig(array $arTabConfig) {
		if( !is_array($arTabConfig) ) {
			return $this;
		}
		if( array_key_exists('TAB', $arTabConfig) ) {
			$this->_tabName = $arTabConfig['TAB'];
		}
		if( array_key_exists('TITLE', $arTabConfig) ) {
			$this->_tabTitle = $arTabConfig['TITLE'];
		}
		if( array_key_exists('DESCRIPTION', $arTabConfig) ) {
			$this->_tabDescription = $arTabConfig['DESCRIPTION'];
		}
		if( array_key_exists('ICON', $arTabConfig) ) {
			$this->_tabIconPath = $arTabConfig['ICON'];
		}
		if( array_key_exists('DIV', $arTabConfig) ) {
			$this->_tabHtmlContainer = $arTabConfig['DIV'];
		}
		if( array_key_exists('LEFT_COLUMN_WIDTH', $arTabConfig) ) {
			$leftSideWidth = intval($arTabConfig['LEFT_COLUMN_WIDTH']);
			switch($leftSideWidth){
				case 10: case 20: case 30: case 40: case 50:
				case 60: case 70: case 80: case 90:
				$this->_tableLeftColumnWidth = $leftSideWidth;
			}
		}
		return $this;
	}

	public function getTabConfig() {
		return array(
			'TAB' => $this->_tabName,
			'TITLE' => $this->_tabTitle,
			'DESCRIPTION' => $this->_tabDescription,
			'ICON' => $this->_tabIconPath,
			'DIV' => $this->_tabHtmlContainer
		);
	}

	public function getTabTitle() {
		return $this->_tabTitle;
	}

	public function getTabDescription() {
		return $this->_tabDescription;
	}

	public function getTabIcon() {
		return $this->_tabIconPath;
	}
	public function getTabHtmlContainer() {
		return $this->_tabHtmlContainer;
	}

	//abstract public function showTabContent();
	//abstract public function showTabScripts();
	//abstract public function saveTabData();

	public function showMessages($colspan = -1) {
		$colspan = intval($colspan);
		if ($colspan < 0) {
			$colspan = 1;
		}
		$arMessagesList = $this->getNotices();
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
			$colspan = 1;
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
			$colspan = 1;
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