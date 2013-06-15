<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

class OBX_Build {

	protected $_arResources = array();
	protected $_arIBlockData = array();
	protected $_moduleName = null;
	protected $_moduleClass = null;

	protected $_bInit = false;
	protected $_bPrologBXIncluded = false;
	protected $_bResourcesFileParsed = false;
	protected $_docRootDir = null;
	protected $_selfFolder = null;
	protected $_selfDir = null;
	protected $_modulesFolder = null;
	protected $_modulesDir = null;
	protected $_bxRootFolder = '/bitrix';
	protected $_bxRootDir = null;
	protected $_arDepModules = array();
	protected $_ParentModule = null;

	function __construct($moduleName, OBX_Build $ParentModule = null) {
		error_reporting(E_ALL ^ E_NOTICE);

		$this->_selfDir = dirname(__FILE__);
		$this->_selfDir = str_replace(array("\\", "//"), "/", $this->_selfDir);
		$this->_selfFolder = '';
		$this->_modulesFolder = $this->_bxRootFolder.'/modules';
		$arrTmp = explode($this->_modulesFolder, $this->_selfDir);
		$this->_docRootDir = $arrTmp[0];
		$_SERVER["DOCUMENT_ROOT"] = $this->_docRootDir;
		$this->_selfFolder = $this->_modulesFolder.$arrTmp[1];
		$this->_bxRootDir = $this->_docRootDir.$this->_bxRootFolder;
		$this->_modulesDir = $this->_docRootDir.$this->_modulesFolder;

		// пришлось сделать так, поскольку в ядре битрикс данный файл подключается через require_once
		// потому для дальнейшего подключения в билдере ядра битрикс простой require не подходит
		$dbConnCode = file_get_contents($this->_bxRootDir.'/php_interface/dbconn.php');
		$dbConnCode = preg_replace('~^[\s\S]*?\<\?(?:php)?~im', '', $dbConnCode);
		$dbConnCode = preg_replace('~\?\>[\s\S]*?$~im', '', $dbConnCode);
		eval($dbConnCode);


		if($ParentModule instanceof self) {
			if($ParentModule->isInit() == true) {
				$this->_ParentModule = $ParentModule;
			}
		}
		$this->reInit($moduleName);
	}

	public function reInit($moduleName = null) {
		if($moduleName == null) {
			if($this->_moduleName == null) {
				echo "Error: can't reInitialize noname module";
				return false;
			}
			$moduleName = $this->_moduleName;
		}
		if( is_dir($this->_modulesDir."/".$moduleName) ) {
			$this->_moduleClass = str_replace('.', '_', $moduleName);
			$this->_arResources = array(
				'RESOURCES' => array(),
				'DEPENDENCIES' => array()
			);
			$this->_moduleName = $moduleName;
		}
		$this->parseResourcesFile();
		$this->findResourcesFiles();
	}

	public function isInit() {
		return ($this->_moduleName == null)?false:true;
	}
	public function getModuleArray() {
		return $this->_arResources;
	}
	public function getModuleName() {
		return $this->_moduleName;
	}
	public function getModuleClass() {
		return $this->_moduleClass;
	}

	protected function _includeProlog() {
		if( !$this->_bPrologBXIncluded ) {
			require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
			global $DB, $DBType;
			$DBType = strtolower($DB->type);
			$this->_bPrologBXIncluded = true;
		}
	}

	public function isDependencyExists($moduleName) {
		if($moduleName == $this->_moduleName) {
			return true;
		}
		foreach($this->_arDepModules as $depModName => $DepModule) {
			if($moduleName == $depModName && $DepModule instanceof self) {
				return true;
			}
		}
		if($this->_ParentModule instanceof self) {
			return $this->_ParentModule->isDependencyExists($moduleName);
		}
		return false;
	}
	protected function addDependency($moduleName) {
		if( !preg_match('~[a-zA-Z0-9]+\.[a-zA-Z0-9]+~', $moduleName) ) {
			if( !is_dir($this->_modulesDir."/".$moduleName) ) {
				return false;
			}
		}
		if($this->_ParentModule instanceof self) {
			$this->_ParentModule->addDependency($moduleName);
		}
		$this->_arDepModules[$moduleName] = new self($moduleName, $this);
		return true;
	}

