<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

class obx_core extends CModule
{
	var $MODULE_ID = "obx.core";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = "Y";

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

	const DB = 1;
	const FILES = 2;
	const DEPS = 4;
	const EVENTS = 8;
	const TASKS = 16;
	const TARGETS = 31;

	public function obx_core() {
		self::includeLangFile();
		$this->installDir = str_replace(array("\\", "//"), "/", __FILE__);
		//10 == strlen("/index.php")
		//8 == strlen("/install")
		$this->installDir = substr($this->installDir , 0, strlen($this->installDir ) - 10);
		$this->moduleDir = substr($this->installDir , 0, strlen($this->installDir ) - 8);
		$this->bxModulesDir = $_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules";

		$arModuleInfo = array();
		$arModuleInfo = include($this->installDir."/version.php");
		$this->MODULE_VERSION = $arModuleInfo["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleInfo["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("OBX_MODULE_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("OBX_MODULE_INSTALL_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("OBX_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("OBX_PARTNER_URI");
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

	/**
	 * @param int $maskTarget
	 * @return bool
	 */
	public function isIntallationSuccess($maskTarget) {
		$bSuccess = true;
		if($maskTarget & self::DB) {
			$bSuccess = $this->bSuccessInstallDB && $bSuccess;
		}
		if($maskTarget & self::FILES) {
			$bSuccess = $this->bSuccessInstallFiles && $bSuccess;
		}
		if($maskTarget & self::DEPS) {
			$bSuccess = $this->bSuccessInstallDeps && $bSuccess;
		}
		if($maskTarget & self::EVENTS) {
			$bSuccess = $this->bSuccessInstallEvents && $bSuccess;
		}
		if($maskTarget & self::TASKS) {
			$bSuccess = $this->bSuccessInstallTasks && $bSuccess;
		}
		return $bSuccess;
	}

	/**
	 * @param int $maskTarget
	 * @return bool
	 */
	public function isUnIntallationSuccess($maskTarget) {
		$bSuccess = true;
		if($maskTarget & self::DB) {
			$bSuccess = $this->bSuccessUnInstallDB && $bSuccess;
		}
		if($maskTarget & self::FILES) {
			$bSuccess = $this->bSuccessUnInstallFiles && $bSuccess;
		}
		if($maskTarget & self::DEPS) {
			$bSuccess = $this->bSuccessUnInstallDeps && $bSuccess;
		}
		if($maskTarget & self::EVENTS) {
			$bSuccess = $this->bSuccessUnInstallEvents && $bSuccess;
		}
		if($maskTarget & self::TASKS) {
			$bSuccess = $this->bSuccessUnInstallTasks && $bSuccess;
		}
		return $bSuccess;
	}

	public function DoInstall() {
		$bSuccess = true;
		$bSuccess = $this->InstallDB() && $bSuccess;
		$bSuccess = $this->InstallFiles() && $bSuccess;
		$bSuccess = $this->InstallDeps() && $bSuccess;
		$bSuccess = $this->InstallEvents() && $bSuccess;
		$bSuccess = $this->InstallTasks() && $bSuccess;
		if($bSuccess) {
			if( !IsModuleInstalled($this->MODULE_ID) ) {
				RegisterModule($this->MODULE_ID);
			}
		}
		return $bSuccess;
	}
	public function DoUninstall() {
		$bSuccess = true;
		$bSuccess = $this->UnInstallTasks() && $bSuccess;
		$bSuccess = $this->UnInstallEvents() && $bSuccess;
		//$bSuccess = $this->UnInstallDeps() && $bSuccess;
		$bSuccess = $this->UnInstallFiles() && $bSuccess;
		$bSuccess = $this->UnInstallDB() && $bSuccess;
		if($bSuccess) {
			if( IsModuleInstalled($this->MODULE_ID) ) {
				UnRegisterModule($this->MODULE_ID);
			}
		}
		return $bSuccess;
	}

	public function InstallFiles() {
		$this->bSuccessInstallFiles = true;
		if (is_file($this->installDir . "/install_files.php")) {
			require($this->installDir . "/install_files.php");
		}
//		else {
//			$this->bSuccessInstallFiles = false;
//		}
		return $this->bSuccessInstallFiles;
	}
	public function UnInstallFiles() {
		$this->bSuccessUnInstallFiles = true;
		if (is_file($this->installDir . "/uninstall_files.php")) {
			require($this->installDir . "/uninstall_files.php");
		}
//		else {
//			$this->bSuccessUnInstallFiles = false;
//		}
		return $this->bSuccessUnInstallFiles;
	}

	public function InstallDB() {
		global $DB, $DBType;
		$this->bSuccessInstallDB = true;
		if( is_file($this->installDir.'/db/'.$DBType.'/install.sql') ) {
			$this->prepareDBConnection();
			$arErrors = $DB->RunSQLBatch($this->installDir.'/db/'.$DBType.'/install.sql');
			if( is_array($arErrors) && count($arErrors)>0 ) {
				$this->arErrors = $arErrors;
				$this->bSuccessInstallDB = false;
			}
		}
//		else {
//			$this->bSuccessInstallDB = false;
//		}
		return $this->bSuccessInstallDB;
	}
	public function UnInstallDB() {
		global $DB, $DBType;
		$this->bSuccessUnInstallDB = true;
		if( is_file($this->installDir.'/db/'.$DBType.'/uninstall.sql') ) {
			$this->prepareDBConnection();
			$arErrors = $DB->RunSQLBatch($this->installDir.'/db/'.$DBType.'/uninstall.sql');
			if( is_array($arErrors) && count($arErrors)>0 ) {
				$this->arErrors = $arErrors;
				$this->bSuccessUnInstallDB = false;
			}
		}
//		else {
//			$this->bSuccessUnInstallDB = false;
//		}
		return $this->bSuccessUnInstallDB;
	}

	public function InstallEvents() {
		RegisterModuleDependences('main', 'OnPageStart', 'obx.core', 'OBX\Core\EventD', 'connectAllEvents', '10');
		$this->bSuccessInstallEvents = true; return $this->bSuccessInstallEvents;
	}
	public function UnInstallEvents() {
		UnRegisterModuleDependences('main', 'OnPageStart', 'obx.core', 'OBX\Core\EventD', 'connectAllEvents');
		$this->bSuccessUnInstallEvents = true; return $this->bSuccessUnInstallEvents;
	}
	public function InstallTasks() { $this->bSuccessInstallTasks = true; return $this->bSuccessInstallTasks; }
	public function UnInstallTasks() { $this->bSuccessUnInstallTasks = true; return $this->bSuccessUnInstallTasks; }
	public function InstallData() { $this->bSuccessInstallData = true; return $this->bSuccessInstallData; }
	public function UnInstallData() { $this->bSuccessUnInstallData = true; return $this->bSuccessUnInstallData; }

	public function InstallDeps() {
		if( is_file($this->installDir."/install_deps.php") ) {
			require $this->installDir."/install_deps.php";
			$arDepsList = $this->getDepsList();
			foreach($arDepsList as $depModID => $depModClass) {
				$depModInstallerFile = $this->bxModulesDir."/".$depModID."/install/index.php";
				if( is_file($depModInstallerFile) ) {
					require_once $depModInstallerFile;
					/** @var CModule $DepModInstaller */
					$bSuccess = true;
					$DepModInstaller = new $depModClass;
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
						$this->bSuccessInstallDeps = true;
					}
					else {
						if( method_exists($DepModInstaller, 'getErrors') ) {
							$arInstallErrors = $DepModInstaller->getErrors();
							foreach($arInstallErrors as $error) {
								$this->arErrors[] = $error;
							}
						}
						$this->bSuccessInstallDeps = false;
					}
				}
			}
		}
		return $this->bSuccessInstallDeps;
	}
	public function UnInstallDeps() {
		$arDepsList = $this->getDepsList();
		foreach($arDepsList as $depModID => $depModClass) {
			$depModInstallerFile = $this->bxModulesDir."/".$depModID."/install/index.php";
			if( is_file($depModInstallerFile) ) {
				require_once $depModInstallerFile;
				/** @var CModule $DepModInstaller */
				$bSuccess = true;
				$DepModInstaller = new $depModClass;
				$bSuccess = true;
				$bSuccess = $DepModInstaller->UnInstallTasks() && $bSuccess;
				$bSuccess = $DepModInstaller->UnInstallEvents() && $bSuccess;
				$bSuccess = $DepModInstaller->UnInstallFiles() && $bSuccess;
				$bSuccess = $DepModInstaller->UnInstallDB() && $bSuccess;
				if( $bSuccess ) {
					if( IsModuleInstalled($depModID) ) {
						UnRegisterModule($depModID);
					}
					$this->bSuccessUnInstallDeps = true;
				}
				else {
					if( method_exists($DepModInstaller, 'getErrors') ) {
						$arInstallErrors = $DepModInstaller->getErrors();
						foreach($arInstallErrors as $error) {
							$this->arErrors[] = $error;
						}
					}
					$this->bSuccessUnInstallDeps = false;
				}
			}
		}
		return $this->bSuccessUnInstallDeps;
	}

	protected function getDepsList() {
		$arDepsList = array();
		if( is_dir($this->installDir."/modules") ) {
			if( ($dirSubModules = @opendir($this->installDir."/modules")) ) {
				while( ($depModID = readdir($dirSubModules)) !== false ) {
					if( $depModID == "." || $depModID == ".." ) {
						continue;
					}
					$arDepsList[$depModID] = str_replace('.', '_', $depModID);
				}
			}
		}
		return $arDepsList;
	}

	protected function prepareDBConnection() {
		global $APPLICATION, $DB, $DBType;
		if (defined('MYSQL_TABLE_TYPE') && strlen(MYSQL_TABLE_TYPE) > 0) {
			$DB->Query("SET table_type = '" . MYSQL_TABLE_TYPE . "'", true);
		}
		if (defined('BX_UTF') && BX_UTF === true) {
			$DB->Query('SET NAMES "utf8"');
			//$DB->Query('SET sql_mode=""');
			$DB->Query('SET character_set_results=utf8');
			$DB->Query('SET collation_connection = "utf8_unicode_ci"');
		}
	}

	public function registerModule() {
		if( !IsModuleInstalled($this->MODULE_ID) ) {
			RegisterModule($this->MODULE_ID);
		}
	}
	public function unRegisterModule() {
		if( IsModuleInstalled($this->MODULE_ID) ) {
			UnRegisterModule($this->MODULE_ID);
		}
	}
	public function isInstalledModule() {
		return IsModuleInstalled($this->MODULE_ID);
	}

	static public function getModuleCurDir() {
		static $modCurDir = null;
		if ($modCurDir === null) {
			$modCurDir = str_replace("\\", "/", __FILE__);
			// 18 = strlen of "/install/index.php"
			$modCurDir = substr($modCurDir, 0, strlen($modCurDir) - 18);
		}
		return $modCurDir;
	}
	static public function includeLangFile() {
		global $MESS;
		@include(GetLangFileName(self::getModuleCurDir() . "/lang/", "/install/index.php"));
	}
}
