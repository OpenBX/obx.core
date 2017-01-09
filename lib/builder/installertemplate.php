<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2017 Devtop
 */

namespace OBX\Core\Builder;


class InstallerTemplate  extends \CModule
{
	protected $installDir = null;
	protected $moduleDir = null;
	protected $bxModulesDir = null;
	protected $arErrors = array();
	protected $arWarnings = array();
	protected $arMessages = array();
	protected $bSuccessInstallDB = false;
	protected $bSuccessInstallFiles = false;
	protected $bSuccessInstallDeps = false;
	protected $bSuccessInstallEvents = false;
	protected $bSuccessInstallTasks = false;
	protected $bSuccessInstallData = false;
	protected $bSuccessUnInstallDB = false;
	protected $bSuccessUnInstallFiles = false;
	protected $bSuccessUnInstallDeps = false;
	protected $bSuccessUnInstallEvents = false;
	protected $bSuccessUnInstallTasks = false;
	protected $bSuccessUnInstallData = false;
	protected $stepsSessionKey = null;

	const INSTALL_DB = 1;
	const INSTALL_FILES = 2;
	const INSTALL_DEPS = 4;
	const INSTALL_EVENTS = 8;
	const INSTALL_TASKS = 16;
	const INSTALL_ALL = 31;
	const INSTALL_NO_DEPS = 27;

	const INSTALL_COMPLETE = self::INSTALL_NO_DEPS;

	const FILE = '__FILE__';
	const DIR = '__DIR__';
	const LANG_PREFIX = 'OBX_INSTALL_MODULE_';

	public function __construct() {
		self::includeLangFile();
		$this->installDir = static::getInstallDir();
		$this->moduleDir = static::getModuleDir();
		$this->bxModulesDir = $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules';

		/** @noinspection PhpIncludeInspection */
		$arModuleVersion = include($this->installDir.'/version.php');
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

		$this->MODULE_NAME = GetMessage(static::LANG_PREFIX.'NAME');
		$this->MODULE_DESCRIPTION = GetMessage(static::LANG_PREFIX.'DESCRIPTION');
		$this->PARTNER_NAME = GetMessage(static::LANG_PREFIX.'PARTNER_NAME');
		$this->PARTNER_URI = GetMessage(static::LANG_PREFIX.'PARTNER_URI');
		$this->linkStepsToSession();
	}

	static public function getInstallDir() {
		return str_replace(array('\\', '//'), '/', static::DIR);;
	}
	static public function getModuleDir() {
		$installDir = static::getInstallDir();
		return substr($installDir, 0, ( strlen($installDir)-strlen('/install') ));
	}

	public function DoInstall() {
		$bSuccess = true;
		$bSuccess = $this->InstallDB() && $bSuccess;
		$bSuccess = $this->InstallFiles(true) && $bSuccess;
		if(method_exists($this, 'InstallDeps')) {
			$bSuccess = $this->InstallDeps() && $bSuccess;
		}
		$bSuccess = $this->InstallEvents() && $bSuccess;
		$bSuccess = $this->InstallTasks() && $bSuccess;
		if($bSuccess) {
			if( !IsModuleInstalled($this->MODULE_ID) ) {
				RegisterModule($this->MODULE_ID);
			}
		}
		else {
			/** @var \CMain $APPLICATION */
			global $APPLICATION;
			foreach($this->arErrors as $error) {
				$APPLICATION->ThrowException($error);
			}
		}
		return $bSuccess;
	}

	public function DoUninstall() {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		$bSuccess = true;
		$arBlockedSubModules = array();
		if( !$this->checkUnInstallSubmodules($arBlockedSubModules) ) {
			$strError = GetMessage(static::LANG_PREFIX.'CANT_DEL_SUB_MOD', array(
				'#MODULES_LIST#' => '"'.implode('", "', $arBlockedSubModules).'"'
			));
			$APPLICATION->ThrowException($strError);
			return false;
		}
		$bSuccess = $this->UnInstallTasks() && $bSuccess;
		$bSuccess = $this->UnInstallEvents() && $bSuccess;
		if(method_exists($this, 'UnInstallDeps')) {
			$bSuccess = $this->UnInstallDeps() && $bSuccess;
		}
		$bSuccess = $this->UnInstallFiles(true) && $bSuccess;
		$bSuccess = $this->UnInstallDB() && $bSuccess;
		if($bSuccess) {
			if( IsModuleInstalled($this->MODULE_ID) ) {
				UnRegisterModule($this->MODULE_ID);
			}
		}
		return $bSuccess;
	}

