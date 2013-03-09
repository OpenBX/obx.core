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
class OBX_Build {

	protected $_arResources = array();
	protected $_moduleName = null;
	protected $_moduleClass = null;

	protected $_bInit = false;
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
		$this->_selfDir = dirname(__FILE__);
		$this->_selfDir = str_replace(array("\\", "//"), "/", $this->_selfDir);
		$this->_selfFolder = '';
		$this->_modulesFolder = $this->_bxRootFolder.'/modules';
		$arrTmp = explode($this->_modulesFolder, $this->_selfDir);
		$this->_docRootDir = $arrTmp[0];
		$this->_selfFolder = $this->_modulesFolder.$arrTmp[1];
		$this->_bxRootDir = $this->_docRootDir.$this->_bxRootFolder;
		$this->_modulesDir = $this->_docRootDir.$this->_modulesFolder;

		require_once $this->_bxRootDir.'/php_interface/dbconn.php';

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
			//print_r($arTmpResources);
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

	public function backInstallResources() {
		if( count($this->_arDepModules) ) {
			foreach($this->_arDepModules as $DependencyModule) {
				/** @var OBX_Build $DependencyModule */
				$DependencyModule->backInstallResources();
				$DependencyModule->reInit();
				$DependencyModule->generateInstallCode();
				$DependencyModule->generateUnInstallCode();
				$DependencyModule->generateBackInstallCode();
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
					self::DeleteDirFilesEx($arResource['INSTALL_FOLDER']);
				}
			}
			foreach($this->_arResources as &$arResource) {
				foreach($arResource['FILES'] as $fsEntryName) {
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
					$backInstallCode .= 'DeleteDirFilesEx("'.$arResource['INSTALL_FOLDER']."\");\n";
				}
			}
			foreach($this->_arResources as &$arResource) {
				foreach($arResource['FILES'] as $fsEntryName) {
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

	static function DeleteDirFilesEx($path)
	{
		if(strlen($path) == 0 || $path == '/')
			return false;

		$full_path = $_SERVER["DOCUMENT_ROOT"].$path;

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

					if(!self::DeleteDirFilesEx($path."/".$file))
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
}