	public function parseResourcesFile() {
		if( !$this->isInit() ) {
			echo "Error: Build system not initialized!\n";
			return false;
		}
		$buildModuleDir = $this->_modulesDir."/".$this->_moduleName;

		if( is_file($buildModuleDir.'/install/resources.php') ) {
			$strResources = file_get_contents($buildModuleDir.'/install/resources.php');
			$arTmpResources = explode("\n", $strResources);
			//rint_r($arTmpResources);
			$configSection = '__UNKNOWN__';
			$lineNumber = 0;

			$this->_arResources = array();
			$this->_arDepModules = array();

			foreach($arTmpResources as $strResource) {
				$lineNumber++;
				$strResource = trim($strResource);
				if( strlen($strResource)<1 ) {
					continue;
				}
				if( substr($strResource, 0, 1) == "#" ) {
					continue;
				}
				if( substr($strResource, 0, 1) == "[" ) {
					if( preg_match('~\[\s*RESOURCES\s*\]~', $strResource) ) {
						$configSection = 'RESOURCES';
					}
					elseif( preg_match('~\[\s*DEPENDENCIES\s*\]~', $strResource) ) {
						$configSection = 'DEPENDENCIES';
					}
					elseif( preg_match('~\[\s*IBLOCK\_DATA\s*\]~', $strResource) ) {
						$configSection = 'IBLOCK_DATA';
					}
					elseif(preg_match('~\[\s*([0-9A-Za-z\_\-\.]*)\s*\]~', $strResource)) {
						$configSection = '__UNKNOWN__';
					}
					continue;
				}

				if($configSection == 'RESOURCES') {
					$arTmpResource = explode('::', $strResource);
					if( count($arTmpResource)<3 ) {
						//echo "Parse resource \"$buildModuleDir/install/resources.php\" error in line $lineNumber\n";
						continue;
					}
					$arResource['OPTIONS'] = array(
						'BUILD_ONLY' => false,
						'NOT_UNINSTALL' => false
					);
					if( strpos($arTmpResource[0], "!") !== false ) {
						$arTmpResourceOpts = explode("!", $arTmpResource[0]);
						$arTmpResource[0] = array_pop($arTmpResourceOpts);
						foreach($arTmpResourceOpts as &$strResOpt) {
							$strResOpt = trim($strResOpt);
							if( isset($arResource['OPTIONS'][$strResOpt]) ) {
								$arResource['OPTIONS'][$strResOpt] = true;
							}
						}
					}
					$arResource["INSTALL_FOLDER"] = trim($arTmpResource[0]);
					$arResource["PATTERN"] = trim($arTmpResource[1]);
					$arResource["TARGET_FOLDER"] = trim($arTmpResource[2]);

					$arResource["INSTALL_FOLDER"] = rtrim(str_replace(
						array(
							'%MODULE_FOLDER%',
							'%INSTALL_FOLDER%',
							'%BX_ROOT%'
						),
						array(
							$this->_modulesFolder.'/'.$this->_moduleName,
							$this->_modulesFolder.'/'.$this->_moduleName.'/install',
							$this->_bxRootFolder
						),
						$arResource["INSTALL_FOLDER"]
					), '/');
					$arResource["TARGET_FOLDER"] = rtrim(str_replace(
						array(
							'%MODULE_FOLDER%',
							'%INSTALL_FOLDER%',
							'%BX_ROOT%'
						),
						array(
							$this->_modulesFolder.'/'.$this->_moduleName,
							$this->_modulesFolder.'/'.$this->_moduleName.'/install',
							$this->_bxRootFolder
						),
						$arResource["TARGET_FOLDER"]
					), '/');

					$this->_arResources[] = $arResource;
				}
				elseif($configSection == 'DEPENDENCIES') {
					$subModuleName = $strResource;
					$this->addDependency($subModuleName);
					$debug = true;
				}
				elseif($configSection == 'IBLOCK_DATA') {
					$arTmpResource = explode('::', $strResource);
					if( count($arTmpResource)<3 ) {
						//echo "Parse resource \"$buildModuleDir/install/resources.php\" error in line $lineNumber\n";
						continue;
					}
					$arIBlockResource = array(
						'IBLOCK_CODE' => null,
						'IBLOCK_ID' => null,
						'EXPORT_PATH' => null
					);
					$arTmpIBlockResource = explode('::', $strResource);
					$arIBlockResource['IBLOCK_CODE'] = trim($arTmpIBlockResource[0]);
					$arIBlockResource['EXPORT_PATH'] = trim($arTmpIBlockResource[1]);
					$arIBlockResource['XML_FILE'] = trim($arTmpIBlockResource[2]);
					$arIBlockResource['EDIT_FORM_FILE'] = trim($arTmpIBlockResource[3]);
					$arIBlockResource["EXPORT_PATH"] = rtrim(str_replace(
						array(
							'%MODULE_FOLDER%',
							'%INSTALL_FOLDER%',
							'%BX_ROOT%'
						),
						array(
							$this->_modulesFolder.'/'.$this->_moduleName,
							$this->_modulesFolder.'/'.$this->_moduleName.'/install',
							$this->_bxRootFolder
						),
						$arIBlockResource["EXPORT_PATH"]
					), '/');
					$this->addIBlockData($arIBlockResource);
				}
			}
		}
	}

	public function findResourcesFiles() {
		if( count($this->_arResources) ) {
			foreach($this->_arResources as &$arResource) {
				//rint_r($arResource);
				$strTargetFileNamePattern = rtrim($arResource['TARGET_FOLDER'], '/').'/'.$arResource['PATTERN'];
				$strInstallFileNamePattern = rtrim($arResource['INSTALL_FOLDER'], '/').'/'.$arResource['PATTERN'];
				$strTargetFullPathPattern = $this->_docRootDir.$strTargetFileNamePattern;
				$strInstallFullPathPattern = $this->_docRootDir.$strInstallFileNamePattern;
				$arTargetFiles = glob($strTargetFullPathPattern);
				$arInstallFiles = glob($strInstallFullPathPattern);

				$arResource['FILES'] = array();
				$arResource['INSTALL_FILES_EXIST'] = array();
				$arResource['TARGET_FILES_EXIST'] = array();

				foreach($arInstallFiles as $installFileFullPath) {
					$fsEntry = str_replace($this->_docRootDir.$arResource['INSTALL_FOLDER'], '', $installFileFullPath);
					$fsEntry = trim($fsEntry, '/');
					if( !in_array($fsEntry, $arResource['FILES']) ) {
						$arResource['FILES'][] = $fsEntry;
					}
					$arResource['INSTALL_FILES_EXIST'][] = str_replace($this->_docRootDir, '', $installFileFullPath);

				}
				foreach($arTargetFiles as $targetFileFullPath) {
					$fsEntry = str_replace($this->_docRootDir.$arResource['TARGET_FOLDER'], '', $targetFileFullPath);
					$fsEntry = trim($fsEntry, '/');
					if( !in_array($fsEntry, $arResource['FILES']) ) {
						$arResource['FILES'][] = $fsEntry;
					}
					$arResource['TARGET_FILES_EXIST'][] = str_replace($this->_docRootDir, '', $targetFileFullPath);
				}

			}
		}
	}