	public function InstallFiles($bSkipDepsInstall = false) {
		$this->bSuccessInstallFiles = true;
		if (is_file($this->installDir . '/install_files.php')) {
			/** @noinspection PhpIncludeInspection */
			require($this->installDir . '/install_files.php');
		}
		else {
			$this->bSuccessInstallFiles = false;
		}
		if( $this->bSuccessInstallFiles
			&& method_exists($this, 'InstallDeps')
			&& !$bSkipDepsInstall
		) {
			$this->InstallDeps();
		}
		return $this->bSuccessInstallFiles;
	}

	public function UnInstallFiles($bSkipDepsUnInstall = false) {
		$this->bSuccessUnInstallFiles = true;
		if( $this->bSuccessUnInstallFiles
			&& method_exists($this, 'UnInstallDeps')
			&& !$bSkipDepsUnInstall
		) {
			$this->UnInstallDeps();
		}
		if(!$this->bSuccessUnInstallDeps) {
			$this->bSuccessUnInstallFiles = false;
		}
		if($this->bSuccessUnInstallDeps) {
			if (is_file($this->installDir . '/uninstall_files.php')) {
				/** @noinspection PhpIncludeInspection */
				require($this->installDir . '/uninstall_files.php');
			}
			else {
				$this->bSuccessUnInstallFiles = false;
			}
		}
		return $this->bSuccessUnInstallFiles;
	}

	public function InstallEvents() {
		$this->bSuccessInstallEvents = true;
		if( $this->registerIfComplete() ) return true;
		return $this->bSuccessInstallEvents;
	}
	public function UnInstallEvents() {
		$this->bSuccessUnInstallEvents = true;
		if( $this->unRegisterIfComplete() ) return true;
		return $this->bSuccessUnInstallEvents;
	}

	public function InstallDB() {
		$this->bSuccessInstallDB = true;
		if( $this->registerIfComplete() ) return true;
		return $this->bSuccessInstallDB;
	}
	public function UnInstallDB() {
		$this->bSuccessUnInstallDB = true;
		if( $this->unRegisterIfComplete() ) return true;
		return $this->bSuccessUnInstallDB;
	}
	public function InstallTasks() {
		$this->bSuccessInstallTasks = true;
		if( $this->registerIfComplete() ) return true;
		return $this->bSuccessInstallTasks;
	}
	public function UnInstallTasks() {
		$this->bSuccessUnInstallTasks = true;
		if( $this->unRegisterIfComplete() ) return true;
		return $this->bSuccessUnInstallTasks;
	}
	public function InstallData() {
		$this->bSuccessInstallData = true;
		if( $this->registerIfComplete() ) return true;
		return $this->bSuccessInstallData;
	}
	public function UnInstallData() {
		$this->bSuccessUnInstallData = true;
		if( $this->unRegisterIfComplete() ) return true;
		return $this->bSuccessUnInstallData;
	}

	public function getErrors() {
		return $this->arErrors;
	}

	public function getWarnings() {
		return $this->arWarnings;
	}

	public function getMessages() {
		return $this->arMessages;
	}

