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
	const BX_ROOT = '/bitrix';
	const BUILD_FOLDER = '/bitrix/modules.build';
	const MODULES_FOLDER = '/bitrix/modules';

	protected $_moduleName = null;
	protected $_moduleClass = null;
	protected $_version = null;
	protected $_versionDate = null;

	protected $_arConfigFiles = array(
		'%SELF_FOLDER%/module.obuild',
		'%BUILD_FOLDER%/release.obuild'
	);
	protected $_bInit = false;

	protected $_bPrologBXIncluded = false;
	protected $_bResourcesFileParsed = false;
	protected $_docRootDir = null;
	protected $_modulesDir = null;
	protected $_bxRootDir = null;
	protected $_selfFolder = null;
	protected $_selfDir = null;
	protected $_buildFolder = null;
	protected $_buildDir = null;

	protected $_arDepModules = array();
	protected $_ParentModule = null;

	protected $_arResources = array();
	protected $_arIBlockData = array();
	protected $_arRawLangCheck = array();
	protected $_arCompParamsConfig = array();

	protected $_releaseFolder = null;
	protected $_releaseDir = null;
	protected $_arReleases = array();
	protected $_lastPubReleaseVersion = null;
	protected $_dependencyVersion = null;

	function __construct($moduleName, self $ParentModule = null) {
		error_reporting(E_ALL ^ E_NOTICE);

		$curDir = dirname(__FILE__);
		$curDir = str_replace(array("\\", "//"), "/", $curDir);
		$arrTmp = explode(self::MODULES_FOLDER, $curDir);
		$this->_docRootDir = $arrTmp[0];
		$_SERVER["DOCUMENT_ROOT"] = $this->_docRootDir;
		$this->_bxRootDir = $this->_docRootDir.self::BX_ROOT;
		$this->_modulesDir = $this->_docRootDir.self::MODULES_FOLDER;

		self::connectDBConnFile();

		if($ParentModule instanceof self) {
			if($ParentModule->isInit() == true) {
				$this->_ParentModule = $ParentModule;
			}
		}
		$this->reInit($moduleName);
	}

	public function connectDBConnFile() {
		// пришлось сделать так, поскольку в ядре битрикс данный файл подключается через require_once
		// потому для дальнейшего подключения в билдере ядра битрикс простой require не подходит
		$dbConnCode = file_get_contents($this->_bxRootDir.'/php_interface/dbconn.php');
		$dbConnCode = preg_replace('~^[\s\S]*?\<\?(?:php)?~im', '', $dbConnCode);
		$dbConnCode = preg_replace('~\?\>[\s\S]*?$~im', '', $dbConnCode);
		eval($dbConnCode);
	}

	public function reInit($moduleName = null) {
		if($moduleName == null) {
			if($this->_moduleName == null) {
				echo 'Error: can\'t reInitialize no-name module';
				return false;
			}
			$moduleName = $this->_moduleName;
		}
		if( is_dir($this->_modulesDir.'/'.$moduleName) ) {
			$this->_moduleClass = str_replace('.', '_', $moduleName);
			$this->_arResources = array(
				'RESOURCES' => array(),
				'DEPENDENCIES' => array()
			);
			$this->_moduleName = $moduleName;
			$this->_selfFolder = self::MODULES_FOLDER.'/'.$this->_moduleName;
			$this->_selfDir = $this->_modulesDir.'/'.$this->_moduleName;
			$arModuleInfo = require $this->_docRootDir.$this->_selfFolder.'/install/version.php';
			$this->_version = $arModuleInfo['VERSION'];
			$this->_versionDate = $arModuleInfo['VERSION_DATE'];
			$this->_buildFolder = self::BUILD_FOLDER.'/'.$this->_moduleName;
			$this->_buildDir = $this->_docRootDir.self::BUILD_FOLDER.'/'.$this->_moduleName;
		}

		$this->parseConfig();
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
			require($this->_modulesDir.'/main/include/prolog_before.php');
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

	/**
	 * @param $moduleVersion
	 * @return null|OBX_Build
	 */
	protected function & addDependency($moduleVersion) {
		$arModuleVersion = self::readVersion($moduleVersion);
		if(empty($arModuleVersion)) {
			echo 'Ошибка: Невозможно добавить зависимый модуль "'.$moduleVersion.'". Неверно указана версия или идентификатор модуля. Формат: "имя.модуля-1.номер.версии"'."\n";
			return null;
		}
		if( !is_dir($this->_modulesDir.'/'.$arModuleVersion['MODULE_ID']) ) {
			echo 'Ошибка: Модуль "'.$arModuleVersion['MODULE_ID'].'" не найден';
			return null;
		}
		if($this->_ParentModule instanceof self) {
			$Dependency = $this->_ParentModule->addDependency($moduleVersion);
//			if( self::compareVersions($arModuleVersion['VERSION'], $Dependency->_version) !== 0) {
//				echo 'Версии зависиых модулей.'
//					."\n\t"
//						.$this->_ParentModule->_moduleName.'-'.$this->_ParentModule->_version
//						.' <-требует- '.$Dependency->_moduleName.'-'.$Dependency->_version
//					."\n\t"
//						.$this->_moduleName.'-'.$this->_version
//						.' <-требует- '.$arModuleVersion['MODULE_ID'].'-'.$arModuleVersion['VERSION']
//					."\n"
//				;
//			}
		}
		else {
			$bNewDependency = true;
			if( array_key_exists($arModuleVersion['MODULE_ID'], $this->_arDepModules) ) {
				/** @var OBX_Build $DependencyExists */
				$DependencyExists = &$this->_arDepModules[$arModuleVersion['MODULE_ID']];
				if(
					$DependencyExists->_dependencyVersion != null
					&& self::compareVersions($DependencyExists->_dependencyVersion, $arModuleVersion['VERSION'])>=0
				) {
					$Dependency = &$DependencyExists;
					$bNewDependency = false;
				}
			}
			if($bNewDependency) {
				$Dependency = new self($arModuleVersion['MODULE_ID'], $this);
				$Dependency->_dependencyVersion = $arModuleVersion['VERSION'];
			}
		}

		$this->_arDepModules[$arModuleVersion['MODULE_ID']] = &$Dependency;
		return $Dependency;
	}

	/**
	 * @param null $moduleName
	 * @return array|null|self
	 */
	public function getDependency($moduleName = null) {
		if($moduleName == null) {
			return $this->_arDepModules;
		}
		if( array_key_exists($moduleName, $this->_arDepModules) ) {
			return $this->_arDepModules[$moduleName];
		}
		return null;
	}

	protected function replacePathMacros($path) {
		return str_replace(
			array(
				'%BX_ROOT%',
				'%MODULES_FOLDER%',
				'%SELF_FOLDER%',
				'%INSTALL_FOLDER%',
				'%BUILD_FOLDER%',
			),
			array(
				self::BX_ROOT,
				self::MODULES_FOLDER,
				$this->_selfFolder,
				$this->_selfFolder.'/install',
				$this->_buildFolder
			),
			$path
		);
	}

	public function addConfigFile($configFile) {
		$configFile = $this->_docRootDir.$this->replacePathMacros($configFile);
		if(is_file($configFile)) {
			$this->_arConfigFiles[] = $configFile;
			return true;
		}
		return false;
	}

	public function parseConfig() {
		$this->_arResources = array();
		$this->_arDepModules = array();
		foreach($this->_arConfigFiles as $configPath) {
			$configPath = $this->_docRootDir.$this->replacePathMacros($configPath);
			$this->_parseConfigFile($configPath);
		}
		$this->findResourcesFiles();
	}
	protected function _parseConfigFile($filePath) {
		if( !$this->isInit() ) {
			echo $this->_moduleName.": Error: Build system not initialized!\n";
			return false;
		}

		if( !is_file($filePath) ) {
			echo $this->_moduleName.": Error: Module resource-file not found: \"".$filePath."\"\n";
			return false;
		}
		$strResources = file_get_contents($filePath);
		$arTmpResources = explode("\n", $strResources);
		//rint_r($arTmpResources);
		$configSection = null;
		$lineNumber = 0;

		$bOpenedBlock = false;
		$blockSection = null;

		$bMultiLineStringOpened = false;
		$multiLineString = null;
		$multiLineString_StrResourceBackup = null;

		$arDependencies = array();

		foreach($arTmpResources as $strResourceLine) {
			$lineNumber++;
			if( strlen(trim($strResourceLine))<1 && !$bMultiLineStringOpened ) {
				continue;
			}

			if( ($commentStrPos = strpos(trim($strResourceLine), '#')) !== false ) {
				if($commentStrPos == 0) {
					continue;
				}
				else {
					$strResourceLine = substr($strResourceLine, 0, $commentStrPos);
				}
			}

			if($bMultiLineStringOpened ) {
				if( ($multiLineStringStopPos = strpos($strResourceLine, '>>>')) === false ) {
					$multiLineString .= $strResourceLine."\n";
					continue;
				}
				else {
					if(trim($strResourceLine) != '>>>') {
						echo 'Config parse error in line '.$lineNumber.': symbol ">>>" must be alone at the line '."\n";
						die();
					}
					$strResourceLine = $multiLineString_StrResourceBackup.$multiLineString;
					$multiLineString_StrResourceBackup = null;
					$multiLineString = null;
					$bMultiLineStringOpened = false;
				}
			}
			if( !$bMultiLineStringOpened && ($multiLineStringStartPos = strpos($strResourceLine, '<<<')) !== false ) {
				$bMultiLineStringOpened = true;
				$multiLineString_StrResourceBackup = trim(substr($strResourceLine, 0, $multiLineStringStartPos));
				$multiLineString = substr($strResourceLine, $multiLineStringStartPos+3);
				continue;
			}

			$strResourceLine = trim($strResourceLine);

			if(strpos($strResourceLine, '{') !== false) {
				if(trim($strResourceLine) != '{') {
					echo 'Config parse error in line '.$lineNumber.': symbol "{" must be alone at the line '."\n";
					die();
				}
				if($bOpenedBlock == true) {
					echo 'Config parse error in line '.$lineNumber.': trying to open block when it\'s already opened'."\n";
					die();
				}
				$bOpenedBlock = true;
			}

			if(strpos($strResourceLine, '}') !== false) {
				if(trim($strResourceLine) != '}') {
					echo 'Config parse error in line '.$lineNumber.': symbol "}" must be alone at the line '."\n";
					die();
				}
				if($bOpenedBlock == false) {
					echo 'Config parse error in line '.$lineNumber.': trying to close block "}" when it\'s not opened'."\n";
					die();
				}
				$blockSection = null;
				$bOpenedBlock = false;
				continue;
			}

			if( substr($strResourceLine, 0, 1) == "[" ) {
				if(preg_match('~\[\s*([0-9A-Za-z\_\-\.]*)\s*\]~', $strResourceLine, $arSectionMatches)) {
					if($bOpenedBlock) {
						$blockSection = $arSectionMatches[1];
					}
					else {
						$configSection = $arSectionMatches[1];
					}
				}
				continue;
			}

			if($configSection == 'RESOURCES') {
				$arTmpResource = explode('::', $strResourceLine);
				if( count($arTmpResource)<3 ) {
					//echo "Parse resource \"".$this->_selfDir."/install/resources.php\" error in line $lineNumber\n";
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

				$arResource["INSTALL_FOLDER"] = rtrim($this->replacePathMacros($arResource["INSTALL_FOLDER"]), '/');
				$arResource["TARGET_FOLDER"] = rtrim($this->replacePathMacros($arResource["TARGET_FOLDER"]), '/');

				$this->_arResources[] = $arResource;
			}
			elseif($configSection == 'COMPONENT_PARAMETERS') {
				$this->addCompParamsConfig($strResourceLine);
			}
			elseif($configSection == 'DEPENDENCIES') {
				$arDependencies[] = $strResourceLine;
			}
			elseif($configSection == 'IBLOCK_DATA') {
				$arTmpResource = explode('::', $strResourceLine);
				if( count($arTmpResource)<3 ) {
					//echo "Parse resource \"".$this->_selfDir."/install/resources.php\" error in line $lineNumber\n";
					continue;
				}
				$arIBlockResource = array(
					'IBLOCK_CODE' => null,
					'IBLOCK_ID' => null,
					'IBLOCK_TYPE' => null,
					'EXPORT_PATH' => null
				);
				$arTmpIBlockResource = explode('::', $strResourceLine);
				$arIBlockResource['IBLOCK_CODE'] = trim($arTmpIBlockResource[0]);
				$arIBlockResource['EXPORT_PATH'] = trim($arTmpIBlockResource[1]);
				$arIBlockResource['XML_FILE'] = trim($arTmpIBlockResource[2]);
				$arIBlockResource['FORM_SETTINGS_FILE'] = trim($arTmpIBlockResource[3]);
				$arIBlockResource["EXPORT_PATH"] = rtrim($this->replacePathMacros($arIBlockResource["EXPORT_PATH"]), '/');
				$this->addIBlockData($arIBlockResource);
			}
			elseif($configSection == 'RAW_LANG_CHECK') {
				if( strlen($blockSection)>0 ) {
					if( !isset($arCheckPath) ) {
						$arCheckPath = array();
					}
					if( !array_key_exists($blockSection, $arCheckPath) ) {
						$arCheckPath[$blockSection] = array(
							'PATH' => null,
							'EXCLUDE' => array(),
							'EXCLUDE_PATH' => array()
						);
					}
					$arTmpCheckPathOpt = explode(':', $strResourceLine);
					$checkPathOptName = trim($arTmpCheckPathOpt[0]);
					$checkPathOptValue = trim($arTmpCheckPathOpt[1]);
					if($checkPathOptName == 'path') {
						$arCheckPath[$blockSection]['PATH'] = $this->replacePathMacros($checkPathOptValue);
					}
					elseif($checkPathOptName == 'exclude_path') {
						$arCheckPath[$blockSection]['EXCLUDE_PATH'][] = $this->replacePathMacros($checkPathOptValue);
					}
					elseif($checkPathOptName == 'exclude') {
						$arCheckPath[$blockSection]['EXCLUDE'][] = $checkPathOptValue;
					}
				}
			}
			elseif($configSection == 'RELEASE') {
				if( !isset($arReleasesList) ) {
					$arReleasesList = array(
						'RELEASE_FOLDER' => null,
						'RELEASES_LIST' => array()
					);
				}
				if( strlen($blockSection)==0 ) {
					list($releaseOpt, $releaseOptValue) = explode(':', $strResourceLine);
					$releaseOpt = trim($releaseOpt);
					$releaseOptValue = trim($releaseOptValue);
					if($releaseOpt == 'release_folder') {
						$arReleasesList['RELEASE_FOLDER'] = $releaseOptValue;
					}
				}
				else {

					$arVersion = self::readVersion($blockSection);
					if( empty($arVersion) ) {
						continue;
					}
					if( !array_key_exists($arVersion['VERSION'], $arReleasesList['RELEASES_LIST']) ) {
						$arReleasesList['RELEASES_LIST'][$blockSection] = array(
							'STATE' => 'dev',
							'UPDATE_FROM' => false,
							'DESCRIPTION' => array(),
						);
					}
					list($releaseOpt, $releaseOptValue) = explode(':', $strResourceLine);
					$releaseOpt = trim($releaseOpt);
					$releaseOptValue = trim($releaseOptValue);
					if($releaseOpt == 'update_from') {
						$arUpdateFromVersion = self::readVersion($releaseOptValue);
						if(!empty($arUpdateFromVersion)) {
							$arReleasesList['RELEASES_LIST'][$blockSection]['UPDATE_FROM'] = $arUpdateFromVersion['VERSION'];
						}
					}
					elseif($releaseOpt == 'description' ) {
						$arReleasesList['RELEASES_LIST'][$blockSection]['DESCRIPTION'] = $releaseOptValue;
					}
					elseif($releaseOpt == 'state') {
						if(
							$releaseOptValue == 'dev'
							|| $releaseOptValue == 'devel'
							|| $releaseOptValue == 'develop'
							|| $releaseOptValue == 'development'
						) {
							$arReleasesList['RELEASES_LIST'][$blockSection]['STATE'] = 'dev';
						}
						if(
							$releaseOptValue == 'done'
							|| $releaseOptValue == 'ready'
							|| $releaseOptValue == 'pub'
							|| $releaseOptValue == 'published'
							|| $releaseOptValue == 'public'
							|| $releaseOptValue == 'stable'
						) {
							$arReleasesList['RELEASES_LIST'][$blockSection]['STATE'] = 'done';
						}
					}
				}
			}
		}
		if(isset($arCheckPath)) {
			$this->addPathToRawLangCheck($arCheckPath);
		}
		if(!empty($arReleasesList)) {
			$this->addReleasesList($arReleasesList);
		}
		if(!empty($arDependencies)) {
			foreach($arDependencies as $subModule) {
				$this->addDependency($subModule);
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
					if( substr($fsEntry, strlen($fsEntry) - 4, strlen($fsEntry)) == '.git' ) {
						continue;
					}
					if( !in_array($fsEntry, $arResource['FILES']) ) {
						$arResource['FILES'][] = $fsEntry;
					}
					$arResource['INSTALL_FILES_EXIST'][] = str_replace($this->_docRootDir, '', $installFileFullPath);

				}
				foreach($arTargetFiles as $targetFileFullPath) {
					$fsEntry = str_replace($this->_docRootDir.$arResource['TARGET_FOLDER'], '', $targetFileFullPath);
					$fsEntry = trim($fsEntry, '/');
					if( substr($fsEntry, strlen($fsEntry) - 4, strlen($fsEntry)) == '.git' ) {
						continue;
					}
					if( !in_array($fsEntry, $arResource['FILES']) ) {
						$arResource['FILES'][] = $fsEntry;
					}
					$arResource['TARGET_FILES_EXIST'][] = str_replace($this->_docRootDir, '', $targetFileFullPath);
				}

			}
		}
	}

	protected function addCompParamsConfig($path) {
		$configRelPath = rtrim($this->replacePathMacros($path), '/');
		if( is_file($this->_docRootDir.$configRelPath) ) {
			$this->_arCompParamsConfig[] = $path;
		}
	}

	public function installResources() {
		if( count($this->_arDepModules) ) {
			foreach($this->_arDepModules as $DependencyModule) {
				self::CopyDirFilesEx(
					 $this->_selfDir.'/install/modules/'.$DependencyModule->getModuleName()
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
		closedir($handle);
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
				// [pronix: 2013-07-26]
				// Теперь файлы подмодулей добавляются в решение только на стадии сборки релиза
				// +++
				//// self::deleteDirFilesEx($this->_selfDir.'/install/modules/'.$DependencyModule->getModuleName(), true);
				//// self::CopyDirFilesEx(
				//// 	 $this->_modulesDir.'/'.$DependencyModule->getModuleName()
				//// 	,$this->_selfDir.'/install/modules/'.$DependencyModule->getModuleName()
				//// 	,true, true, FALSE, 'modules'
				//// );
				//// @unlink($this->_selfDir.'/install/modules/'.$DependencyModule->getModuleName().'/.git');
				// ^^^
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
			$this->_removeGitSubModuleLinks();
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
			file_put_contents($this->_selfDir.'/install/'.$installFile, $installCode);
		}
		else {
			file_put_contents($this->_selfDir.'/install/'.$installFile, "<?php\n?>");
		}
		if( strlen($installDepsCode)>0 ) {
			$installDepsCode = 	 $this->getHeaderCodeOfInstallFile()
								.$this->getCodeOfCopyFunction()
								.$installDepsCode
								.$this->getFooterCodeOfInstallFile();
			file_put_contents($this->_selfDir.'/install/'.$installDepsFile, $installDepsCode);
		}
		else {
			file_put_contents($this->_selfDir.'/install/'.$installDepsFile, "<?php\n?>");
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
			file_put_contents($this->_selfDir.'/install/'.$unInstallFile, $unInstallCode);
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
			file_put_contents($this->_selfDir.'/install/'.$backInstallFile, $backInstallCode);
		}
	}

	/**
	 * @param $path_from
	 * @param $path_to
	 * @param bool $ReWrite
	 * @param bool $Recursive
	 * @param bool $bDeleteAfterCopy
	 * @param string | array $Exclude
	 * @return bool
	 */
	static function CopyDirFilesEx($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $Exclude = "") {
		$path_from = str_replace(array("\\", "//"), "/", $path_from);
		$path_to = str_replace(array("\\", "//"), "/", $path_to);
		if(is_file($path_from) && !is_file($path_to)) {
			if( self::CheckDirPath($path_to) ) {
				$file_name = substr($path_from, strrpos($path_from, "/")+1);
				$path_to = rtrim($path_to, '/');
				$path_to .= '/'.$file_name;
				//cho __METHOD__.": ".$path_from." => ".$path_to."\n";
				return self::CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $Exclude);
			}
		}
		if( is_dir($path_from) && substr($path_to, strlen($path_to)-1) == '/' ) {
			$folderName = substr($path_from, strrpos($path_from, '/')+1);
			$path_to .= $folderName;
		}
		return self::CopyDirFiles($path_from, $path_to, $ReWrite, $Recursive, $bDeleteAfterCopy, $Exclude);
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

	/**
	 * В отличие от битриксовской может принимать в качестве
	 * исключения (аргумент $Exclude) не только строку, но и массив
	 * @param $path_from
	 * @param $path_to
	 * @param bool $ReWrite
	 * @param bool $Recursive
	 * @param bool $bDeleteAfterCopy
	 * @param string | array $Exclude
	 * @return bool
	 */
	static public function CopyDirFiles($path_from, $path_to, $ReWrite = True, $Recursive = False, $bDeleteAfterCopy = False, $Exclude = "")
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
				if ($file == "." || $file == "..") continue;

				if( is_string($Exclude) ) {
					if(strlen($Exclude)>0 && substr($file, 0, strlen($Exclude))==$Exclude) continue;
				}
				elseif(is_array($Exclude)) {
					$bContinue = false;
					foreach($Exclude as $excludeItem) {
						if(strlen($excludeItem)>0 && substr($file, 0, strlen($excludeItem))==$excludeItem) $bContinue = true;
					}
					if($bContinue) continue;
				}


				if (is_dir($path_from."/".$file) && $Recursive)
				{
					self::CopyDirFiles($path_from."/".$file, $path_to."/".$file, $ReWrite, $Recursive, $bDeleteAfterCopy, $Exclude);
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
	 * Работает так же как _replaceComponentParameters
	 * с тем отличием, что может принять в аргумент:
	 * 		1. массив с файлами
	 * 		2. файл
	 * 		3. если аргумент не задан, пути до массива буду прочитаны из конфига ресурсов модуля
	 * @param bool|array|string $Config
	 */
	public function replaceComponentParameters($Config = false) {
		if($Config === false) {
			foreach($this->_arCompParamsConfig as $path) {
				$this->_replaceComponentParameters($path);
			}
		}
		if( is_array($Config) ) {
			foreach($Config as $path) {
				$this->_replaceComponentParameters($path);
			}
		}
		if(is_string($Config)) {
			$this->_replaceComponentParameters($Config);
		}
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
	public function _replaceComponentParameters($configPath) {
		$rawConfigPath = $configPath;
		$configPath = rtrim($this->replacePathMacros($configPath), '/');
		$path = dirname($configPath);
		$configPath = $this->_docRootDir.$configPath;
		$path = $this->_docRootDir.$path;

		if( file_exists($configPath) ) {
			$arComponentParamsPlaceholdersList = require($configPath);
			foreach($arComponentParamsPlaceholdersList as $pubFileRelPath => $arComponentReplaces) {
				if(!is_array($arComponentReplaces)) {
					echo 'Wrong replace config for "'.$pubFileRelPath.'". Config path: "'.$rawConfigPath.'"'."\n";
				}
				if( array_key_exists('NAME', $arComponentReplaces) ) {
					$arComponentReplaces = array($arComponentReplaces);
				}
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
		if( strlen($arIBlockData['FORM_SETTINGS_FILE'])<1 ) {
			$arIBlockData['FORM_SETTINGS_FILE'] = $arIBlockData['IBLOCK_CODE'].'.form_settings';
		}
		elseif(strrpos($arIBlockData['FORM_SETTINGS_FILE'], '.form_settings' ) === false) {
			$arIBlockData['FORM_SETTINGS_FILE'] = $arIBlockData['FORM_SETTINGS_FILE'].'.form_settings';
		}
		$arIBlockData['EXPORT_FULL_PATH'] = $this->_docRootDir.$arIBlockData['EXPORT_PATH'];
		$arIBlockData['EXPORT_WORK_DIR'] = '/'.str_replace('.xml', '', $arIBlockData['XML_FILE']).'_files/';
		$arIBlockData['EXPORT_WORK_DIR_FULL_PATH'] = $arIBlockData['EXPORT_FULL_PATH'].$arIBlockData['EXPORT_WORK_DIR'];
		$arIBlockData['XML_FILE_FULL_PATH'] = $arIBlockData['EXPORT_FULL_PATH'].'/'.$arIBlockData['XML_FILE'];
		$arIBlockData['FORM_SETTINGS_FILE_FULL_PATH'] = $arIBlockData['EXPORT_FULL_PATH'].'/'.$arIBlockData['FORM_SETTINGS_FILE'];

		$this->_arIBlockData[$arIBlockData['IBLOCK_CODE']] = $arIBlockData;
		return true;
	}

	protected function _checkConfig4IBlockCode($iblockCode) {
		if( !array_key_exists($iblockCode, $this->_arIBlockData) ) {
			echo "Iblock \"$iblockCode\" not found in resource file \n";
			return false;
		}
		return true;
	}

	protected function _checkIBlockCode($iblockCode) {
		if( !$this->_checkConfig4IBlockCode($iblockCode) ) return false;
		if(
			$this->_arIBlockData[$iblockCode]['IBLOCK_TYPE'] != null
			&& $this->_arIBlockData[$iblockCode]['IBLOCK_ID'] != null
		) {
			return true;
		}
		$this->_includeProlog();
		CModule::IncludeModule('iblock');
		$rsIBlock = CIBlock::GetList(false, array('CODE' => $iblockCode));
		if( !($arIBlock = $rsIBlock->GetNext()) ) {
			echo "Iblock \"$iblockCode\" not found \n";
			return false;
		}
		$this->_arIBlockData[$iblockCode]['IBLOCK_ID'] = $arIBlock['ID'];
		$this->_arIBlockData[$iblockCode]['IBLOCK_TYPE'] = $arIBlock['IBLOCK_TYPE_ID'];
		return true;
	}

	protected function _exportIBlockXML($iblockCode) {
		if(!$this->_checkIBlockCode($iblockCode)) return false;
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

	public function getIBlockFormSettings($iblockCode) {
		if(!$this->_checkIBlockCode($iblockCode)) return null;
		$arIB = &$this->_arIBlockData[$iblockCode];
		$arFormSettings = array(
			'LIST' => CUserOptions::GetOption('list', 'tbl_iblock_list_'.md5($arIB['IBLOCK_TYPE'].'.'.$arIB['IBLOCK_ID']), false, 0),
			'DETAIL' => CUserOptions::GetOption('form', 'form_element_'.$arIB['IBLOCK_ID'], false, 0),
			'PROPERTIES' => array()
		);
		preg_match_all('~PROPERTY\_([\d]{1,10})~', $arFormSettings['DETAIL']['tabs'], $arDetailMatches);

		$arPropertyIDList = $arDetailMatches[1];
		$rsProperties = CIBlockProperty::GetList(array('id' => 'asc'), array('IBLOCK_ID' => $arIB['IBLOCK_ID']));
		while($arProperty = $rsProperties->Fetch()) {
			if( in_array($arProperty['ID'], $arPropertyIDList) ) {
				if( strlen(trim($arProperty['CODE']))<1 ) {
					echo 'CODE for property "'.$arProperty['NAME'].'" not set';
					return null;
				}
				$arFormSettings['PROPERTIES'][] = $arProperty['CODE'];
				$arFormSettings['DETAIL']['tabs'] = str_replace('PROPERTY_'.$arProperty['ID'], 'PROPERTY_%'.$arProperty['CODE'].'%', $arFormSettings['DETAIL']['tabs']);
				$arFormSettings['LIST']['columns'] = str_replace('PROPERTY_'.$arProperty['ID'], 'PROPERTY_%'.$arProperty['CODE'].'%', $arFormSettings['LIST']['columns']);
			}
		}
		return $arFormSettings;
	}


	protected function _exportIBlockFormSettings($iblockCode) {
		$arFormSettings = $this->getIBlockFormSettings($iblockCode);
		if($arFormSettings === null) return false;
		$arIB = &$this->_arIBlockData[$iblockCode];
		$fpFormSettFile = fopen($arIB['FORM_SETTINGS_FILE_FULL_PATH'], 'wb');
		if(!$fpFormSettFile) {
			echo "Can't create / open form-settings file \n";
			return false;
		}
		$serFormSettings = serialize($arFormSettings);
		fwrite($fpFormSettFile, $serFormSettings);
		if($fpFormSettFile) fclose($fpFormSettFile);
	}

	public function exportIBlockFormSettings($iblockCode = null) {
		$bSuccess = true;
		if($iblockCode === null) {
			foreach($this->_arIBlockData as $iblockCode => &$arIB) {
				$bSuccess = $this->_exportIBlockFormSettings($iblockCode) && $bSuccess;
			}
		}
		else {
			return $this->_exportIBlockFormSettings($iblockCode);
		}
		return $bSuccess;
	}


	public function processCommandOptions() {
		$arCommandOptions = getopt('bfh', array(
			'help',
			'build',
			'full',
			'iblock-cml::',
			'iblock-form-settings::',
			'replace-cmp-params::',
			'raw-lang-check',
			'make-release',
			'build-release::',
			'make-update::',
			'build-update::'
		));

		if( empty($arCommandOptions) ) {
			$arCommandOptions['help'] = false;
		}

		if(
			array_key_exists('full', $arCommandOptions)
			|| array_key_exists('f', $arCommandOptions)
		) {
			$arCommandOptions['build'] = false;
			$arCommandOptions['iblock-cml'] = false;
			$arCommandOptions['iblock-form-settings'] = false;
		}

		if(
			array_key_exists('help', $arCommandOptions)
			|| array_key_exists('h', $arCommandOptions)
		) {

			$scriptName = basename($_SERVER['argv'][0]);
			$whiteSpace = str_repeat(' ', strlen($scriptName));
			echo <<<HELP
$scriptName [bfh] [--help] [--build] [--full]
$whiteSpace [--iblock-cml=ibcode1,ibcode2...] [--iblock-form-settings=ibcode1,ibcode2...]
$whiteSpace [--raw-lang-check] [--replace-cmp-params]
$whiteSpace [--build-release] [--build-release]
$whiteSpace [--make-update[=versionFrom+versionTo]] [--build-update]
SHORT OPTIONS
    -h: alias --help
    -b: alias --build
    -f: alias --full
OPTIONS
    --build
         Собирает файлы из установленного битрикса внутрь модуля
    --full:
         alias: --build --iblock-cml --iblock-form-settings
    --replace-cmp-params=[config_path]:
         Заменяет параметры компонентов собранной публички на плейсхолдеры
         Возможно явно указать путь до конфига с параметрами
         Так же выполняется внутри --build
    --raw-lang-check
         Выявляет наличие языкового текста там, где должны быть GetMessage('LANG_CODE')
    --make-release
         Собирка файлов выпуска
    --build-release
         Сборка архива с выпуском для загрузки в МаркетПлейс Битрикс
    --make-update[=versionFrom+versionTo]
         Сборка файлов обновления,
            где versionFrom - версия выпуска, от которого происходит обновление
            и versionTo - версия, до которой происходит обновление.
         Примеры:
             --make-update=+1.0.3
                В данном случае указана только versionTo. За versionFrom будет взята версия последнего релиза
             --make-update=1.0.0+
                В данном случае указана только versionFrom. За версию versionTo будет взята последняя версия,
                находящаяся в разработке, если таковая имеется.
             Если аргумент метода оставить пустым, то и versionTo и versionFrom будут определены автоматически.
    --build-update=[versionTo]
         Сборка архива с обновлением

HELP;
;
			return;
		}

		if(
			array_key_exists('build', $arCommandOptions)
			|| array_key_exists('b', $arCommandOptions)
		) {
			$this->backInstallResources();
			$this->reInit();
			$this->generateInstallCode();
			$this->generateUnInstallCode();
			$this->generateBackInstallCode();
			$this->replaceComponentParameters();
		}

		if( array_key_exists('iblock-cml', $arCommandOptions) ) {
			$arCommandOptions['iblock-cml'] = trim($arCommandOptions['iblock-cml']);
			if( strlen($arCommandOptions['iblock-cml']) > 0 ) {
				$arBuildXML4IBlocks = explode(',', $arCommandOptions['iblock-cml']);
				foreach($arBuildXML4IBlocks as $iblockCode) {
					$this->exportIBlockCML($iblockCode);
				}
			}
			else {
				$this->exportIBlockCML();
			}
		}

		if( array_key_exists('iblock-form-settings', $arCommandOptions) ) {
			$arCommandOptions['iblock-form-settings'] = trim($arCommandOptions['iblock-form-settings']);
			if( strlen($arCommandOptions['iblock-form-settings']) > 0 ) {
				$arBuildIBFormSettings = explode(',', $arCommandOptions['iblock-form-settings']);
				foreach($arBuildIBFormSettings as $iblockCode) {
					$this->exportIBlockFormSettings($iblockCode);
				}
			}
			else {
				$this->exportIBlockFormSettings();
			}
		}

		if( array_key_exists('replace-cmp-params', $arCommandOptions) ) {
			$arCommandOptions['replace-cmp-params'] = trim($arCommandOptions['replace-cmp-params']);
			if( strlen($arCommandOptions['replace-cmp-params']) > 0 ) {
				$this->replaceComponentParameters($arCommandOptions['replace-cmp-params']);
			}
			else {
				$this->replaceComponentParameters();
			}
		}

		if( array_key_exists('raw-lang-check', $arCommandOptions) ) {
			$rawLangCheckResult = $this->getModuleRawLangText();
			if(strlen($rawLangCheckResult)>0) {
				echo 'Найдены файлы в которых языковый текст не перемещен в LANG-файлы:'."\n".$rawLangCheckResult."\n";
			}

		}

		if( array_key_exists('make-release', $arCommandOptions) ) {
			$this->makeRelease();
		}
		if( array_key_exists('make-update', $arCommandOptions) ) {
			$versionFrom = null;
			$versionTo = null;
			$arCommandOptions['make-update'] = trim($arCommandOptions['make-update']);
			if( strlen($arCommandOptions['make-update']) > 0) {
				list($versionFrom, $versionTo) = explode('+', $arCommandOptions['make-update']);
				$versionFrom = trim($versionFrom); $versionTo = trim($versionTo);
			}
			$this->makeUpdate($versionFrom, $versionTo);
		}
		if( array_key_exists('build-release', $arCommandOptions) ) {
			$releaseVersion = null;
			$arCommandOptions['build-release'] = trim($arCommandOptions['build-release']);
			if( strlen($arCommandOptions['build-release'])>0 ) {
				$releaseVersion = $arCommandOptions['build-release'];
			}
			$this->buildRelease($releaseVersion);
		}
		if( array_key_exists('build-update', $arCommandOptions) ) {
			$versionTo = null;
			$arCommandOptions['build-update'] = trim($arCommandOptions['build-update']);
			if( strlen($arCommandOptions['build-update']) > 0 ) {
				$versionTo = $arCommandOptions['build-update'];
			}
			$this->buildUpdate($versionTo);
		}
	}


	/**
	 * @param $needleCharList
	 * @param $haystack
	 * @param int $offset
	 * @return bool|int
	 */
	protected function _strpos($haystack, $needleCharList, $offset = 0) {
		$strLen = strlen($needleCharList);
		for($i=0; $i<$strLen;$i++){
			$pos = strpos($haystack, substr($needleCharList, $i, 1), $offset);
			if( $pos !== false ) {
				return $pos;
			}
		}
		return false;
	}

	/**
	 * @param $relPath
	 * @param array $arExclude
	 * @param array $arPathExclude
	 * @param array|null $arExcludeEntries - не трогать этот аргумент. Нуженя для рекурсии
	 * @return array
	 */
	public function findRawLangText($relPath = '', $arExclude = array(), $arPathExclude = array(), &$arExcludeEntries = null) {
		static $rusLit = 'абвгдеёжзиёклмнопрстуфхцчшщэюяФБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЭЮЯ';
		if($relPath == '.') $relPath = '';
		$relPath = '/'.trim($relPath, '/ ');
		$curPath = rtrim($this->_docRootDir.$relPath, '/ ');
		$arFiles = array();
		if( is_dir($curPath) ) {
			$dir = opendir($curPath);
			if($arExcludeEntries === null) {
				$arExcludeEntries = array();
				foreach($arPathExclude as $excludePattern) {
					$excludePattern = trim($excludePattern);
					if( strlen($excludePattern)<1 ) {
						continue;
					}
					if( substr($excludePattern, 0, 1) != '/' ) {
						$excludePattern = $curPath.'/'.$excludePattern;
					}
					else {
						$excludePattern = $this->_docRootDir.$excludePattern;
					}
					$arFoundPatterns = glob($excludePattern);
					foreach($arFoundPatterns as $excludePath) {
						$arExcludeEntries[] = $excludePath;
					}
				}
			}
			while($fsEntry = readdir($dir)) {
				$fsEntryPath = $curPath.'/'.$fsEntry;
				$fsEntryRelPath = $relPath.'/'.$fsEntry;
				if(
					$fsEntry == '.' || $fsEntry == '..'
					|| $fsEntry == '.directory'
					|| $fsEntry == '.git' || $fsEntry == '.gitignore' || $fsEntry == '.gitmodules' || $fsEntry == '.gitkeep'
					|| in_array($fsEntry, $arExclude)
					|| in_array($fsEntryPath, $arExcludeEntries)
				) {
					continue;
				}
				if(is_dir($fsEntryPath)) {
					if( in_array($fsEntry.'/', $arExclude) ) {
						continue;
					}
					$arFiles = array_merge($arFiles, $this->findRawLangText($fsEntryRelPath, $arExclude, $arPathExclude, $arExcludeEntries));
				}
				else {
					$fsEntryExt = substr($fsEntry, strlen($fsEntry) - 4, strlen($fsEntry));
					if($fsEntryExt != '.php') {
						continue;
					}
					$this->__checkRawLangTextInFile($arFiles, $fsEntryPath, $fsEntryRelPath, $rusLit);
				}
			}
			closedir($dir);
		}
		elseif(is_file($curPath)) {
			if( substr($curPath, strlen($curPath) - 4, strlen($curPath)) == '.php' ) {
				$this->__checkRawLangTextInFile($arFiles, $curPath, $relPath, $rusLit);
			}
		}
		return $arFiles;
	}

	protected function __checkRawLangTextInFile(&$arFiles, &$fsEntryPath, &$fsEntryRelPath, &$rusLit) {
		$bMultiLineComment = false;
		$file = fopen($fsEntryPath, 'r');
		$iLine = 0;
		while( $lineContent = fgets($file) ) {
			$iLine++;
			$posMLCClose = strpos($lineContent, '*/');
			if($posMLCClose!==false) $bMultiLineComment = false;
			if($bMultiLineComment) continue;

			$posMLCOpen = strpos($lineContent, '/*');
			if( $posMLCOpen !== false ) {
				$bMultiLineComment = true;
			}
			$posRusSymbol = $this->_strpos($lineContent, $rusLit);
			$posComment = strpos($lineContent, '//');
			if( $posRusSymbol !== false ) {
				if($posMLCClose !== false && $posRusSymbol < $posMLCClose) {
					continue;
				}
				if(
					($posMLCOpen !== false && $posRusSymbol > $posMLCOpen)
					&&
					($posMLCClose===false || $posRusSymbol < $posMLCClose)
				) {
					$bMultiLineComment = true;
					continue;
				}
				if($posComment !== false && $posRusSymbol > $posComment) {
					continue;
				}
				/////
				$arFiles[] = array(
					'FILE' => '.'.$fsEntryRelPath,
					'LINE' => $iLine,
					'NEAR' => trim($lineContent, ' 	'."\n")
				);
			}
		}
	}

	public function findModuleRawLangText(){
		$arFiles = array();
		foreach($this->_arRawLangCheck as $checkName => $arCheck) {
			$arCheck['EXCLUDE'][] = 'ru/';
			$arCheck['EXCLUDE'][] = 'lang/';
			$arFiles[$checkName] = $this->findRawLangText($arCheck['PATH'], $arCheck['EXCLUDE'], $arCheck['EXCLUDE_PATH']);
		}
		return $arFiles;
	}

	/**
	 * @return string
	 */
	public function getModuleRawLangText() {
		$arChecks = $this->findModuleRawLangText();
		$result = '';
		foreach($arChecks as $checkName => $arFiles) {
			if(!empty($arFiles)) {
				$title = $checkName.': '.$this->_arRawLangCheck[$checkName]['PATH'];
				$result .= "\n";
				$result .= '####################'.str_repeat('#', strlen($title)+4).'####################'."\n";
				$result .= '#################### ['.$title.'] ####################'."\n";
				$result .= '####################'.str_repeat('#', strlen($title)+4).'####################'."\n";
			}
			foreach($arFiles as $arFile) {
				$result .= ''
					.'File: '.$arFile['FILE']."\n"
					.'Line: №'.$arFile['LINE']."\n"
					.'Text: '.$arFile['NEAR']."\n"
					.'------------------------------------------------------------'
					.'------------------------------------------------------------'
				."\n";
			}
		}
		return $result;
	}

	protected function addPathToRawLangCheck($arCheckPath) {
		foreach($arCheckPath as $checkName => &$arPath) {
			if($arPath['PATH'] != null) {
				$this->_arRawLangCheck[$checkName] = $arPath;
			}
		}
	}

	protected function _removeGitSubModuleLinks($path = null) {
		if($path === null) {
			$path = $this->_selfDir.'/install';
		}
		if(is_dir($path) ) {
			$dir = opendir($path);
			while( $fsEntry = readdir($dir) ) {
				if($fsEntry == '.' || $fsEntry == '..') continue;
				if($fsEntry == '.git') {
					@unlink($path.'/'.$fsEntry);
					continue;
				}
				if( is_dir($path.'/'.$fsEntry) ) {
					$this->_removeGitSubModuleLinks($path.'/'.$fsEntry);
				}
			}
			closedir($dir);
		}
	}

	/**
	 * @param $moduleVersion
	 * @return array
	 */
	static public function readVersion($moduleVersion) {
		$regVersion = '~^'
						.'(?:'
							.'('
								.'(?:[a-zA-Z0-9]{1,}\.)?'
								.'(?:[a-zA-Z0-9]{1,})'
							.')'
							.'\-'
						.')?'
						.'([\d]{1,2})\.([\d]{1,2})\.([\d]{1,2})(?:\-r([\d]{1,4}))?$~';
		$arVersion = array();
		if( preg_match($regVersion, $moduleVersion, $arMatches) ) {
			$arVersion['MODULE_ID'] = $arMatches[1];
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


	/**
	 * @param string $folderA
	 * @param string $folderB
	 * @param string $subFolder - не трогать, нужен для рекурсии
	 * @return array
	 */
	public function compareFolderContents($folderA, $folderB, $subFolder = '.') {
		$pathA = $this->_docRootDir.$folderA;
		$pathB = $this->_docRootDir.$folderB;
		$arChanges = array(
			'DELETED' => array(),
			'NEW' => array(),
			'MODIFIED' => array()
		);
		if( is_dir($pathA) && is_dir($pathB) ) {
			$dirA = opendir($pathA);
			while( $fsEntryA = readdir($dirA) ) {
				if( $fsEntryA == '.' || $fsEntryA == '..' || $fsEntryA == '.git' || $fsEntryA == '.directory') {
					continue;
				}
				if( is_dir($pathA.'/'.$fsEntryA) && is_dir($pathB.'/'.$fsEntryA) ) {
					$arChangesRec = $this->compareFolderContents($folderA.'/'.$fsEntryA, $folderB.'/'.$fsEntryA, $subFolder.'/'.$fsEntryA);
					$arChanges['DELETED'] = array_merge($arChanges['DELETED'], $arChangesRec['DELETED']);
					$arChanges['NEW'] = array_merge($arChanges['NEW'], $arChangesRec['NEW']);
					$arChanges['MODIFIED'] = array_merge($arChanges['MODIFIED'], $arChangesRec['MODIFIED']);
				}
				elseif( file_exists($pathA.'/'.$fsEntryA) && !file_exists($pathB.'/'.$fsEntryA) ) {
					$arChanges['DELETED'][] = $subFolder.'/'.$fsEntryA;
				}
				elseif(
					is_dir($pathA.'/'.$fsEntryA) && is_file($pathB.'/'.$fsEntryA)
					||
					is_file($pathA.'/'.$fsEntryA) && is_dir($pathB.'/'.$fsEntryA)
				) {
					$arChanges['DELETED'][] = $subFolder.'/'.$fsEntryA;
					$arChanges['NEW'][] = $subFolder.'/'.$fsEntryA;
				}
				elseif( is_file($pathA.'/'.$fsEntryA) && is_file($pathB.'/'.$fsEntryA) ) {
					$md5SumA = md5(file_get_contents($pathA.'/'.$fsEntryA));
					$md5SumB = md5(file_get_contents($pathB.'/'.$fsEntryA));
					if($md5SumA != $md5SumB) {
						$arChanges['MODIFIED'][] = $subFolder.'/'.$fsEntryA;
					}
				}
			}
			closedir($dirA);
			$dirB = opendir($pathB);
			while($fsEntryB = readdir($dirB)) {
				if( $fsEntryB == '.' || $fsEntryB == '..' || $fsEntryB == '.git'  || $fsEntryB == '.directory') {
					continue;
				}
				elseif( file_exists($pathB.'/'.$fsEntryB) && !file_exists($pathA.'/'.$fsEntryB) ) {
					$arChanges['NEW'][] = $subFolder.'/'.$fsEntryB;
				}
			}
			closedir($dirB);
		}
		elseif( file_exists($pathA) && !file_exists($pathB) ) {
			$arChanges['DELETED'][] = $subFolder;
		}
		elseif( !file_exists($pathA) && file_exists($pathB) ) {
			$arChanges['NEW'][] = $subFolder;
		}
		elseif(
			is_dir($pathA) && is_file($pathB)
			||
			is_file($pathA) && is_dir($pathB)
		) {
			$arChanges['DELETED'][] = $subFolder;
			$arChanges['NEW'][] = $subFolder;
		}
		elseif( is_file($pathA) && is_file($pathB) ) {
			$md5SumA = md5(file_get_contents($pathA));
			$md5SumB = md5(file_get_contents($pathB));
			if($md5SumA != $md5SumB) {
				$arChanges['MODIFIED'][] = $subFolder;
			}
		}

		return $arChanges;
	}

	protected function addReleasesList($arReleasesList) {
		$this->_releaseFolder = $this->_buildFolder;
		if( array_key_exists('RELEASE_FOLDER', $arReleasesList) && $arReleasesList['RELEASE_FOLDER'] != false ) {
			$this->_releaseFolder = $this->replacePathMacros($arReleasesList['RELEASE_FOLDER']);
		}
		$this->_releaseFolder = trim(rtrim($this->_releaseFolder, '/'));
		$this->_releaseDir = $this->_docRootDir.$this->_releaseFolder;
		uksort($arReleasesList['RELEASES_LIST'], 'OBX_Build::compareVersions');
		foreach($arReleasesList['RELEASES_LIST'] as $version => $arRelease) {
			if( !is_dir($this->_releaseDir.'/release-'.$version) ) {
				echo 'ОШИБКА: Выпуск '.$this->_moduleName.'-'.$version.' не найден в папке сборки релизов'
					.' ('.$this->_releaseFolder.'). Выпуск пропущен.'."\n";
				continue;
			}
			if(
				array_key_exists('UPDATE_FROM', $arRelease) && $arRelease['UPDATE_FROM'] != false
				&& !array_key_exists($arRelease['UPDATE_FROM'], $arReleasesList['RELEASES_LIST'])
			) {
				echo 'ОШИБКА: Выпуск '.$this->_moduleName.'-'.$version.' должен быть обновлен с версии '.$arRelease['UPDATE_FROM']
					.'. Данная версия не найдена в списке выпусков. Выпуск пропущен.'."\n";
				continue;
			}
			$this->_arReleases[$version] = $arRelease;
			if( $arRelease['STATE'] == 'done' ) {
				$this->_lastPubReleaseVersion = $version;
			}
		}
	}

	public function makeRelease() {
		echo 'Выпуск '.$this->_moduleName.'-'.$this->_version."\n";
		if( self::compareVersions($this->_version, $this->_lastPubReleaseVersion) <= 0 ) {
			echo 'ОШИБКА: Текущая версия модуля ('.$this->_version.') должна быть больше последней версии опубликованного выпуска ('.$this->_lastPubReleaseVersion.')'."\n";
			return false;
		}
		self::deleteDirFilesEx($this->_releaseFolder.'/release-'.$this->_version);
		self::CopyDirFilesEx(
			$this->_selfDir
			,$this->_releaseDir.'/release-'.$this->_version
			,true, true, FALSE, array('.git', 'modules')
		);
		$this->removeSQLFileComments($this->_releaseFolder.'/release-'.$this->_version.'/install/db/');
		foreach($this->_arDepModules as $Dependency) {
			/** @var OBX_Build $Dependency */
			$arDepVersion = self::readVersion($Dependency->_dependencyVersion);
			if( empty($arDepVersion) ) {
				echo 'Ошибка: '.$this->_moduleName.': версия подмодуля "'.$Dependency->_moduleName.'" не определелна'."\n";
				continue;
			}
			if( !is_dir($Dependency->_releaseDir.'/release-'.$Dependency->_dependencyVersion) ) {
				echo 'Папка содержащая выпуск подмодуля '
					.$Dependency->_moduleName
					.' ('.$Dependency->_releaseDir.'/release-'.$Dependency->_version.') не найдена'."\n";
			}
			// Удаляем старую папку релиза
			self::deleteDirFilesEx($this->_releaseFolder.'/release-'.$this->_version.'/install/modules/'.$Dependency->_moduleName);
			// Копируем новые файлы релиза
			self::CopyDirFilesEx(
				$Dependency->_releaseDir.'/release-'.$Dependency->_dependencyVersion
				,$this->_releaseDir.'/release-'.$this->_version.'/install/modules/'
				,true, true, FALSE, array('.git', 'modules')
			);
			// Даем папке с релзом правильное имя
			rename(
				 $this->_releaseDir.'/release-'.$this->_version.'/install/modules/release-'.$Dependency->_dependencyVersion
				,$this->_releaseDir.'/release-'.$this->_version.'/install/modules/'.$Dependency->_moduleName
			);
			// копируем в релиз все обновления подмодулей
			$depReleaseDir = opendir($Dependency->_releaseDir);
			while($depReleaseFSEntry = readdir($depReleaseDir) ) {
				if($depReleaseFSEntry == '.' || $depReleaseFSEntry == '..'
					|| $depReleaseFSEntry == '.git' || $depReleaseFSEntry == '.directory'
				) continue;
				if( strpos($depReleaseFSEntry, 'update-') !== false ) {
					self::CopyDirFilesEx(
						$Dependency->_releaseDir.'/'.$depReleaseFSEntry
						,$this->_releaseDir.'/release-'.$this->_version.'/install/modules/'.$Dependency->_moduleName.'/'
						,true, true, FALSE, array('.git', 'modules')
					);
				}
			}
			closedir($depReleaseDir);
		}
	}

	protected function _checkBuildFolder() {
		$gitIgnoreFile = $this->_releaseDir.'/build/.gitignore';
		if( !self::CheckDirPath($gitIgnoreFile) ) {
			echo 'Ошибка: путь для сборки архивов не является папкой'."\n";
			return false;
		}
		if( !is_file($gitIgnoreFile) ) {
			file_put_contents($gitIgnoreFile, "*\n*.*\n");
		}
		return true;
	}

	/**
	 * @param string $version
	 * @return bool
	 */
	public function buildRelease($version = null) {
		if( $version == 'last' || $version== 'last_version' ) {
			$version = $version = $this->_lastPubReleaseVersion;
		}
		if( $version === null || $version == 'dev' || $version == 'devel' || $version == 'development' ) {
			$version = $this->_version;
		}
		$arVersion = self::readVersion($version);
		if(empty($arVersion)) {
			echo 'Ошибка: неверно задана версия выпуска'."\n";
			return false;
		}
		echo 'Сборка архива с выпуском '.$this->_moduleName.'-'.$version."\n";
		if( !is_dir($this->_releaseDir.'/release-'.$arVersion['VERSION']) ) {
			echo 'Ошибка: не найдена папка с выпуском ('.$this->_releaseDir.'/release-'.$arVersion['VERSION'].')'."\n";
			return false;
		}
		if( !$this->_checkBuildFolder() ) return false;
		if( file_exists($this->_releaseDir.'/build/.last_version') ) {
			self::deleteDirFilesEx($this->_releaseFolder.'/build/.last_version');
			@mkdir($this->_releaseDir.'/build/.last_version');
		}
		$releaseDirHandler = opendir($this->_releaseDir.'/release-'.$arVersion['VERSION']);
		while($releaseFSEntry = readdir($releaseDirHandler)) {
			if($releaseFSEntry == '.' || $releaseFSEntry == '..'
				|| $releaseFSEntry == '.git' || $releaseFSEntry == '.directory'
			) {
				continue;
			}
			self::CopyDirFilesEx(
				$this->_releaseDir.'/release-'.$arVersion['VERSION'].'/'.$releaseFSEntry
				,$this->_releaseDir.'/build/.last_version/'
				,true, true, FALSE, '.git'
			);
		}
		$bIConvSuccess = $this->iconvFiles($this->_releaseFolder.'/build/.last_version/');
		closedir($releaseDirHandler);
		if($bIConvSuccess) {
			$shellCommand = ''
				.'cd '.$this->_releaseDir.'/build;'."\n"
				.(file_exists($this->_releaseDir.'/build/release-'.$version)
					?'rm release-'.$version.'.tar.gz;'."\n"
					:''
				)
				.'tar czvf release-'.$version.'.tar.gz .last_version > /dev/null;'."\n"
				.'ln -sf release-'.$version.'.tar.gz .last_version.tar.gz;'."\n"
			;
			shell_exec($shellCommand);
		}
	}

	const ICONV_ALL_FILES = 1;
	const ICONV_PHP_FILES = 2;
	const ICONV_LANG_FILES = 3;
	public function iconvFiles($relPath, $target = self::ICONV_ALL_FILES, $from = 'UTF-8', $to = 'CP1251') {
		$relPath = str_replace(array('//', '\\', '/./'), '/', rtrim($relPath, '/'));
		$path = $this->_docRootDir.$relPath;
		if( is_file($path) ) {
			$fsEntry = substr($path, strrpos($path, '/')+1);
			if(
				$target == self::ICONV_ALL_FILES
				|| (
					$target == self::ICONV_PHP_FILES
					&& substr($fsEntry, strlen($fsEntry) - 4, strlen($fsEntry)) == '.php'
				)
				|| (
					$target == self::ICONV_LANG_FILES
					&& substr($fsEntry, strlen($fsEntry) - 4, strlen($fsEntry)) == '.php'
					&& strpos($relPath, '/ru/') !== false
				)
			) {
				$content = file_get_contents($path);
				$content = iconv($from, $to, $content);
				if($content === false) {
					echo 'Ошибка: невозможно конвертировать кодировку файла '.$relPath.' ('.$from.' -> '.$to.')'."\n";
					return false;
				}
				file_put_contents($path, $content);
				return true;
			}
		}
		elseif(is_dir($path)) {
			$dir = opendir($path);
			$bSuccess = true;
			while($fsEntry = readdir($dir)) {
				if($fsEntry == '.' || $fsEntry == '..' || $fsEntry == '.git' || $fsEntry == '.directory') continue;
				$bSuccess = self::iconvFiles($relPath.'/'.$fsEntry, $target, $from, $to) && $bSuccess;
			}
			closedir($dir);
			return $bSuccess;
		}
		else {
			echo 'Ошибка: невозможно сконвертировать кодировку файлов. Путь не найден ('.$relPath.')'."\n";
			return false;
		}
	}

	public function removeSQLFileComments($relPath) {
		$relPath = str_replace(array('//', '\\', '/./'), '/', rtrim($relPath, '/'));
		$path = $this->_docRootDir.$relPath;
		if( is_file($path) ) {
			$fsEntry = substr($path, strrpos($path, '/')+1);
			$fsEntryExt = substr($fsEntry, strrpos($fsEntry, '.')+1);
			if($fsEntryExt == 'sql') {
				$this->_removeSQLFileComments($path);
			}
		}
		if( is_dir($path) ) {
			$dir = opendir($path);
			while($fsEntry = readdir($dir)) {
				if($fsEntry == '.' || $fsEntry == '..' || $fsEntry == '.git' || $fsEntry == '.directory') continue;
				self::removeSQLFileComments($relPath.'/'.$fsEntry);
			}
		}
	}
	protected function _removeSQLFileComments($filePath) {
		$sqlContent = file_get_contents($filePath)."\n";
		$sqlContent = preg_replace('~(?:\-\-(?:.*?)\n)~im', '', $sqlContent);
		$sqlContent = preg_replace('~\/\*.*?\*\/~is', '', $sqlContent);
		$sqlContent = str_replace("\n\n", "\n", $sqlContent);
		file_put_contents($filePath, $sqlContent);
	}

	public function makeUpdate($versionFrom = null, $versionTo = null) {
		$arVersionFrom = self::readVersion($versionFrom);
		$arVersionTo = self::readVersion($versionTo);
		if(empty($arVersionTo)) {
			$versionTo = $this->_version;
			$arVersionTo = self::readVersion($versionTo);
		}
		if(empty($arVersionFrom)) {
			if(
				array_key_exists($versionTo, $this->_arReleases)
				&& $this->_arReleases[$versionTo]['UPDATE_FROM'] !== false
			) {
				$versionFrom = $this->_arReleases[$versionTo]['UPDATE_FROM'];
			}
			else {
				echo 'Предупреждение: исходная версия не указана'
					.' и не была найдена в конфигурации целевой версии.'
					."\n".'В качестве исходной версии'
					.' принята версия последнего опубликованного выпуска ['.$this->_lastPubReleaseVersion.'].'."\n";
				$versionFrom = $this->_lastPubReleaseVersion;
			}
			$arVersionFrom = self::readVersion($versionFrom);
		}
		echo 'Обновление '.$this->_moduleName.'-['.$versionFrom.' => '.$versionTo.']'."\n";
		if($arVersionFrom['RAW_VERSION'] == $arVersionTo['RAW_VERSION']) {
			echo 'Ошибка: целевая и исходна версии равны. Задайте явно интервал версий для построения обновления.'
				.' (такая ситуация возможно когда версия последнего выпуска равна версии для разработки'
				.' и интервал версий не указан явно)'."\n";
			return false;
		}
		$prevReleaseFolder = $this->_releaseFolder.'/release-'.$versionFrom;
		$nextReleaseFolder = $this->_releaseFolder.'/release-'.$versionTo;
		if( !is_dir($this->_docRootDir.$prevReleaseFolder) ) {
			echo 'Ошибка: не найдена папка с файлами исходного выпуска ('.$this->_releaseFolder.'/release-'.$versionFrom.')'."\n";
			return false;
		}
		if( !is_dir($this->_docRootDir.$nextReleaseFolder) ) {
			echo 'Ошибка: не найдена папка с файлами целевого выпуска ('.$this->_releaseFolder.'/release-'.$versionTo.')'."\n";
			return false;
		}
		$arChanges = self::compareFolderContents($prevReleaseFolder, $nextReleaseFolder);

		$updateFolder = $this->_releaseFolder.'/update-'.$versionTo;
		$updateDir = $this->_docRootDir.$updateFolder;

		// Очищаем папку с обовлениями
		if(!empty($arChanges['NEW']) || !empty($arChanges['MODIFIED']) || !empty($arChanges['DELETED'])) {
			if( !self::CheckDirPath($updateDir.'/.') ) {
				echo 'Ошибка: "'.$updateFolder.'" не является папкой.'."\n";
				return false;
			}
			$updateDirHandler = opendir($updateDir);
			while($updateFSEntry = readdir($updateDirHandler)) {
				if($updateFSEntry == '.' || $updateFSEntry == '..') {
					continue;
				}
				if(
					preg_match('~^description\.[a-z]{2}$~', $updateFSEntry)
					|| strpos($updateFSEntry, 'updater.') !== false
				) {
					continue;
				}
				self::deleteDirFilesEx($updateFolder.'/'.$updateFSEntry);
			}
		}
		// генерируем описание обновления
		$updateDescription = '';
		foreach($this->_arReleases as $releaseVersion => &$arRelease) {
			if(
				self::compareVersions($releaseVersion, $versionFrom)>=0
				&& self::compareVersions($releaseVersion, $versionTo)<=0
			) {
				$updateDescription .= "\n".'['.$releaseVersion.']'."\n";
			}
			if( array_key_exists('DESCRIPTION', $arRelease) ) {
				$updateDescription .= $arRelease['DESCRIPTION']."\n";
			}
		}
		file_put_contents($updateDir.'/description.ru', $updateDescription);
		if(!empty($arChanges['NEW']) || !empty($arChanges['MODIFIED'])) {
			$updateFilesCode = $this->getHeaderCodeOfInstallFile();
			$updateFilesCode .= $this->getCodeOfCopyFunction();
			foreach($arChanges['NEW'] as $newFSEntry) {
				self::CopyDirFiles(
					str_replace(array('/./', '//'. '\\'), '/', $this->_docRootDir.$nextReleaseFolder.'/'.$newFSEntry),
					str_replace(array('/./', '//'. '\\'), '/', $updateDir.'/'.$newFSEntry),
					true, true,
					false, ''
				);
				$updateFilesCode .= 'CopyDirFiles('
					.'dirname(__FILE__)."'.str_replace(array('/./', '//'. '\\'), '/', '/'.$newFSEntry).'", '
					.'"'.str_replace(array('/./', '//'. '\\'), '/', $this->_selfFolder.'/'.$newFSEntry).'"'
				.');'."\n";
			}
			foreach($arChanges['MODIFIED'] as $modFSEntry) {
				self::CopyDirFiles(
					str_replace(array('/./', '//'. '\\'), '/', $this->_docRootDir.$nextReleaseFolder.'/'.$modFSEntry),
					str_replace(array('/./', '//'. '\\'), '/', $updateDir.'/'.$modFSEntry),
					true, true,
					false, ''
				);
				$updateFilesCode .= 'CopyDirFiles('
					.'dirname(__FILE__)."'.str_replace(array('/./', '//'. '\\'), '/', '/'.$modFSEntry).'", '
					.'"'.str_replace(array('/./', '//'. '\\'), '/', $this->_selfFolder.'/'.$modFSEntry).'"'
				.');'."\n";
			}
			$updateFilesCode .= $this->getFooterCodeOfInstallFile();
		}
		if(!empty($arChanges['DELETED'])) {
			$updateDeleteCode = $this->getHeaderCodeOfInstallFile();
			foreach($arChanges['DELETED'] as $delFSEntry) {
				$updateDeleteCode .= 'DeleteFilesEx("'
					.str_replace(array('/./', '//'. '\\'), '/', $this->_selfFolder.'/'.$delFSEntry)
				.'");'."\n";
			}
			$updateDeleteCode .= "\n".$this->getFooterCodeOfInstallFile();
			file_put_contents($updateDir.'/updater.delete.php', $updateDeleteCode);
		}
		file_put_contents($updateDir.'/updater.php', "<?php\n\n\n?>");
		foreach($this->_arDepModules as $DependencyModule) {
			$debug=1;
		}
	}

	public function buildUpdate($versionTo = null) {
		$arVersionTo = self::readVersion($versionTo);
		if(empty($arVersionTo)) {
			$versionTo = $this->_version;
			$arVersionTo = self::readVersion($versionTo);
		}
		echo 'Создание архива обновления '.$this->_moduleName.'-'.$versionTo."\n";
		if( !is_dir($this->_releaseDir.'/update-'.$versionTo) ) {
			echo 'Ошибка: не найдена папка с обновлением не найдена ('.$this->_releaseFolder.'/update-'.$versionTo.').'."\n";
		}
		if( !$this->_checkBuildFolder() ) return false;


		if( file_exists($this->_releaseDir.'/build/'.$versionTo) ) {
			self::deleteDirFilesEx($this->_releaseFolder.'/build/'.$versionTo);
			@mkdir($this->_releaseDir.'/build/'.$versionTo);
		}
		$updateDirHandler = opendir($this->_releaseDir.'/update-'.$versionTo);
		while($updateFSEntry = readdir($updateDirHandler)) {
			if($updateFSEntry == '.' || $updateFSEntry == '..'
				|| $updateFSEntry == '.git' || $updateFSEntry == '.directory'
			) {
				continue;
			}
			self::CopyDirFilesEx(
				$this->_releaseDir.'/update-'.$versionTo.'/'.$updateFSEntry
				,$this->_releaseDir.'/build/'.$versionTo
				,true, true, FALSE, '.git'
			);
		}
		$bIConvSuccess = $this->iconvFiles($this->_releaseFolder.'/build/'.$versionTo);
		closedir($updateDirHandler);
		if($bIConvSuccess) {
			$shellCommand = ''
				.'cd '.$this->_releaseDir.'/build;'."\n"
				.(file_exists($this->_releaseDir.'/build/'.$versionTo.'.tar.gz')
					?'rm '.$versionTo.'.tar.gz;'."\n"
					:''
				)
				.'tar czvf '.$versionTo.'.tar.gz '.$versionTo.' > /dev/null;'."\n"
			;
			shell_exec($shellCommand);
		}
	}

	/**
	 * [pronix:2013-07-23]
	 * Данная функция помечает все PHP, XML и JS файлы внутри папки как PlainText
	 * Что бы любимый PhpStorm не индексировал лишнего :)
	 * TODO: Реализовать метод OBX_Build::addIdeaProjectFolderAsPlainText
	 * За явную пометку файлов как plainText отвечает файл .idea/misc.xml
	 */
	static public function addIdeaProjectFolderAsPlainText() {

	}
}

/*
 * 1. СБОРКА ОБНОВЛЕНИЙ ФАЙЛОВ МОДУЛЕЙ
 * 1.1 Скопировать релиз
 * 1.2 Снять контрольные суммы
 * 1.3 Создать папку обновлений
 * 1.4 Скопировать в папку обновлений файлы по разнице контролных сумм
 * 1.5 Генерируем файлы с кодом копирования обновленных файлов модуля
 *
 * 2. СБОРКА ОБНОВЛЕНИЙ ПОДМОДУЛЯ
 * 2.1 Собираем подмодуль автономно (см. СБОРКА ОБНОВЛЕНИЙ ФАЙЛОВ МОДУЛЕЙ)
 */
