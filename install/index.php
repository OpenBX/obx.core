<?php
/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @License GPLv3                  **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

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
		if (is_file($this->installDir . "/install_files.php")) {
			require($this->installDir . "/install_files.php");
		}
		return true;
	}
	public function UnInstallFiles() {
		if (is_file($this->installDir . "/uninstall_files.php")) {
			require($this->installDir . "/uninstall_files.php");
		}
		return true;
	}

	public function InstallDB() {
		global $DB, $DBType;
		if( is_file($this->installDir.'/db/'.$DBType.'/install.sql') ) {
			$this->prepareDBConnection();
			$arErrors = $DB->RunSQLBatch($this->installDir.'/db/'.$DBType.'/install.sql');
			if( is_array($arErrors) && count($arErrors)>0 ) {
				$this->arErrors = $arErrors;
				return false;
			}
		}
		return true;
	}
	public function UnInstallDB() {
		global $DB, $DBType;
		if( is_file($this->installDir.'/db/'.$DBType.'/uninstall.sql') ) {
			$this->prepareDBConnection();
			$arErrors = $DB->RunSQLBatch($this->installDir.'/db/'.$DBType.'/uninstall.sql');
			if( is_array($arErrors) && count($arErrors)>0 ) {
				$this->arErrors = $arErrors;
				return false;
			}
		}
		return true;
	}

	public function InstallEvents() { return true; }
	public function UnInstallEvents() { return true; }
	public function InstallTasks() { return true; }
	public function UnInstallTasks() { return true; }
	public function InstallData() { return true; }
	public function UnInstallData() { return true; }

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
					}
				}
			}
		}
		return true;
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
				}
			}
		}
		return true;
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
?>