	protected function linkStepsToSession() {
		$this->stepsSessionKey = md5(
			'__MODULE_INSTALL_STEPS_'.$this->MODULE_ID.'_'.$this->MODULE_VERSION
		);
//		if(array_key_exists($this->stepsSessionKey, $_SESSION)) {
//			$timeDelta = time() - $_SESSION[$sessionKey]['TIMESTAMP'];
//			if($timeDelta < 0 || $timeDelta >= 60) {
//				unset($_SESSION[$sessionKey]);
//			}
//		}
		if(!array_key_exists($this->stepsSessionKey, $_SESSION)) {
			$_SESSION[$this->stepsSessionKey] = array(
				'TIMESTAMP' => time(),
				'I_DB'		=> &$this->bSuccessInstallDB,
				'I_FILES'	=> &$this->bSuccessInstallFiles,
				'I_DEPS'	=> &$this->bSuccessInstallDeps,
				'I_EVENTS'	=> &$this->bSuccessInstallEvents,
				'I_TASKS'	=> &$this->bSuccessInstallTasks,
				'I_DATA'	=> &$this->bSuccessInstallData,

				'U_DB'		=> &$this->bSuccessUnInstallDB,
				'U_FILES'	=> &$this->bSuccessUnInstallFiles,
				'U_DEPS'	=> &$this->bSuccessUnInstallDeps,
				'U_EVENTS'	=> &$this->bSuccessUnInstallEvents,
				'U_TASKS'	=> &$this->bSuccessUnInstallTasks,
				'U_DATA'	=> &$this->bSuccessUnInstallData
			);
		}
		else {
			$_SESSION[$this->stepsSessionKey]['TIMESTAMP'] = time();
			$this->bSuccessInstallDB		= &$_SESSION[$this->stepsSessionKey]['I_DB'];
			$this->bSuccessInstallFiles		= &$_SESSION[$this->stepsSessionKey]['I_FILES'];
			$this->bSuccessInstallDeps		= &$_SESSION[$this->stepsSessionKey]['I_DEPS'];
			$this->bSuccessInstallEvents	= &$_SESSION[$this->stepsSessionKey]['I_EVENTS'];
			$this->bSuccessInstallTasks		= &$_SESSION[$this->stepsSessionKey]['I_TASKS'];
			$this->bSuccessInstallData		= &$_SESSION[$this->stepsSessionKey]['I_DATA'];

			$this->bSuccessUnInstallDB		= &$_SESSION[$this->stepsSessionKey]['U_DB'];
			$this->bSuccessUnInstallFiles	= &$_SESSION[$this->stepsSessionKey]['U_FILES'];
			$this->bSuccessUnInstallDeps	= &$_SESSION[$this->stepsSessionKey]['U_DEPS'];
			$this->bSuccessUnInstallEvents	= &$_SESSION[$this->stepsSessionKey]['U_EVENTS'];
			$this->bSuccessUnInstallTasks	= &$_SESSION[$this->stepsSessionKey]['U_TASKS'];
			$this->bSuccessUnInstallData	= &$_SESSION[$this->stepsSessionKey]['U_DATA'];
		}
	}

	/**
	 * @param int $maskTarget
	 * @return bool
	 */
	public function isInstallationSuccess($maskTarget = self::INSTALL_ALL) {
		$bSuccess = true;
		if($maskTarget & self::INSTALL_DB) {
			$bSuccess = $this->bSuccessInstallDB && $bSuccess;
		}
		if($maskTarget & self::INSTALL_FILES) {
			$bSuccess = $this->bSuccessInstallFiles && $bSuccess;
		}
		if($maskTarget & self::INSTALL_DEPS) {
			$bSuccess = $this->bSuccessInstallDeps && $bSuccess;
		}
		if($maskTarget & self::INSTALL_EVENTS) {
			$bSuccess = $this->bSuccessInstallEvents && $bSuccess;
		}
		if($maskTarget & self::INSTALL_TASKS) {
			$bSuccess = $this->bSuccessInstallTasks && $bSuccess;
		}
		return $bSuccess;
	}

	/**
	 * @param int $maskTarget
	 * @return bool
	 */
	public function isUnInstallationSuccess($maskTarget = self::INSTALL_ALL) {
		$bSuccess = true;
		if($maskTarget & self::INSTALL_DB) {
			$bSuccess = $this->bSuccessUnInstallDB && $bSuccess;
		}
		if($maskTarget & self::INSTALL_FILES) {
			$bSuccess = $this->bSuccessUnInstallFiles && $bSuccess;
		}
		if($maskTarget & self::INSTALL_DEPS) {
			$bSuccess = $this->bSuccessUnInstallDeps && $bSuccess;
		}
		if($maskTarget & self::INSTALL_EVENTS) {
			$bSuccess = $this->bSuccessUnInstallEvents && $bSuccess;
		}
		if($maskTarget & self::INSTALL_TASKS) {
			$bSuccess = $this->bSuccessUnInstallTasks && $bSuccess;
		}
		return $bSuccess;
	}