	public function installResources() {
		if( count($this->_arDepModules) ) {
			foreach($this->_arDepModules as $DependencyModule) {
				self::CopyDirFilesEx(
					 $this->_modulesDir.'/'.$this->_moduleName.'/install/modules/'.$DependencyModule->getModuleName()
					,$this->_modulesDir.'/'.$DependencyModule->getModuleName()
					,true, true, FALSE, 'modules'
				);
				$DependencyModule->reInit();
				$DependencyModule->installResources();
			}
		}
		if( count($this->_arResources)>0 ) {
			foreach($this->_arResources as &$arResource) {
				if($arResource['BUILD_ONLY']) {
					continue;
				}
				foreach($arResource['FILES'] as $fsEntryName) {
					self::CopyDirFilesEx(
						  $this->_docRootDir.$arResource['INSTALL_FOLDER'].'/'.$fsEntryName
						, $this->_docRootDir.$arResource['TARGET_FOLDER'].'/'
						, true, true);
				}
			}
		}
	}

	static protected function isEmptyDir($fullPath, $bRecursiveCheck4Files = false) {
		$bEmpty = true;
		if(!is_dir($fullPath)) {
			return false;
		}
		if( ! ($handle = opendir($fullPath)) ) {
			return false;
		}
		while( ($fsEntry = readdir($handle)) !== false ) {
			if( $fsEntry == '.' || $fsEntry == '..' ) continue;
			if( is_dir($fullPath.'/'.$fsEntry) ) {
				if($bRecursiveCheck4Files) {
					$bEmpty = self::isEmptyDir($fullPath.'/'.$fsEntry, true);
				}
				else {
					$bEmpty = false;
				}
			}
			else {
				$bEmpty = false;
			}
		}
		return $bEmpty;
	}

//	static protected function deleteEmptyFSBranches($fullPath) {
//		while (($fsEntry = readdir($fullPath)) !== false) {
//			if( $fsEntry == '.' || $fsEntry == '..' ) continue;
//			if( is_dir($fullPath.'/'.$fsEntry) ) {
//				if( self::isEmptyDir($fullPath.'/'.$fsEntry, false) ) {
//
//				}
//			}
//			else {
//				$bEmpty = false;
//			}
//		}
//		return $bEmpty;
//	}

	public function backInstallResources() {
		if( count($this->_arDepModules) ) {
			foreach($this->_arDepModules as $DependencyModule) {
				/** @var OBX_Build $DependencyModule */
				$DependencyModule->backInstallResources();
				$DependencyModule->reInit();
				$DependencyModule->generateInstallCode();
				$DependencyModule->generateUnInstallCode();
				$DependencyModule->generateBackInstallCode();
				self::deleteDirFilesEx($this->_modulesDir.'/'.$this->getModuleName().'/install/modules/'.$DependencyModule->getModuleName(), true);
				self::CopyDirFilesEx(
					 $this->_modulesDir.'/'.$DependencyModule->getModuleName()
					,$this->_modulesDir.'/'.$this->getModuleName().'/install/modules/'.$DependencyModule->getModuleName()
					,true, true, FALSE, 'modules'
				);
				@unlink($this->_modulesDir.'/'.$this->getModuleName().'/install/modules/'.$DependencyModule->getModuleName().'/.git');
			}
		}
		if( count($this->_arResources)>0 ) {
			foreach($this->_arResources as &$arResource) {
				if(
					$arResource['INSTALL_FOLDER'] != '/bitrix/modules/'.$this->getModuleName().'/install'
					&& $arResource['INSTALL_FOLDER'] != '/bitrix/modules/'.$this->getModuleName().'/install/'
				) {
					foreach($arResource['INSTALL_FILES_EXIST'] as $installFSEntry) {
						self::deleteDirFilesEx($installFSEntry);
					}
					if( self::isEmptyDir($this->_docRootDir.$arResource['INSTALL_FOLDER'], true) ) {
						self::deleteDirFilesEx($arResource['INSTALL_FOLDER']);
					}
				}
			}
			foreach($this->_arResources as &$arResource) {
				foreach($arResource['FILES'] as $fsEntryName) {
					if( ! is_dir($this->_docRootDir.$arResource['INSTALL_FOLDER']) ) {
						@mkdir($this->_docRootDir.$arResource['INSTALL_FOLDER'], BX_DIR_PERMISSIONS, true);
					}
					self::CopyDirFilesEx(
						 $this->_docRootDir.$arResource['TARGET_FOLDER'].'/'.$fsEntryName
						,$this->_docRootDir.$arResource['INSTALL_FOLDER'].'/'
						,true, true);
					$debug = 1;
				}
			}
		}
	}

	public function generateInstallCode() {
		$installFile = 'install_files.php';
		$installDepsFile = 'install_deps.php';
		$installCode = '';
		$installDepsCode = '';
		if( count($this->_arDepModules) ) {
			foreach($this->_arDepModules as $DependencyModule) {
				$installDepsCode .=			'OBX_CopyDirFilesEx('
												.'$_SERVER["DOCUMENT_ROOT"]'
													.'.BX_ROOT'
													.'."/modules/'.$this->_moduleName
													.'/install/modules/'.$DependencyModule->getModuleName().'"'
												.', $_SERVER["DOCUMENT_ROOT"]'
													.'.BX_ROOT'
													.'."/modules/'.$DependencyModule->getModuleName().'"'
											.', true, true);'."\n";
				$depInstallFilePathCode =	'$_SERVER["DOCUMENT_ROOT"]'
												.'.BX_ROOT."/modules/'
												.$DependencyModule->getModuleName()
												.'/install/'.$installFile.'"';
				$installDepsCode .=			'if( is_file('.$depInstallFilePathCode.') ) {'."\n"
												."\t".'require_once '.$depInstallFilePathCode.";\n"
											."}\n";
			}
		}
		if( count($this->_arResources)>0 ) {
			foreach($this->_arResources as &$arResource) {
				if($arResource['OPTIONS']['BUILD_ONLY']) {
					continue;
				}
				foreach($arResource['FILES'] as $fsEntryName) {
					$installCode .= 'OBX_CopyDirFilesEx('
						.'$_SERVER["DOCUMENT_ROOT"]."'
							.$arResource['INSTALL_FOLDER'].'/'.$fsEntryName
						.'", $_SERVER["DOCUMENT_ROOT"]."'
							.$arResource['TARGET_FOLDER'].'/'
						.'", true, true);'
						."\n";
				}
			}
		}
		if( strlen($installCode)>0 ) {
			$installCode = 	 $this->getHeaderCodeOfInstallFile()
							.$this->getCodeOfCopyFunction()
							.$installCode
							.$this->getFooterCodeOfInstallFile();
			file_put_contents($this->_modulesDir.'/'.$this->_moduleName.'/install/'.$installFile, $installCode);
		}
		else {
			file_put_contents($this->_modulesDir.'/'.$this->_moduleName.'/install/'.$installFile, "<?php\n?>");
		}
		if( strlen($installDepsCode)>0 ) {
			$installDepsCode = 	 $this->getHeaderCodeOfInstallFile()
								.$this->getCodeOfCopyFunction()
								.$installDepsCode
								.$this->getFooterCodeOfInstallFile();
			file_put_contents($this->_modulesDir.'/'.$this->_moduleName.'/install/'.$installDepsFile, $installDepsCode);
		}
		else {
			file_put_contents($this->_modulesDir.'/'.$this->_moduleName.'/install/'.$installDepsFile, "<?php\n?>");
		}
	}

