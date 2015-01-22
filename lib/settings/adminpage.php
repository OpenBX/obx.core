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

class AdminPage {
	protected $_tabControlName = null;
	protected $_arTabs = array();
	protected $_defRestoreConfirmMessage = null;

	public function __construct($tabControlName) {
		$this->_tabControlName = $tabControlName;
		$this->_defRestoreConfirmMessage = GetMessage('OBX_CORE_SETT_ADM_PAGE_BTN_DEF_RESTORE_CONFIRM');
	}

	public function readConfig($configRelativePath) {

	}

	public function addTab(ITab $Tab) {
		if($Tab instanceof ITab) {
			$this->_arTabs[] = $Tab;
		}
		return $this;
	}

	public function addTabList($arTabs) {
		foreach($arTabs as $Tab) {
			/** @var ITab $Tab */
			$this->addTab($Tab);
		}
	}

	/**
	 * @param bool $bReturnArray
	 * @return array
	 */
	public function getTabList($bReturnArray = false){
		if($bReturnArray === true) {
			$arTabs = array();
			/** @var ATab $Tab */
			foreach($this->_arTabs as $Tab) {
				$arTab = $Tab->getTabConfig();
				$arTab['CONTROLLER'] = $Tab;
				$arTabs[] = $arTab;
			}
			return $arTabs;
		}
		return $this->_arTabs;
	}

	public function _showTabsFormHeader() {
		/** @var \CMain $APPLICATION */
		global $APPLICATION;
		?>
		<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($_REQUEST['mid'])?>&amp;lang=<?=LANGUAGE_ID?>">
	<?
	}

	public function _showTabsFormFooter() {
		?>
		</form>
	<?
	}

	public function getBXTabControl() {
		static $BXTabControl = null;
		if($BXTabControl === null) {
			$BXTabControl = new \CAdminTabControl($this->_tabControlName, $this->getTabList(true));
		}
		return $BXTabControl;
	}

	public function setRestoreConfirmMessage($strMessage) {
		if(strlen($strMessage)>0) {
			$this->_defRestoreConfirmMessage = htmlspecialchars($strMessage);
		}
	}

	public function show($bShowHtmlForm = true) {
		$BXTabControl = $this->getBXTabControl();
		if( true === $bShowHtmlForm) {
			$this->_showTabsFormHeader();
		}
		$BXTabControl->Begin();
		/** @var Tab $Tab */
		foreach($this->_arTabs as $Tab) {
			$BXTabControl->BeginNextTab();
			$Tab->showMessages(2);
			$Tab->showErrors(2);
			$Tab->showTabContent();
			$BXTabControl->EndTab();
		}
		$BXTabControl->Buttons();
		?>
		<input type="submit" name="Update"
			   class="adm-btn-save"
			   value="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_SAVE_VAL")?>"
			   title="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_SAVE_TITLE")?>">
		<!-- <input type="submit" name="Apply" value="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_APPLY_VAL")?>"
			   title="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_APPLY_TITLE")?>"> -->
		<?if (true || strlen($_REQUEST["back_url_settings"]) > 0): ?>
			<input type="button" name="Cancel" value="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_CANCEL_VAL")?>"
				   title="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_CANCEL_TITLE")?>"
				   onclick="window.location='<?echo htmlspecialchars(\CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
			<!-- <input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>"> -->
		<? endif?>
		<input type="submit" name="RestoreDefaults"
			   value="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_DEF_VAL")?>"
			   title="<?=GetMessage("OBX_CORE_SETT_ADM_PAGE_BTN_DEF_TITLE")?>"
			   onclick="if(confirm('<?=$this->_defRestoreConfirmMessage?>')) return true; else return false;">
		<?=bitrix_sessid_post();?>
		<?
		$BXTabControl->End();
		if( true === $bShowHtmlForm) {
			$this->_showTabsFormFooter();
		}

	}

	public function checkSaveRequest() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST'
			&& strlen($_POST['Update'] . $_POST['Apply']) > 0
			&& check_bitrix_sessid()
		) {
			return true;
		}
		return false;
	}

	public function save($bRedirectAfterSave = true) {
		$bAllSuccess = true;
		foreach($this->_arTabs as $Tab) {
			/** @var ITab|ISettings $Tab */
			if($Tab instanceof ITab) {
				$bAllSuccess = $Tab->saveTabData() && $bAllSuccess;
			}
			elseif($Tab instanceof ISettings) {
				$bAllSuccess = $Tab->saveSettingsRequestData() && $bAllSuccess;
			}
		}
		if( true === ($bRedirectAfterSave && $bAllSuccess) ) {
			$this->redirectAfterSave();
		}
	}

	public function checkRestoreRequest() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST'
			&& strlen($_POST['RestoreDefaults']) > 0
			&& check_bitrix_sessid()
		) {
			return true;
		}
		return false;
	}

	public function restoreDefaults($bResirectAfterSave = true){
		foreach($this->_arTabs as $Tab) {
			/** @var ATab|ISettings $Tab */
			if($Tab instanceof ISettings) {
				$Tab->restoreDefaults();
			}
		}
		if( true === $bResirectAfterSave) {
			$this->redirectAfterSave();
		}
	}

	public function redirectAfterSave() {
		/** @var \CMain $APPLICATION */
		global $APPLICATION;
		$BXTabControl = $this->getBXTabControl();
		if (strlen($_REQUEST['Update']) > 0 && strlen($_REQUEST['back_url_settings']) > 0) {
			LocalRedirect($_REQUEST['back_url_settings']);
		}
		else {
			LocalRedirect(
				$APPLICATION->GetCurPage()
				.'?mid=' . urlencode($_REQUEST['mid'])
				.'&lang=' . urlencode(LANGUAGE_ID)
				.'&back_url_settings='.urlencode($_REQUEST['back_url_settings'])
				.'&'.$BXTabControl->ActiveTabParam()
			);
		}

	}
}