	public function InstallDeps() {
		$arDepsList = $this->getDepsList();
		$this->bSuccessInstallDeps = true;
		foreach($arDepsList as $depModID => $depModClass) {
			$depModInstallerFile = $this->bxModulesDir.'/'.$depModID.'/install/index.php';
			if( !IsModuleInstalled($depModID) ) {
				if(file_exists($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$depModID)) {
					DeleteDirFilesEx($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$depModID);
				}
				CopyDirFiles(
					$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$this->MODULE_ID.'/install/modules/'.$depModID,
					$_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$depModID,
					true, true
					, false, 'update-'
				);

				if( !is_file($depModInstallerFile) ) {
					$this->bSuccessInstallDeps = false;
					$this->arErrors[] = 'Installer "'.$this->MODULE_ID.'": Dependency installer not found ('.BX_ROOT.'/modules/'.$depModID.')';
				}
				else {
					/** @noinspection PhpIncludeInspection */
					require_once $depModInstallerFile;
					/** @var \CModule $DepModInstaller */
					$DepModInstaller = new $depModClass;
					$bSuccess = true;
					$bSuccess = $DepModInstaller->InstallFiles() && $bSuccess;
					$bSuccess = $DepModInstaller->InstallDB() && $bSuccess;
					$bSuccess = $DepModInstaller->InstallEvents() && $bSuccess;
					$bSuccess = $DepModInstaller->InstallTasks() && $bSuccess;
					if( method_exists($DepModInstaller, 'InstallData') ) {
						$bSuccess = $DepModInstaller->InstallData() && $bSuccess;
					}
					if( $bSuccess ) {
						if( !IsModuleInstalled($depModID) ) {
							RegisterModule($depModID);
						}
					}
					else {
						if( method_exists($DepModInstaller, 'getErrors') ) {
							$arInstallErrors = $DepModInstaller->getErrors();
							foreach($arInstallErrors as $error) {
								$this->arErrors[] = 'Installer "'.$this->MODULE_ID.'": Install dependency error '.$depModID.': '.$error;
							}
						}
						$this->bSuccessInstallDeps = false;
					}
				}
			}
			else {
				if( !is_file($depModInstallerFile) ) {
					$this->bSuccessInstallDeps = false;
					$this->arErrors[] = 'Installer "'.$this->MODULE_ID.'": Dependency installer not found ('.BX_ROOT.'/modules/'.$depModID.')';
				}
				else {
					/** @noinspection PhpIncludeInspection */
					require_once $depModInstallerFile;
					/** @var \CModule $DepModInstaller */
					$DepModInstaller = new $depModClass;

					$depInstallModulePath = $_SERVER['DOCUMENT_ROOT'].BX_ROOT
						.'/modules/'.$this->MODULE_ID
						.'/install/modules/'.$depModID
					;
					$depInstallModuleFolder = BX_ROOT
						.'/modules/'.$this->MODULE_ID
						.'/install/modules/'.$depModID
					;
					if( !is_dir($depInstallModulePath) ) {
						continue;
					}
					$depInstallDir = opendir($depInstallModulePath);
					$arUpdates = array();
					while($depInsFSEntry = readdir($depInstallDir)) {
						if($depInsFSEntry == '.' || $depInsFSEntry == '..') continue;
						if( strpos($depInsFSEntry, 'update-') !== false
							&& is_dir($depInstallModulePath.'/'.$depInsFSEntry)
						) {
							$arUpdateVersion = self::readVersion($depInsFSEntry);
							$arCurrentModuleVersion = self::readVersion($DepModInstaller->MODULE_VERSION);
							if(
								!empty($arUpdateVersion) && !empty($arCurrentModuleVersion)
								&& $arUpdateVersion['RAW_VERSION'] > $arCurrentModuleVersion['RAW_VERSION']
							) {
								$arUpdates[] = $depInsFSEntry;
							}
						}
					}
					closedir($depInstallDir);
					if( !empty($arUpdates) ) {
						uasort($arUpdates, array($this, 'compareVersions'));
						$CUpdateClientPartner = new \CUpdateClientPartner();
						foreach($arUpdates as $updateFolder) {
							$strErrors = '';
							if( self::checkUpdaterScripts($depInstallModulePath.'/'.$updateFolder) ) {
								$GLOBALS['__runAutoGenUpdater'] = true;
								$CUpdateClientPartner->AddMessage2Log('Installer "'.$this->MODULE_ID.'": Run updater of Dependency '.$depModID);
								$CUpdateClientPartner->__RunUpdaterScript(
									$depInstallModulePath.'/'.$updateFolder.'/updater.dep.php',
									$strErrors,
									$depInstallModuleFolder.'/'.$updateFolder,
									$depModID
								);
								unset($GLOBALS['__runAutoGenUpdater']);
							}
							else {
								$strErrors .= 'Installer "'.$this->MODULE_ID.'": Dependency updater-script not found('.$depInstallModuleFolder.'/'.$updateFolder.'/updater.dep.php'.')'."\n";
							}
							if(strlen($strErrors)>0) {
								$logError = 'Update dependency error '.$depModID.': '."\n".$strErrors;
								$this->arErrors[] = $logError;
								$CUpdateClientPartner->AddMessage2Log('Installer "'.$this->MODULE_ID.'": '.$logError);
								$this->bSuccessInstallDeps = false;
							}
						}
					}
				}
			}
		}
		if( $this->registerIfComplete() ) {
			return true;
		}
		return $this->bSuccessInstallDeps;
	}