	public function generateUnInstallCode() {
		$unInstallFile = 'uninstall_files.php';
		if( count($this->_arResources)>0 ) {
			$unInstallCode = $this->getHeaderCodeOfInstallFile();
			foreach($this->_arResources as &$arResource) {
				if(
					$arResource['OPTIONS']['BUILD_ONLY']
					||
					$arResource['OPTIONS']['NOT_UNINSTALL']
				) {
					continue;
				}
				foreach($arResource['FILES'] as $fsEntryName) {
					$unInstallCode .= 'DeleteDirFilesEx('
						.'"'.$arResource['TARGET_FOLDER'].'/'.$fsEntryName
					.'");'
					."\n";
				}
			}
			$unInstallCode .= $this->getFooterCodeOfInstallFile();
			file_put_contents($this->_modulesDir.'/'.$this->_moduleName.'/install/'.$unInstallFile, $unInstallCode);
		}
	}

	public function generateBackInstallCode() {
		$backInstallFile = 'get_back_installed_files.php';
		$backInstallCode = '';
		if( count($this->_arDepModules) ) {
			foreach($this->_arDepModules as $DependencyModule) {
				$depBackInstallFilePathCode =
					'$_SERVER["DOCUMENT_ROOT"]'
						.'.BX_ROOT."/modules/'
						.$DependencyModule->getModuleName()
						.'/install/'.$backInstallFile.'"';
				$backInstallCode .=
					'if( is_file('.$depBackInstallFilePathCode.') ) {'."\n"
					."\t".'require_once '.$depBackInstallFilePathCode.";\n"
					."}\n";
				$backInstallCode .= 'DeleteDirFilesEx("'
					.'/bitrix/modules/'.$this->getModuleName()
					.'/install/modules/'.$DependencyModule->getModuleName()
				.'");'."\n";
				$backInstallCode .=
					'OBX_CopyDirFilesEx('
						.'$_SERVER["DOCUMENT_ROOT"]'
							.'.BX_ROOT'
							.'."/modules/'.$DependencyModule->getModuleName().'"'
						.', $_SERVER["DOCUMENT_ROOT"]'
							.'.BX_ROOT'
							.'."/modules/'.$this->_moduleName
							.'/install/modules/"'
						.', true, true, FALSE, "modules");'."\n";
			}
		}
		if( count($this->_arResources)>0 ) {
			foreach($this->_arResources as &$arResource) {
				if(
					$arResource['INSTALL_FOLDER'] != '/bitrix/modules/'.$this->getModuleName().'/install'
					&& $arResource['INSTALL_FOLDER'] != '/bitrix/modules/'.$this->getModuleName().'/install/'
				) {
					foreach($arResource['INSTALL_FILES_EXIST'] as $installFSEntry) {
						$backInstallCode .= 'DeleteDirFilesEx("'.$installFSEntry."\");\n";
					}
					if( self::isEmptyDir($this->_docRootDir.$arResource['INSTALL_FOLDER'], true) ) {
						$backInstallCode .= 'DeleteDirFilesEx("'.$arResource['INSTALL_FOLDER']."\");\n";
					}
				}
			}
			$arFolderCreated = array();
			foreach($this->_arResources as &$arResource) {
				foreach($arResource['FILES'] as $fsEntryName) {
					if( !in_array($arResource['INSTALL_FOLDER'], $arFolderCreated) ) {
						$backInstallCode .= 'if( ! is_dir($_SERVER["DOCUMENT_ROOT"]."'.$arResource['INSTALL_FOLDER'].'") ) {'
							."\n\t".'@mkdir($_SERVER["DOCUMENT_ROOT"]."'.$arResource['INSTALL_FOLDER'].'", BX_DIR_PERMISSIONS, true);'
							."\n".'}'."\n";
						$arFolderCreated[] = $arResource['INSTALL_FOLDER'];
					}
					$backInstallCode .= 'OBX_CopyDirFilesEx('
						.'$_SERVER["DOCUMENT_ROOT"]."'
							.$arResource['TARGET_FOLDER'].'/'.$fsEntryName
						.'", $_SERVER["DOCUMENT_ROOT"]."'
							.$arResource['INSTALL_FOLDER'].'/'
						.'", true, true);'
						."\n";
				}
			}
		}
		if( strlen($backInstallCode)>0 ) {
			$backInstallCode = 	 $this->getHeaderCodeOfInstallFile()
								.$this->getCodeOfCopyFunction()
								.$backInstallCode
								.$this->getFooterCodeOfInstallFile();
			file_put_contents($this->_modulesDir.'/'.$this->_moduleName.'/install/'.$backInstallFile, $backInstallCode);
		}
	}

	static function CopyDirFilesEx($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $strExclude = "") {
		$path_from = str_replace(array("\\", "//"), "/", $path_from);
		$path_to = str_replace(array("\\", "//"), "/", $path_to);
		if(is_file($path_from) && !is_file($path_to)) {
			if( self::CheckDirPath($path_to) ) {
				$file_name = substr($path_from, strrpos($path_from, "/")+1);
				$path_to = rtrim($path_to, '/');
				$path_to .= '/'.$file_name;
				//cho __METHOD__.": ".$path_from." => ".$path_to."\n";
				return self::CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
			}
		}
		if( is_dir($path_from) && substr($path_to, strlen($path_to)-1) == '/' ) {
			$folderName = substr($path_from, strrpos($path_from, '/')+1);
			$path_to .= $folderName;
		}
		return self::CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
	}

	protected function getHeaderCodeOfInstallFile() {
		return '<?php
$bConnectEpilog = false;
if(!defined("BX_ROOT")) {
	$bConnectEpilog = true;
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	global $USER;
	if( !$USER->IsAdmin() ) return false;
}
'."\n";
	}

	protected function getFooterCodeOfInstallFile() {
		return 'if($bConnectEpilog) require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");'."\n?>";
	}

	protected function getCodeOfCopyFunction() {
		return 'if(!function_exists("OBX_CopyDirFilesEx")) {
	function OBX_CopyDirFilesEx($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $strExclude = "") {
		$path_from = str_replace(array("\\\\", "//"), "/", $path_from);
		$path_to = str_replace(array("\\\\", "//"), "/", $path_to);
		if(is_file($path_from) && !is_file($path_to)) {
			if( CheckDirPath($path_to) ) {
				$file_name = substr($path_from, strrpos($path_from, "/")+1);
				$path_to .= $file_name;
				return CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
			}
		}
		if( is_dir($path_from) && substr($path_to, strlen($path_to)-1) == "/" ) {
			$folderName = substr($path_from, strrpos($path_from, "/")+1);
			$path_to .= $folderName;
		}
		return CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
	}
}'."\n";
	}

	protected function getCodeOfInstallDepModule() {
		return 'if( !function_exists("OBX_InstallDependencyModule") ) {
	function OBX_InstallDependencyModule($parentModuleName, $moduleName) {

	}
}
';
	}

	public function generateMD5FilesList() {
		//if(  )
	}

	static public function CheckDirPath($path, $bPermission=true)
	{
		$path = str_replace(array("\\", "//"), "/", $path);

		//remove file name
		if(substr($path, -1) != "/")
		{
			$p = strrpos($path, "/");
			$path = substr($path, 0, $p);
		}

		$path = rtrim($path, "/");

		if(!file_exists($path))
			return mkdir($path, BX_DIR_PERMISSIONS, true);
		else
			return is_dir($path);
	}

	static public function CopyDirFiles($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $strExclude = "")
	{
		if (strpos($path_to."/", $path_from."/")===0 || realpath($path_to) === realpath($path_from))
			return false;

		if (is_dir($path_from))
		{
			self::CheckDirPath($path_to."/");
		}
		elseif(is_file($path_from))
		{
			$p = self::bxstrrpos($path_to, "/");
			$path_to_dir = substr($path_to, 0, $p);
			self::CheckDirPath($path_to_dir."/");

			if (file_exists($path_to) && !$ReWrite)
				return False;

			@copy($path_from, $path_to);
			if(is_file($path_to))
				@chmod($path_to, BX_FILE_PERMISSIONS);

			if ($bDeleteAfterCopy)
				@unlink($path_from);

			return True;
		}
		else
		{
			return True;
		}

		if ($handle = @opendir($path_from))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (strlen($strExclude)>0 && substr($file, 0, strlen($strExclude))==$strExclude)
					continue;

				if (is_dir($path_from."/".$file) && $Recursive)
				{
					self::CopyDirFiles($path_from."/".$file, $path_to."/".$file, $ReWrite, $Recursive, $bDeleteAfterCopy, $strExclude);
					if ($bDeleteAfterCopy)
						@rmdir($path_from."/".$file);
				}
				elseif (is_file($path_from."/".$file))
				{
					if (file_exists($path_to."/".$file) && !$ReWrite)
						continue;

					@copy($path_from."/".$file, $path_to."/".$file);
					@chmod($path_to."/".$file, BX_FILE_PERMISSIONS);

					if($bDeleteAfterCopy)
						@unlink($path_from."/".$file);
				}
			}
			@closedir($handle);

			if ($bDeleteAfterCopy)
				@rmdir($path_from);

			return true;
		}

		return false;
	}