	static protected function checkUpdaterScripts($updateDirPath) {
		if( is_file($updateDirPath.'/__upd__.dep.php') ) {
			$updateDir = opendir($updateDirPath);
			while($fsEntry = readdir($updateDir)) {
				if( is_file($updateDirPath.'/'.$fsEntry) && strpos($fsEntry, '__upd__.') !== false ) {
					$rightUpdaterScriptName = $updateDirPath.'/'.str_replace('__upd__.', 'updater.', $fsEntry);
					@rename($updateDirPath.'/'.$fsEntry, $rightUpdaterScriptName);
				}
			}
			closedir($updateDir);
			return true;
		}
		elseif( is_file($updateDirPath.'/updater.dep.php') ) {
			return true;
		}
		else {
			return false;
		}
	}

	public function UnInstallDeps() {
		/** global CMain $APPLICATION */
		global $APPLICATION;
		$arDepsList = $this->getMyOwnDepsList();
		$this->bSuccessUnInstallDeps = true;
		$CUpdateClientPartner = new \CUpdateClientPartner();
		$arBlockedSubModules = array();
		if( !$this->checkUnInstallSubmodules($arBlockedSubModules) ) {
			$strError = GetMessage(static::LANG_PREFIX.'CANT_DEL_SUB_MOD', array(
				'#MODULES_LIST#' => '"'.implode('", "', $arBlockedSubModules).'"'
			));
			$CUpdateClientPartner->AddMessage2Log('Installer "'.$this->MODULE_ID.'": '.$strError);
			$APPLICATION->ThrowException($strError);
			$this->bSuccessUnInstallDeps = false;
		}
		else {
			foreach($arDepsList as $depModID => $depModClass) {
				$CUpdateClientPartner->__DeleteDirFilesEx($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/'.$depModID);
			}
		}
		if( $this->unRegisterIfComplete() ) return true;
		return $this->bSuccessUnInstallDeps;
	}

	protected function checkUnInstallSubmodules(&$arBlockedSubModules = null) {
		$bCanUnInstall = true;
		$arBlockedSubModules = array();
		$arDepsList = $this->getMyOwnDepsList();
		foreach($arDepsList as $depModID => $depModClass) {
			if( IsModuleInstalled($depModID) ) {
				/** @var \CModule $SubModuleInstaller */
				$SubModuleInstaller = $this->getSubModuleObject($depModID);
				$arBlockedSubModules[$depModID] = $SubModuleInstaller->MODULE_NAME.' ('.$depModID.')';
				$bCanUnInstall = false;
			}
		}
		return $bCanUnInstall;
	}

	protected function getSubModuleObject($moduleID) {
		$arDepsList = $this->getDepsList();
		if( !array_key_exists($moduleID, $arDepsList) ) {
			$this->arErrors[] = GetMessage(static::LANG_PREFIX.'MODULE_IS_NOT_DEP', array(
				'#MODULE#' => $moduleID
			));
			return null;
		}
		$moduleInstallerFile = $this->bxModulesDir.'/'.$moduleID.'/install/index.php';
		if( !is_file($moduleInstallerFile) ) {
			$this->arErrors[] = GetMessage(static::LANG_PREFIX.'SUBMODULE_INSTALLER_NOT_FOUND', array(
				'#MODULE#' => $moduleID
			));
			return null;
		}
		/** @noinspection PhpIncludeInspection */
		require_once $moduleInstallerFile;
		$SubModule = new $arDepsList[$moduleID];
		return $SubModule;
	}

	public function getDepsList() {
		$arDepsList = array();
		if( is_dir($this->installDir.'/modules') && is_file($this->installDir.'/dependencies.php') ) {
			/** @noinspection PhpIncludeInspection */
			$arDepsList = require $this->installDir.'/dependencies.php';
		}
		return $arDepsList;
	}

	protected function getSuperModulesList() {
		static $arSuperModulesList = null;
		if($arSuperModulesList !== null) {
			return $arSuperModulesList;
		}
		$dirModules = opendir($this->bxModulesDir);
		$arSuperModulesList = array();
		while($moduleID = readdir($dirModules)) {
			if($moduleID == '.' || $moduleID == '..' || $moduleID == $this->MODULE_ID) {
				continue;
			}
			if( is_file($this->bxModulesDir.'/'.$moduleID.'/install/dependencies.php') ) {
				$arSuperModulesList[$moduleID] = array(
					'CLASS' => str_replace('.', '_', $moduleID),
					'INSTALLER_FILE'  => $this->bxModulesDir.'/'.$moduleID.'/install/index.php',
					'DEPS_LIST_FILE' => $this->bxModulesDir.'/'.$moduleID.'/install/dependencies.php'
				);
			}
		}
		return $arSuperModulesList;
	}

	public function getMyOwnDepsList() {
		static $arDepsList = null;
		if($arDepsList !== null) {
			return $arDepsList;
		}
		$arDepsList = $this->getDepsList();
		$arSuperModules = $this->getSuperModulesList();
		$arUsedDeps = array();
		foreach($arSuperModules as $superModuleID => &$arSuperMod) {
			/** @noinspection PhpIncludeInspection */
			$arSuperModuleDeps = include $arSuperMod['DEPS_LIST_FILE'];
			if( array_key_exists($superModuleID, $arDepsList) ) {
				continue;
			}
			if(!is_file($arSuperMod['INSTALLER_FILE'])) {
				continue;
			}
			if( !IsModuleInstalled($superModuleID) ) {
				continue;
			}
			if(!class_exists($arSuperMod['CLASS'])) {
				/** @noinspection PhpIncludeInspection */
				require $arSuperMod['INSTALLER_FILE'];
			}
			if(!class_exists($arSuperMod['CLASS'])) {
				continue;
			}
			/** @var \CModule $SuperModule */
			$SuperModule = new $arSuperMod['CLASS'];
			if(!($SuperModule instanceof \CModule)) {
				continue;
			}
			if($SuperModule->MODULE_ID != $superModuleID) {
				continue;
			}
			$arUsedDeps = array_merge($arUsedDeps, $arSuperModuleDeps);
		}
		foreach($arUsedDeps as $depModID => $depModClass) {
			if( array_key_exists($depModID, $arDepsList) ) {
				unset($arDepsList[$depModID]);
			}
		}
		return $arDepsList;
	}

	protected function IncludeStep($strTitle, $stepFilePath)
	{
		//define all global vars
		global $__IncludeStepFileGlobalKeys;
		global $__IncludeStepFileGlobalKeysIterator;
		$__IncludeStepFileGlobalKeys = array_keys($GLOBALS);
		$__IncludeStepFileGlobalKeysCount = count($__IncludeStepFileGlobalKeys);
		for($__IncludeStepFileGlobalKeysIterator = 0;
			$__IncludeStepFileGlobalKeysIterator < $__IncludeStepFileGlobalKeysCount;
			$__IncludeStepFileGlobalKeysIterator++
		) {
			if(
				$__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]!='i'
				&& $__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]!='GLOBALS'
				&& $__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]!='strTitle'
				&& $__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]!='filepath'
				&& $__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]!='__IncludeStepFileGlobalKeys'
				&& $__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]!='__IncludeStepFileGlobalKeysIterator'
				&& $__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]!='__IncludeStepFileGlobalKeysCount'
			) {
				global ${$__IncludeStepFileGlobalKeys[$__IncludeStepFileGlobalKeysIterator]};
			}
		}
		unset($GLOBALS['__IncludeStepFileGlobalKeys']);
		unset($GLOBALS['__IncludeStepFileGlobalKeysIterator']);
		unset($GLOBALS['__IncludeStepFileGlobalKeysCount']);
		/** @noinspection PhpIncludeInspection */
		include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/prolog_admin_after.php');
		/**
		 * @var \CMain $APPLICATION
		 */
		global $APPLICATION;
		if(!empty($APPLICATION)) {
			$APPLICATION->SetTitle($strTitle);
		}
		/** @noinspection PhpIncludeInspection */
		include($stepFilePath);
		/** @noinspection PhpIncludeInspection */
		include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
		die();
	}

	protected function resetStepSuccess() {
		$this->bSuccessInstallFiles		= false;
		$this->bSuccessInstallDB		= false;
		$this->bSuccessInstallDeps		= false;
		$this->bSuccessInstallEvents	= false;
		$this->bSuccessInstallTasks		= false;
		$this->bSuccessInstallData		= false;

		$this->bSuccessUnInstallFiles	= false;
		$this->bSuccessUnInstallDB		= false;
		$this->bSuccessUnInstallDeps	= false;
		$this->bSuccessUnInstallEvents	= false;
		$this->bSuccessUnInstallTasks	= false;
		$this->bSuccessUnInstallData	= false;
	}

	public function registerModule() {
		if( !IsModuleInstalled($this->MODULE_ID) ) {
			RegisterModule($this->MODULE_ID);
			$this->resetStepSuccess();
		}
	}
	public function unRegisterModule() {
		if( IsModuleInstalled($this->MODULE_ID) ) {
			UnRegisterModule($this->MODULE_ID);
			$this->resetStepSuccess();
		}
	}

	public function registerIfComplete() {
		if( $this->isInstallationSuccess(static::INSTALL_COMPLETE) ) {
			if( !IsModuleInstalled($this->MODULE_ID) ) {
				$this->registerModule();
			}
			return true;
		}
		return false;
	}
	public function unRegisterIfComplete() {
		if( $this->isUnInstallationSuccess(static::INSTALL_COMPLETE) ) {
			if(IsModuleInstalled($this->MODULE_ID)) {
				$this->unRegisterModule();
			}
			return true;
		}
		return false;
	}

	static public function includeLangFile() {
		static $langFileIncluded = false;
		if(false === $langFileIncluded) {
			/** @noinspection PhpUnusedLocalVariableInspection */
			global $MESS;
			$fileName = 'index.php';
			if(strpos(static::FILE, static::DIR.'/') === 0) {
				$fileName = substr(static::FILE, strlen(static::DIR)+1);
			}
			/** @noinspection PhpIncludeInspection */
			@include(static::getModuleDir().'/lang/'.LANGUAGE_ID.'/install/'.$fileName);
			$langFileIncluded = true;
		}
	}

	static public function readVersion($version) {
		$regVersion = (
			'~^'.(
				'(?:'.(
					'('.(
						'(?:[a-zA-Z0-9]+\.)?'
						.'(?:[a-zA-Z0-9]+)'
					).')'
					.'\-'
				).')?'
				.'([\d]{1,2})\.([\d]{1,2})\.([\d]{1,2})'.'(?:\-r([\d]{1,4}))?'
			).'$~'
		);
		$arVersion = array();
		if( preg_match($regVersion, $version, $arMatches) ) {
			$arVersion['NAME'] = $arMatches[1];
			$arVersion['MAJOR'] = $arMatches[2];
			$arVersion['MINOR'] = $arMatches[3];
			$arVersion['FIXES'] = $arMatches[4];
			$arVersion['REVISION'] = 0;
			$arVersion['VERSION'] = $arMatches[2].'.'.$arMatches[3].'.'.$arMatches[4];
			if($arMatches[5]) {
				$arVersion['REVISION'] = $arMatches[5];
				$arVersion['VERSION'] .= '-r'.$arVersion['REVISION'];
			}
			$arVersion['RAW_VERSION'] =
				($arVersion['MAJOR'] * 1000000000)
				+ ($arVersion['MINOR'] * 10000000)
				+ ($arVersion['FIXES'] * 10000)
				+ ($arVersion['REVISION'])
			;
		}
		return $arVersion;
	}

	static public function compareVersions($versionA, $versionB) {
		$arVersionA = self::readVersion($versionA);
		$arVersionB = self::readVersion($versionB);
		if($arVersionA['RAW_VERSION'] == $arVersionB['RAW_VERSION']) return 0;
		return ($arVersionA['RAW_VERSION'] < $arVersionB['RAW_VERSION'])? -1 : 1;
	}
}