//	static function DeleteDirFilesEx($path)
//	{
//		if(strlen($path) == 0 || $path == '/')
//			return false;
//
//		$full_path = $_SERVER["DOCUMENT_ROOT"].$path;
//
//		$f = true;
//		if(is_file($full_path) || is_link($full_path))
//		{
//			if(@unlink($full_path))
//				return true;
//			return false;
//		}
//		elseif(is_dir($full_path))
//		{
//			if($handle = opendir($full_path))
//			{
//				while(($file = readdir($handle)) !== false)
//				{
//					if($file == "." || $file == "..")
//						continue;
//
//					if(!self::DeleteDirFilesEx($path."/".$file))
//						$f = false;
//				}
//				closedir($handle);
//			}
//			if(!@rmdir($full_path))
//				return false;
//			return $f;
//		}
//		return false;
//	}

	/**
	 * Работает так же как битриксовская, но в отличие от неё, может принимать полный путь.
	 * @param String $path - путь
	 * @param bool $bIsPathFull - абсолюьный=true, относительный=false
	 * @return boolean
	 */
	static public function deleteDirFilesEx($path, $bIsPathFull = false)
	{
		if(strlen($path) == 0 || $path == '/')
			return false;
		if(!$bIsPathFull) {
			$full_path = $_SERVER["DOCUMENT_ROOT"].$path;
		}
		else {
			$full_path = $path;
		}

		$f = true;
		if(is_file($full_path) || is_link($full_path))
		{
			if(@unlink($full_path))
				return true;
			return false;
		}
		elseif(is_dir($full_path))
		{
			if($handle = opendir($full_path))
			{
				while(($file = readdir($handle)) !== false)
				{
					if($file == "." || $file == "..")
						continue;

					if(!self::deleteDirFilesEx($path."/".$file, $bIsPathFull))
						$f = false;
				}
				closedir($handle);
			}
			if(!@rmdir($full_path))
				return false;
			return $f;
		}
		return false;
	}

	static function DeleteDirFiles($frDir, $toDir, $arExept = array())
	{
		if(is_dir($frDir))
		{
			$d = dir($frDir);
			while ($entry = $d->read())
			{
				if ($entry=="." || $entry=="..")
					continue;
				if (in_array($entry, $arExept))
					continue;
				@unlink($toDir."/".$entry);
			}
			$d->close();
		}
	}

	function bxstrrpos($haystack, $needle)
	{
		if(defined("BX_UTF"))
		{
			$ln = strlen($needle);
			for($i=strlen($haystack)-$ln; $i>=0; $i--)
				if(substr($haystack, $i, $ln)==$needle)
					return $i;
			return false;
		}
		$index = strpos(strrev($haystack), strrev($needle));
		if($index === false)
			return false;
		$index = strlen($haystack) - strlen($needle) - $index;
		return $index;
	}

	/**
	 * Заменяет параметры компонентов в файлах, указанных в конфиге
	 * @param $configPath
	 * Пример конфига
	 * <?php
	 * 		return array(
	 * 			'/ru/index.php' => array(
	 * 				array(
	 * 					'NAME' => 'obx.market:catalog',
	 * 					'TEMPLATE' => '',
	 * 					'NUMBER' => 0,
	 * 					'PARAMS' => array(
	 * 						'IBLOCK_TYPE' => '#DVT_PIZZA_CATALOG_IBLOCK_TYPE#',
	 * 						'IBLOCK_ID' => '#DVT_PIZZA_CATALOG_IBLOCK_ID#',
	 * 					)
	 * 				),
	 * 			),
	 * 			'/ru/catalog/pizza/index.php' => array(
	 * 				array(
	 * 					'NAME' => 'obx.market:catalog',
	 * 					'TEMPLATE' => '',
	 * 					'NUMBER' => 0,
	 * 					'PARAMS' => array(
	 * 						'IBLOCK_TYPE' => '#DVT_PIZZA_PIZZA_CATALOG_IBLOCK_TYPE#',
	 * 						'IBLOCK_ID' => '#DVT_PIZZA_PIZZA_CATALOG_IBLOCK_ID#',
	 * 					)
	 * 				),
	 * 			),
	 * 			'/ru/catalog/pizza/.catalog-child.menu_ext.php' => array(
	 * 				array(
	 * 					'NAME' => 'obx.market:catalog',
	 * 					'TEMPLATE' => '',
	 * 					'NUMBER' => 0,
	 * 					'PARAMS' => array(
	 * 						'IBLOCK_TYPE' => '#DVT_PIZZA_PIZZA_CATALOG_IBLOCK_TYPE#',
	 * 						'IBLOCK_ID' => '#DVT_PIZZA_PIZZA_CATALOG_IBLOCK_ID#',
	 * 					)
	 * 				),
	 * 			),
	 * 		);
	 */
	public function replaceComponentParameters($configPath) {
		$configPath = rtrim(str_replace(
			array(
				'%MODULE_FOLDER%',
				'%INSTALL_FOLDER%',
				'%BX_ROOT%'
			),
			array(
				$this->_modulesFolder.'/'.$this->_moduleName,
				$this->_modulesFolder.'/'.$this->_moduleName.'/install',
				$this->_bxRootFolder
			),
			$configPath
		), '/');
		$path = dirname($configPath);
		$configPath = $this->_docRootDir.$configPath;
		$path = $this->_docRootDir.$path;

		if( file_exists($configPath) ) {
			$arComponentParamsPlaceholdersList = require($configPath);
			foreach($arComponentParamsPlaceholdersList as $pubFileRelPath => $arComponentReplaces) {
				self::__replaceComponentParameters($path.$pubFileRelPath, $arComponentReplaces);
			}
		}
	}

	static public function __replaceComponentParameters($path, $arComponentReplaces) {
		if( !file_exists($path) ) {
			return false;
		}
		$regComponent = '('
			.'\$APPLICATION\-\>IncludeComponent\((?:[\s\S]*?)\)\;'
		.')';
		$regParse4Components = '~[\s\S]*?'
			.$regComponent
		.'[\s\S]*?~mi';
		$fileContent = file_get_contents($path);
		preg_match_all($regParse4Components, $fileContent, $arMatches);
		$arComponents = $arMatches[1];
		$arComponentsRaw = array();
		//$evalString = '$arComponentsRaw = array();'."\n";
		$evalString = '';
		$regVariable = '#\=\>[\s]*?(\$[a-zA-Z]([a-zA-Z0-9\_]|(\["|"\]))*)#i';
		foreach($arComponents as $strComponentCall) {
			$strComponentCall = str_replace('$APPLICATION->IncludeComponent(', '$arComponentsRaw[] = array(', $strComponentCall)."\n";
			if( preg_match($regVariable, $strComponentCall) ) {
				$strComponentCall = preg_replace($regVariable, '=> \'$1\'', $strComponentCall);
			}
			$evalString .= $strComponentCall;
		}
		$component = null;
		eval($evalString);
		$arComponents = array();
		$componentIndex = 0;
		foreach($arComponentsRaw as &$arCmpRaw) {
			$arComponents[$componentIndex] = array(
				'NAME' => $arCmpRaw[0],
				'TEMPLATE' => (strlen(trim($arCmpRaw[1]))<1)?'.default':$arCmpRaw[1],
				'PARAMS' => $arCmpRaw[2],
				'INDEX' => $componentIndex
			);
			$componentIndex++;
		}

		$arComponentIndex = self::getListIndex($arComponents, array('NAME', 'TEMPLATE'), false, true);

		foreach($arComponentReplaces as &$arComponentReplace) {
			if( strlen(trim($arComponentReplace['TEMPLATE']))<1 ) {
				$arComponentReplace['TEMPLATE'] = '.default';
			}
			$cmpComplexKey = $arComponentReplace['NAME'].'_'.$arComponentReplace['TEMPLATE'];
			if( array_key_exists($cmpComplexKey, $arComponentIndex) ) {
				if( array_key_exists('NAME', $arComponentIndex[$cmpComplexKey]) && $arComponentReplace['NUMBER'] == 0) {
					$arComponentIndex[$cmpComplexKey]['PARAMS'] = array_merge(
						$arComponentIndex[$cmpComplexKey]['PARAMS'], $arComponentReplace['PARAMS']
					);
				}
				elseif( array_key_exists($arComponentReplace['NUMBER'], $arComponentIndex[$cmpComplexKey]) ) {
					$arComponentIndex[$cmpComplexKey][$arComponentReplace['NUMBER']]['PARAMS'] = array_merge(
						$arComponentIndex[$cmpComplexKey][$arComponentReplace['NUMBER']]['PARAMS'],
						$arComponentReplace['PARAMS']
					);
				}
			}
		}

		$arFileContent = preg_split('~'.$regComponent.'~im', $fileContent, -1, PREG_SPLIT_OFFSET_CAPTURE);
		if( count($arFileContent) < (count($arComponents)+1) ) {
			return false;
		}
		$newFileContent = '';
		foreach($arFileContent as $key => $arContentChank) {
			$newFileContent .= $arContentChank[0];
			if( array_key_exists($key, $arComponents) ) {
				$newFileContent .= '$APPLICATION->IncludeComponent('."\n";
				$newFileContent .= "\t".'"'.$arComponents[$key]['NAME'].'",'."\n";
				$newFileContent .= "\t".'"'.$arComponents[$key]['TEMPLATE'].'",'."\n";
				$newFileContent .= "\t".self::convertArray2PhpCode($arComponents[$key]['PARAMS'], "\t")."\n";
				$newFileContent .= ');';
			}
		}
		//echo $newFileContent;
		file_put_contents($path, $newFileContent);
	}



	/**
	 * Построить индекс массива
	 * @param $arList
	 * @param string | array $str_arKey - ключ по которому проиндексировать массив
	 * @param bool $bUniqueKeys
	 * @param bool $bSetReferences
	 * @return array
	 */
	static function getListIndex(&$arList, $str_arKey, $bUniqueKeys = true, $bSetReferences = false ) {
		$arListIndex = array();
		$complexKey = null;
		if( !is_array($str_arKey) ) {
			$str_arKey = array($str_arKey);
		}
		foreach($arList as &$arItem) {
			if( is_array($arItem) ) {
				$arItem['__THIS_IS_VALUE_ARRAY'] = true;
			}
			$bFirst = true;
			$complexKey = '';
			foreach($str_arKey as &$keyItem) {
				$complexKey .= ($bFirst?'':'_');
				$bFirst = false;
				if( ! array_key_exists($keyItem, $arItem) || empty($arItem[$keyItem]) ) {
					$complexKey .= 'NULL';
				}
				else {
					$complexKey .= $arItem[$keyItem];
				}
			}

			if( $bUniqueKeys || !array_key_exists($complexKey, $arListIndex) ) {
				if($bSetReferences) {
					$arListIndex[$complexKey] = &$arItem;
				}
				else {
					$arListIndex[$complexKey] = $arItem;
				}
			}
			else {
				if( is_array($arListIndex[$complexKey]) && !array_key_exists('__THIS_IS_VALUE_ARRAY', $arListIndex[$complexKey]) ) {
					if($bSetReferences) {
						$arListIndex[$complexKey][] = &$arItem;
					}
					else {
						$arListIndex[$complexKey][] = $arItem;
					}
				}
				else {
					if($bSetReferences) {
						$arNowElementIsArray = array(&$arListIndex[$complexKey]);
						$arListIndex[$complexKey] = &$arNowElementIsArray;
						$arListIndex[$complexKey][] = &$arItem;
					}
					else {
						$arListIndex[$complexKey] = array($arListIndex[$complexKey]);
						$arListIndex[$complexKey][] = $arItem;
					}
				}
			}
		}
		self::__removeTmpDataFromListIndex($arListIndex);
		if(!$bSetReferences) {
			foreach($arList as &$arItem) {
				if( is_array($arItem) ) {
					unset($arItem['__THIS_IS_VALUE_ARRAY']);
				}
			}
		}
		return $arListIndex;
	}

	static protected function __removeTmpDataFromListIndex(&$arListIndex) {
		foreach($arListIndex as $key => &$arItem) {
			if( is_array($arItem) && !array_key_exists('__THIS_IS_VALUE_ARRAY', $arItem) ) {
				self::__removeTmpDataFromListIndex($arItem);
			}
			else {
				unset($arItem['__THIS_IS_VALUE_ARRAY']);
			}
		}
		return;
	}

	/**
	 * Возвращает строку с php-кодом массива переданного на вход
	 * @param Array $array - входной массив, для вывода в виде php-кода
	 * @param String $whiteOffset - отступ от начала каждй строки(для красоты)
	 * @return string
	 * @author Maksim S. Makarov aka pr0n1x
	 * @link https://code.google.com/p/scriptacid/
	 * @link https://code.google.com/p/scriptacid/source/browse/branches/0.1/scriptacid/core/lib/class.ComponentTools.php
	 * @license GPLv3
	 * @created 12 apr 2011
	 * @modified 4 jun 2013
	 */
	static protected function convertArray2PhpCode($array, $whiteOffset = '') {
		$strResult = "array(\n";
		foreach($array as $paramName => &$paramValue) {
			if(!is_array($paramValue)) {
				if( substr($paramValue, 0, 1) != '$' ) {
					$paramValue = '"'.$paramValue.'"';
				}
				$strResult .= $whiteOffset."\t\"".$paramName."\" => ".$paramValue.",\n";
			}
			else {
				$strResult .= $whiteOffset."\t\"".$paramName."\" => ".self::convertArray2PhpCode($paramValue, $whiteOffset."\t").",\n";
			}
		}
		$strResult .= $whiteOffset.")";
		return $strResult;
	}

	public function addIBlockData($arIBlockData) {
		if( !is_dir($this->_docRootDir.$arIBlockData['EXPORT_PATH']) ) {
			$bSuccess = @mkdir($this->_docRootDir.$arIBlockData['EXPORT_PATH'], BX_DIR_PERMISSIONS, true);
			if(!$bSuccess) {
				return false;
			}
		}
		if( strrpos($arIBlockData['XML_FILE'], '.xml' ) === false ) {
			$arIBlockData['XML_FILE'] = $arIBlockData['XML_FILE'].'.xml';
		}
		$arIBlockData['EXPORT_FULL_PATH'] = $this->_docRootDir.$arIBlockData['EXPORT_PATH'];
		$arIBlockData['EXPORT_WORK_DIR'] = '/'.str_replace('.xml', '', $arIBlockData['XML_FILE']).'_files/';
		$arIBlockData['EXPORT_WORK_DIR_FULL_PATH'] = $arIBlockData['EXPORT_FULL_PATH'].$arIBlockData['EXPORT_WORK_DIR'];
		$arIBlockData['XML_FILE_FULL_PATH'] = $arIBlockData['EXPORT_FULL_PATH'].'/'.$arIBlockData['XML_FILE'];

		$this->_arIBlockData[$arIBlockData['IBLOCK_CODE']] = $arIBlockData;
		return true;
	}

	protected function _exportIBlockXML($iblockCode) {
		if( !array_key_exists($iblockCode, $this->_arIBlockData) ) {
			echo "Iblock \"$iblockCode\" not found in resource file \n";
			return false;
		}
		$this->_includeProlog();
		CModule::IncludeModule('iblock');
		$rsIBlock = CIBlock::GetList(false, array('CODE' => $iblockCode));
		if( !($arIBlock = $rsIBlock->GetNext()) ) {
			echo "Iblock \"$iblockCode\" not found \n";
			return false;
		}
		$this->_arIBlockData[$iblockCode]['IBLOCK_ID'] = $arIBlock['ID'];
		$arIB = &$this->_arIBlockData[$iblockCode];

		self::deleteDirFilesEx($arIB['EXPORT_WORK_DIR_FULL_PATH'], true);
		unlink($arIB['XML_FILE_FULL_PATH']);

		$fpXmlFile = fopen($arIB['XML_FILE_FULL_PATH'], "ab");
		if(!$fpXmlFile) {
			echo "Can't create / open xml file \n";
			return false;
		}
		$start_time = time();
		$nextStep = array();
		$arSectionMap = false;
		$arPropertyMap = false;
		$arSectionFilter = array('IBLOCK_ID' => $arIB['IBLOCK_ID']);
		$arElementFilter = array('IBLOCK_ID' => $arIB['IBLOCK_ID']);
		$INTERVAL = 0;
		/**
		 * @var CIBlockCMLExport $obExport
		 */
		$obExport = new CIBlockCMLExport;
		if($obExport->Init($fpXmlFile, $arIB['IBLOCK_ID'], $nextStep, true, $arIB['EXPORT_FULL_PATH'], $arIB['EXPORT_WORK_DIR'])) {
			// <КоммерческаяИнформация>
			$obExport->StartExport();

				// <Классификатор>
				$obExport->StartExportMetadata();
					// <Свойства>
				 	$obExport->ExportProperties($arPropertyMap);
					// </Свойства>
					// <Группы>
					$result = $obExport->ExportSections(
						$arSectionMap,
						$start_time,
						$INTERVAL,
						$arSectionFilter
					);
					// </Группы>
				// </Классификатор>
				$obExport->EndExportMetadata();

				// <Каталог>
				$obExport->StartExportCatalog();
					// <Товары>
					$result = $obExport->ExportElements(
						$arPropertyMap,
						$arSectionMap,
						$start_time,
						$INTERVAL,
						0,
						$arElementFilter
					);
					// </Товары>
				// </Каталог>
				$obExport->EndExportCatalog();

			// </КоммерческаяИнформация>
			$obExport->EndExport();
		}
		else {
			echo "\nCan't initialize xml-export for \"{$arIB['IBLOCK_ID']}\".\n Perhaps Minimal access for IBlock lower than \"W\"\n";
		}
		if($fpXmlFile)
			fclose($fpXmlFile);
	}
	public function exportIBlockCML($iblockCode = null) {
		$bSuccess = true;
		if($iblockCode === null) {
			foreach($this->_arIBlockData as $iblockCode => &$arIB) {
				$bSuccess = $this->_exportIBlockXML($iblockCode) && $bSuccess;
			}
		}
		else {
			return $this->_exportIBlockXML($iblockCode);
		}
		return $bSuccess;
	}

	public function getIBlockListFormSettings() {

	}

	public function getIBlockFormSettings($iblockCode) {

	}

	public function exportIBlockFormSettings() {

	}


	public function processCommandOptions() {
		$arCommandOptions = getopt('', array(
			'build-cml::'
		));

		$arBuildXML4IBlocks = array();
		if( array_key_exists('build-cml', $arCommandOptions) ) {
			$arCommandOptions['build-cml'] = trim($arCommandOptions['build-cml']);
			if( strlen($arCommandOptions['build-cml']) > 0 ) {
				$arBuildXML4IBlocks = explode(',', $arCommandOptions['build-cml']);
				foreach($arBuildXML4IBlocks as $iblockCode) {
					$this->exportIBlockCML($iblockCode);
				}
			}
			else {
				$this->exportIBlockCML();
			}
		}
	}


}