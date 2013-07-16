<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace {
	IncludeModuleLangFile(__FILE__);

	/**
	 * Class OBX_Tools
	 * @deprecated use OBX\Core\Tools
	 */
	class OBX_Tools extends OBX\Core\Tools {}
}

namespace OBX\Core {
	class Tools
	{

		static protected $arPropIdToPropCode = array();
		static function getPropIdByCode($IBLOCK_ID, $PROP_CODE, &$arProp = array(), &$ERR_MSG = array()) {
			if( !\CModule::IncludeModule('iblock') ) {
				$ERR_MSG[] = GetMessage('OBX_CORE_TOOLS_IBLOCK_NOT_INSTALLED');
				return false;
			}
			$PROP_CODE = strtoupper($PROP_CODE);

			if( !isset(self::$arPropIdToPropCode[$IBLOCK_ID]) ) {
				self::$arPropIdToPropCode[$IBLOCK_ID] = array();
			}

			if( count(self::$arPropIdToPropCode[$IBLOCK_ID])<1 ) {
				$properties = \CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
				while ($prop_fields = $properties->GetNext()) {
					//echo $prop_fields["ID"]." - ".$prop_fields["NAME"]."<br>";
					self::$arPropIdToPropCode[$IBLOCK_ID][strtoupper($prop_fields["CODE"])] = $prop_fields;
				}
			}
			if ( array_key_exists($PROP_CODE, self::$arPropIdToPropCode[$IBLOCK_ID]) ) {
				$arProp = self::$arPropIdToPropCode[$IBLOCK_ID][$PROP_CODE];
				return self::$arPropIdToPropCode[$IBLOCK_ID][$PROP_CODE]["ID"];
			}
			else {
				$ERR_MSG[] = GetMessage('OBX_CORE_TOOLS_IBLOCK_PROP_NOT_FOUND');
				return false;
			}

		}
		static public function clearPropIdByCodeCache(){
			self::$arPropIdToPropCode = array();
		}

		static protected $arPropCodeToPropId = array();
		static public function getPropCodeById($IBLOCK_ID, $PROP_ID, &$ERR_MSG = array()) {
			if( !\CModule::IncludeModule("iblock") ) {
				$ERR_MSG[] = GetMessage('OBX_CORE_TOOLS_IBLOCK_NOT_INSTALLED');
				return false;
			}

			if( !isset(self::$arPropCodeToPropId[$IBLOCK_ID]) ) {
				self::$arPropCodeToPropId[$IBLOCK_ID] = array();
			}

			if( count(self::$arPropCodeToPropId[$IBLOCK_ID])<1 ) {
				$properties = \CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
				while ($prop_fields = $properties->GetNext())
				{
					//echo $prop_fields["ID"]." - ".$prop_fields["CODE"]."<br>";
					self::$arPropCodeToPropId[$IBLOCK_ID][$prop_fields["ID"]] = $prop_fields["CODE"];
				}
			}
			if ( isset(self::$arPropCodeToPropId[$IBLOCK_ID][$PROP_ID]) ) {
				return self::$arPropCodeToPropId[$IBLOCK_ID][$PROP_ID];
			}
			else {
				$ERR_MSG[] = GetMessage('OBX_CORE_TOOLS_IBLOCK_PROP_NOT_FOUND');
				return false;
			}
		}
		static public function clearPropCodeByIdCache() {
			self::$arPropCodeToPropId = array();
		}

		static function cropString($str, $len, $endOfLine = "") {
			$str = trim($str);
			$len = intval($len);
			if (strlen($str) < 1 || $len < 1) {
				return false;
			}
			if (strlen($str) <= $len) {
				return $str;
			}
			$len++;
			$str = substr($str, 0, strrpos(substr($str, 0, $len), " ")).$endOfLine;
			return $str;
		}

		static function rusDays($amount) {
			$amount = abs(intval($amount));
			switch($amount%100) {
				case 11:
				case 12:
				case 13:
				case 14:
					return GetMessage('DAY_GM');
					break;
				default:
					switch($amount%10) {
						case 1:
							return GetMessage('DAY_N');
							break;
						case 2:
						case 3:
						case 4:
							return GetMessage('DAY_G');
							break;
						default:
							return GetMessage('DAY_GM');
					}
			}
		}
		static function rusHours($amount) {
			$amount = abs(intval($amount));
			switch($amount%100) {
				case 11:
				case 12:
				case 13:
				case 14:
					return GetMessage('HOUR_GM');
					break;
				default:
					switch($amount%10) {
						case 1:
							return GetMessage('HOUR_N');
							break;
						case 2:
						case 3:
						case 4:
							return GetMessage('HOUR_G');
							break;
						default:
							return GetMessage('HOUR_GM');
					}
			}
		}

		/**
		 * Отдает наименование единицы в форме соотвествующей числу
		 * @param integer $quantity число
		 * @param string $nominative именительный подеж ед. число (час)
		 * @param string $genetive родительный подеж ед. число  (часа)
		 * @param string $genplural родительный подеж множ. число (часов)
		 * @return string
		 */
		static public function rusQuantity($quantity, $nominative, $genetive = NULL, $genplural = NULL){
			$oneState = false;
			if($genetive == NULL || $genplural = NULL) {
				$oneState = true;
			}
			$quantity = abs(intval($quantity));
			switch($quantity%100) {
				case 11:
				case 12:
				case 13:
				case 14:
					return ($oneState)?$nominative.GetMessage('SOME_GM'):$genplural;
					break;
				default:
					switch($quantity%10) {
						case 1:
							return $nominative;
							break;
						case 2:
						case 3:
						case 4:
							return ($oneState)?$nominative.GetMessage('SOME_G'):$genetive;
							break;
						default:
							return ($oneState)?$nominative.GetMessage('SOME_GM'):$genplural;
					}
			}
		}

		// get list converted to array indexed by ID
		static function convListToIDIndex(&$arSectionList) {
			$arSectionsIDIndexList = array();
			foreach($arSectionList as &$arSectionList) {
				$arSectionsIDIndexList[$arSectionList['ID']] = $arSectionList;
			}
			return $arSectionsIDIndexList;
		}

		// making parent-child relation table
		static function getRelationTableFromFlatTree(&$arFlatTree, $DEPTH_KEY = 'DEPTH_LEVEL', $CHILDS_KEY = 'CHILDS', $PARENT_KEY = 'PARENT', $bModifySrcArray = false) {
			$iItems = 0;
			$itemsCount = count($arFlatTree);
			$curPointer = &$arTree;
			$curDepth = 1;
			$prevKey = 0;
			$parentKey = 0;
			$arLastKeyInDepth = array();
			$arParents = array();
			$arChilds = array();
			foreach($arFlatTree as $key => &$item) {
				$iItems++;
				if($item[$DEPTH_KEY] > $curDepth) {
					$parentKey = $prevKey;
					$curDepth = $item[$DEPTH_KEY];
				}
				elseif($item[$DEPTH_KEY] < $curDepth) {
					$curDepth = $item[$DEPTH_KEY];
					$parentKey = $arLastKeyInDepth[$curDepth-1];
				}
				$arChilds[$key][$DEPTH_KEY] = $curDepth;
				$arChilds[$key][$PARENT_KEY] = $parentKey;

				if(!$parentKey) $parentKey = 0;
				$arParents[$parentKey][$CHILDS_KEY][] = $key;
				$prevKey = $key;
				$arLastKeyInDepth[$item[$DEPTH_KEY]] = $prevKey;
			}
			//d($arParents, '$arParents');
			//d($arChilds, '$arChilds');

			$arRelations = array();
			$arRelations[0] = $arParents[0];
			foreach($arChilds as $childKey => $arChild) {
				$arRelations[$childKey] = $arChild;
				$arRelations[$childKey][$CHILDS_KEY] = array();
				$arRelations[$childKey][$CHILDS_KEY] = $arParents[$childKey][$CHILDS_KEY];

				if($bModifySrcArray) {
					$arFlatTree[$childKey][$PARENT_KEY] = $arChild[$PARENT_KEY];
					$arFlatTree[$childKey][$CHILDS_KEY] = array();
					$arFlatTree[$childKey][$CHILDS_KEY] = $arParents[$childKey][$CHILDS_KEY];
				}
			}
			//d($arRelations, '$arRelations');

			return $arRelations;
		}

		static function getParentIDByRelationTable(&$SectionID, &$arRelationTable)
		{
			if(!$SectionID)
				return false;
			if(@isset($arRelationTable[$SectionID])) {
				return $arRelationTable[$SectionID]["PARENT_SECTION_ID"];
			}
			return false;
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

				if( $bUniqueKeys ) {
					if($bSetReferences) {
						$arListIndex[$complexKey] = &$arItem;
					}
					else {
						$arListIndex[$complexKey] = $arItem;
					}
				}
				else {
					if( !array_key_exists($complexKey, $arListIndex) ) {
						$arListIndex[$complexKey] = array();
					}
					if($bSetReferences) {
						$arListIndex[$complexKey][] = &$arItem;
					}
					else {
						$arListIndex[$complexKey][] = $arItem;
					}
				}
			}
			return $arListIndex;
		}

		static public function arrayMergeRecursiveDistinct( array &$array1, array &$array2) {
			$arMerged = $array1;
			foreach( $array2 as $key => &$value ) {
				if ( is_array($value) && isset($arMerged[$key]) && is_array($arMerged[$key]) ) {
					$arMerged[$key] = self::arrayMergeRecursiveDistinct($arMerged[$key], $value);
				}
				else {
					$arMerged[$key] = $value;
				}
			}
			return $arMerged;
		}

		//////////// РАБОТА С ФАЙЛАМИ И ПАПКАМИ
		/**
		 *
		 * Ф-ия работает опасно. Если указать не глубокий путь,
		 * то можно потереть чужие файлы.
		 *
		 * Что бы этого изберажть, ниже ТУДУ.
		 *
		 * TODO: Написать небольшую рекурсию,
		 * которая удаляя содержимое папки удаляла только те имена,
		 * которые есть в $frDir. Т.е. вызывать не DeleteDirFilesEx а саму себя
		 * и при выходе из рекурсии проверять не остлись ли файлы или папки.
		 * Если остались, то не удаляем папку.
		 * @param string $frDir
		 * @param string $toDir
		 * @param array $arExclude
		 * @static
		 */
		static function deleteDirContents($frDir, $toDir, $arExclude = array()) {
			if( is_dir($frDir) ) {
				$d = dir($frDir);
				while( $entry = $d->read() ) {
					if( $entry=="." || $entry==".." ) {
						continue;
					}
					if( in_array($entry, $arExclude) ) {
						continue;
					}
					if( is_dir($toDir."/".$entry) ) {
						//echo "delete dir: ".$toDir."/".$entry."<br />\n";
						self::deleteDirFilesEx($toDir."/".$entry, true);
					}
					else {
						//echo "delete file: ".$toDir."/".$entry."<br />\n";
						@unlink($toDir."/".$entry);
					}
				}
				$d->close();
				//die();
			}
		}

		/**
		 * Работает так же как битриксовская, но в отличие от неё, может принимать полный путь.
		 * @param String $path - путь
		 * @param bool $bIsPathFull - абсолюьный=true, относительный=false
		 * @return boolean
		 */
		function deleteDirFilesEx($path, $bIsPathFull = false)
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

		/**
		 * Допустим подае такой $FIELDS:
		 * array(
		 * 		"TRACKING_ID" => "D6Jvd38Mpa",
		 * 		"DELIVERY" => array(
		 * 			"ID" => 543,
		 * 			"CHECKED" => "Y",
		 * 		),
		 * 		"key1" => "value1",
		 * 		"key2" => "value2",
		 * 		"key3" => "value3",
		 * 	)
		 * Тогда файлы шаблона выглядят так:
		 * <?php
		 * return <<<HTML
		 *
		 * Идентификационный номер отправления:
		 * TRACKING_ID: <b>$TRACKING_ID</b><br />
		 * DELIVERY_ID: <b>$DELIVERY_ID</b><br />
		 * DELIVERY_CHECKED: <b>$DELIVERY_CHECKED</b><br />
		 * key1: <b>$key1</b><br />
		 * key2: <b>$key2</b><br />
		 * key3: <b>$key3</b><br />
		 *
		 * HTML;
		 * ?>
		 */
		function getTemplateMessage($templateFile, $FIELDS, &$ERR_MSG = array()) {
			if( !is_file($templateFile) ) {
				$ERR_MSG[] ="Неверно указан файл шаблона (".$templateFile.")";
				return '';
			}
			//d($FIELDS, '$FIELDS');
			foreach($FIELDS as $varName => $varValue) {
				if( !is_array($varValue) ) {
					$arNewVar = array($varName => $varValue);
					extract($arNewVar, EXTR_PREFIX_SAME, "TPL_");
				}
				else {
					foreach($varValue as $subVarName => $subVarValue) {
						if( !is_array($subVarValue) ) {
							$arNewSubVar = array($varName."_".$subVarName => $subVarValue);
							extract($arNewSubVar, EXTR_PREFIX_SAME, "TPL_");
						}
					}
				}
			}
			return include $templateFile;
		}


		static private $_bViewContentDispatcherActive = false;
		static private $_arContentViewTargets = array();
		static public function showViewContent($view) {
			if(preg_match('~^[a-zA-Z\_\-]{1,30}$~', $view)) {
				if( is_dir($_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/view_target') ) {
					$contentFile = $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/view_target/'.$view.'.php';
				}
				else {
					$contentFile = $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/view_target.'.$view.'.php';
				}

				if( file_exists($contentFile) ) {
					global $APPLICATION;
					$APPLICATION->ShowViewContent($view);
					self::$_arContentViewTargets[$view] = array(
						'CONTENT_FILE' => $contentFile
					);
					if(!self::$_bViewContentDispatcherActive) {
						AddEventHandler('main', 'OnEpilog', 'OBX\Core\Tools::dispatchViewTargetContents');
						self::$_bViewContentDispatcherActive = true;
					}
				}
			}

		}

		static public function dispatchViewTargetContents() {
			global $APPLICATION, $USER, $DB;
			foreach(self::$_arContentViewTargets as $view => &$arViewTarget) {
				ob_start();
				include $arViewTarget['CONTENT_FILE'];
				$content = ob_get_clean();
				$APPLICATION->AddViewContent($view, $content);
			}
		}

		/////////////////////////////
		/// CONNECTING LESS FILES ///
		static private $_arLessFiles = array();
		static private $_arLessFilesSort = array();
		static private $_lessFilesCounter = 0;
		static private $_bLessProduction = false;
		static private $_lessCompiledExt = '.css';
		static private $_lessJSPath = null;
		static private $_bLessFilesConnected = false;
		static private $_bLessJSHeadConnected = false;
		static private $_bConnectLessJSFileAfterLessFiles = false;

		static public function __sortLessFiles($fileIndexA, $fileIndexB) {
			$sortA = intval(self::$_arLessFilesSort[$fileIndexA] * 100 + $fileIndexA);
			$sortB = intval(self::$_arLessFilesSort[$fileIndexB] * 100 + $fileIndexB);
			if($sortA == $sortB) return 0;
			return ($sortA < $sortB)? -1 : 1;
		}
		static public function getLessHead() {
			$returnString = '';
			uksort(self::$_arLessFiles, '\OBX\Core\Tools::__sortLessFiles');
			foreach(self::$_arLessFiles as $lessFilePath) {
				$compiledLessFilePath = substr($lessFilePath, 0, -5).self::$_lessCompiledExt;
				if(!self::$_bLessProduction) {
					$returnString .= '<link rel="stylesheet/less" type="text/css" href="'.$lessFilePath.'">'."\n";
				}
				else {
					$returnString .= '<link rel="stylesheet" type="text/css" href="'.$compiledLessFilePath.'">'."\n";
				}
			}
			return $returnString;
		}
		static public function getLessJSHead() {
			$returnString = '';
			if( self::$_lessJSPath ) {
				$returnString .= '<script type="text/javascript"> less = { env: \'development\' }; </script>'."\n";
				$returnString .= '<script type="text/javascript" src="'.self::$_lessJSPath.'"></script>'."\n";
				//$returnString .= '<script type="text/javascript">less.watch();</script>'."\n";
			}
			return $returnString;
		}
		static public function showLessHead() {
			global $APPLICATION;
			$APPLICATION->AddBufferContent('OBX\Core\Tools::getLessHead');
			self::$_bLessFilesConnected = true;
			if( self::$_bConnectLessJSFileAfterLessFiles ) {
				$APPLICATION->AddBufferContent('OBX\Core\Tools::getLessJSHead');
				self::$_bConnectLessJSFileAfterLessFiles = false;
				self::$_bLessJSHeadConnected = true;
			}
		}
		static public function showLessJSHead($bWaitWhileLessFilesConnected = true) {
			if( $bWaitWhileLessFilesConnected && !self::$_bLessFilesConnected ) {
				self::$_bConnectLessJSFileAfterLessFiles = true;
				return;
			}
			global $APPLICATION;
			$APPLICATION->AddBufferContent('OBX\Core\Tools::getLessJSHead');
			self::$_bLessJSHeadConnected = true;
		}
		static public function setLessJSPath($lessJSPath, $bShowLessHead = true) {
			if( strpos($lessJSPath, 'less')===false || substr($lessJSPath, -3)!=".js" ) {
				return false;
			}
			if( is_file($_SERVER["DOCUMENT_ROOT"].$lessJSPath) ) {
				self::$_lessJSPath = $lessJSPath;
			}
			elseif( is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$lessJSPath) ) {
				$lessJSPath = str_replace(
					array('//', '///'), array('/', '/'),
					SITE_TEMPLATE_PATH."/".$lessJSPath
				);
				self::$_lessJSPath = $lessJSPath;
			}
			if( $bShowLessHead ) {
				if( !self::$_bLessFilesConnected ) {
					self::$_bConnectLessJSFileAfterLessFiles = false;
					self::showLessHead();
				}
				if( !self::$_bLessJSHeadConnected ) {
					self::showLessJSHead();
				}
			}
			return true;
		}
		static public function getLessJSPath() {
			return self::$_lessJSPath;
		}
		static public function setLessCompiledExt($ext) {
			if( preg_match('~^\.[a-zA-Z0-9\_\-]*\.css$~', $ext)) {
				self::$_lessCompiledExt = $ext;
			}
		}
		/**
		 * @param $lessFilePath
		 * @param int $sort
		 * @return bool
		 */
		static public function addLess($lessFilePath, $sort = 500) {
			if( !in_array($lessFilePath, self::$_arLessFiles) ) {
				if( substr($lessFilePath, -5) == ".less" ) {
					$compiledLessFilePath = substr($lessFilePath, 0, -5).self::$_lessCompiledExt;
					$sort = intval($sort);
					if( is_file($_SERVER["DOCUMENT_ROOT"].$lessFilePath)
						|| (
							is_file($_SERVER["DOCUMENT_ROOT"].$compiledLessFilePath)
							&& self::$_bLessProduction)
					) {
						self::$_arLessFiles[self::$_lessFilesCounter] = $lessFilePath;
						self::$_arLessFilesSort[self::$_lessFilesCounter] = $sort;
						self::$_lessFilesCounter++;
						return true;
					}
					elseif(
						is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$lessFilePath)
						|| (
							is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$compiledLessFilePath)
							&& self::$_bLessProduction
						)
						//					// На случай если мы будем комипировать less в папку css
						//					|| (
						//						substr($compiledLessFilePath, 0, 5) == 'less/'
						//						&& self::$_bLessProduction
						//						&& is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/css/".substr($compiledLessFilePath, 5))
						//					)
					) {
						self::$_arLessFiles[self::$_lessFilesCounter] = SITE_TEMPLATE_PATH."/".$lessFilePath;
						self::$_arLessFilesSort[self::$_lessFilesCounter] = $sort;
						self::$_lessFilesCounter++;
						return true;
					}
				}
			}
			return false;
		}
		static public function getLessFilesList($lessCompiledFileExt = null) {
			if($lessCompiledFileExt === null) {
				self::setLessCompiledExt($lessCompiledFileExt);
			}
			return self::$_arLessFiles;
		}
		/**
		 * @static
		 * @param $component
		 * @param null $lessFilePath
		 * @param $sort
		 * @return bool
		 */
		static public function addComponentLess($component, $lessFilePath = null, $sort = 500) {
			/**
			 * @var \CMain $APPLICATION
			 * @var \CBitrixComponent $component
			 */
			$templateFolder = null;
			if($component instanceof \CBitrixComponent) {
				$templateFolder = $component->__template->__folder;
			}
			elseif($component instanceof \CBitrixComponentTemplate) {
				$template = &$component;
				$templateFolder = $template->__folder;
			}
			elseif( is_string($component) ) {
				if(
					($bxrootpos = strpos($component, BX_ROOT."/templates")) !== false
					||
					($bxrootpos = strpos($component, BX_ROOT."/components")) !== false
				) {
					$component = substr($component, $bxrootpos);
				}
				if( ($extpos = strrpos($component, ".php")) !== false
					|| ($extpos = strrpos($component, ".less")) !== false
				) {
					if( $dirseppos = strrpos($component, "/") ) {
						$templateFolder = substr($component, 0, $dirseppos);
						if($lessFilePath == null && strrpos($component, ".less") !== false) {
							$lessFilePath = substr($component, $dirseppos);
							$lessFilePath = ltrim($lessFilePath, '/');
						}
					}
				}
				else {
					$templateFolder = $component;
				}
			}
			$sort = intval($sort);
			if( $lessFilePath == null ) {
				if( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/style.less")
					|| (is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/style.less.css")
						&& self::$_bLessProduction)
				) {
					$lessFilePath = str_replace(
						array('//', '///'), array('/', '/'),
						$templateFolder."/style.less"
					);
					if( !in_array($lessFilePath, self::$_arLessFiles) ) {
						self::$_arLessFiles[self::$_lessFilesCounter] = $lessFilePath;
						self::$_arLessFilesSort[self::$_lessFilesCounter] = $sort;
						self::$_lessFilesCounter++;
						return true;
					}
					return true;
				}
			}
			elseif( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/".$lessFilePath)
				|| (is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/".$lessFilePath.".css")
					&& self::$_bLessProduction )
			) {
				$lessFilePath = str_replace(
					array('//', '///'), array('/', '/'),
					$templateFolder."/".$lessFilePath
				);
				if( substr($lessFilePath, -5) == ".less" ) {
					if( !in_array($lessFilePath, self::$_arLessFiles) ) {
						self::$_arLessFiles[self::$_lessFilesCounter] = $lessFilePath;
						self::$_arLessFilesSort[self::$_lessFilesCounter] = $sort;
						self::$_lessFilesCounter++;
						return true;
					}
				}
			}
			return false;
		}
		static public function setLessProductionReady($bCompiled = true) {
			self::$_bLessProduction = ($bCompiled)?true:false;
		}

		///////////////////////////////////
		/// CONNECTNG DEFERRED JS FILES ///
		static private $_arDeferredJSFiles = array();
		static private $_deferredFileCounter = 0;
		static private $_arDeferredJSFilesSort = array();

		/**
		 * @param string $jsFilePath
		 * @param int $sort
		 * @return bool
		 */
		static public function addDeferredJS($jsFilePath, $sort = 500) {
			if( !in_array($jsFilePath, self::$_arDeferredJSFiles) ) {
				if( substr($jsFilePath, -3) == ".js" ) {
					$sort = intval($sort);
					if( is_file($_SERVER["DOCUMENT_ROOT"].$jsFilePath) ) {
						self::$_arDeferredJSFiles[self::$_deferredFileCounter] = $jsFilePath;
						self::$_arDeferredJSFilesSort[self::$_deferredFileCounter] = $sort;
						self::$_deferredFileCounter++;
						return true;
					}
					elseif( is_file($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/".$jsFilePath) ) {
						self::$_arDeferredJSFiles[self::$_deferredFileCounter] = SITE_TEMPLATE_PATH."/".$jsFilePath;
						self::$_arDeferredJSFilesSort[self::$_deferredFileCounter] = $sort;
						self::$_deferredFileCounter++;
						return true;
					}
				}
			}
			return false;
		}
		static public function __sortDefJSFiles($fileIndexA, $fileIndexB) {
			$sortA = intval(self::$_arDeferredJSFilesSort[$fileIndexA] * 100 + $fileIndexA);
			$sortB = intval(self::$_arDeferredJSFilesSort[$fileIndexB] * 100 + $fileIndexB);
			if($sortA == $sortB) return 0;
			return ($sortA < $sortB)? -1 : 1;
		}
		static public function getDeferredJS() {
			$returnString = '';
			uksort(self::$_arDeferredJSFiles, '\OBX\Core\Tools::__sortDefJSFiles');
			foreach(self::$_arDeferredJSFiles as $jsFilePath) {
				$returnString .= '<script type="text/javascript" src="'.$jsFilePath.'"></script>'."\n";
			}
			return $returnString;
		}
		static public function getDeferredJSFilesList() {
			return self::$_arDeferredJSFiles;
		}
		static public function showDeferredJS() {
			/** @var \CMain $APPLICATION */
			global $APPLICATION;
			$APPLICATION->AddBufferContent('OBX\Core\Tools::getDeferredJS');
		}
		/**
		 * @static
		 * @param \CBitrixComponent|string $component
		 * @param null $jsFilePath
		 * @param int $sort
		 * @return bool
		 */
		static public function addComponentDeferredJS($component, $jsFilePath = null, $sort = 500) {
			/** @var \CMain $APPLICATION */
			$templateFolder = null;
			if($component instanceof \CBitrixComponent) {
				$templateFolder = $component->__template->__folder;
			}
			elseif($component instanceof \CBitrixComponentTemplate) {
				$template = &$component;
				$templateFolder = $template->__folder;
			}
			elseif( is_string($component) ) {
				if(
					($bxrootpos = strpos($component, BX_ROOT."/templates")) !== false
					||
					($bxrootpos = strpos($component, BX_ROOT."/components")) !== false
				) {
					$component = substr($component, $bxrootpos);
				}
				if( ($extpos = strrpos($component, ".php")) !== false
					|| ($extpos = strrpos($component, ".js")) !== false
				) {
					if( $dirseppos = strrpos($component, "/") ) {
						$templateFolder = substr($component, 0, $dirseppos);
						if($jsFilePath == null && strrpos($component, ".js") !== false) {
							$jsFilePath = substr($component, $dirseppos);
							while( substr($jsFilePath, 0, 1) == "/" ) {
								$jsFilePath = substr($jsFilePath, 1);
							}
						}
					}
				}
				else {
					$templateFolder = $component;
				}
			}
			$sort = intval($sort);
			if( $jsFilePath == null ) {
				if( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script_deferred.js") ) {
					$jsFilePath = str_replace(
						array('//', '///'), array('/', '/'),
						$templateFolder."/script_deferred.js"
					);
					if( !in_array($jsFilePath, self::$_arDeferredJSFiles) ) {
						self::$_arDeferredJSFiles[self::$_deferredFileCounter] = $jsFilePath;
						self::$_arDeferredJSFilesSort[self::$_deferredFileCounter] = $sort;
						self::$_deferredFileCounter++;
						return true;
					}
					return true;
				}
			}
			elseif( is_file($_SERVER["DOCUMENT_ROOT"].$templateFolder."/".$jsFilePath) ) {
				$jsFilePath = str_replace(
					array('//', '///'), array('/', '/'),
					$templateFolder."/".$jsFilePath
				);
				if( substr($jsFilePath, -3) == ".js" ) {
					if( !in_array($jsFilePath, self::$_arDeferredJSFiles) ) {
						self::$_arDeferredJSFiles[self::$_deferredFileCounter] = $jsFilePath;
						self::$_arDeferredJSFilesSort[self::$_deferredFileCounter] = $sort;
						self::$_deferredFileCounter++;
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Форматирование даты с особенностями русского языка
		 * @param string $bxDateString
		 * @param int $dayStep
		 * @return arrayl
		 * @author pr0n1x
		 * @year 2009
		 */
		static public function bxDateToArray($bxDateString, $dayStep = 0) {
			$bxDateString = trim($bxDateString);
			if ($bxDateString == 'today') {
				$bxDateString = \ConvertTimeStamp(time(), "FULL");
			}
			if(strlen($bxDateString)==0) {
				return array();
			}
			$itemDate = array();
			$itemDate["STRING"] = $bxDateString;
			$itemDate["Year"] = ConvertDateTime($itemDate["STRING"], "YYYY");
			$itemDate["Month"] = ConvertDateTime($itemDate["STRING"], "MM");
			$itemDate["Day"] = ConvertDateTime($itemDate["STRING"], "DD");
			$itemDate["Hour"] = ConvertDateTime($itemDate["STRING"], "HH");
			$itemDate["Minute"] = ConvertDateTime($itemDate["STRING"], "MI");
			$itemDate["Second"] = ConvertDateTime($itemDate["STRING"], "SS");

			$itemDate["Day"] = $itemDate["Day"] + $dayStep;

			$itemDate["TIME_STAMP"] = mktime(
				$itemDate["Hour"],
				$itemDate["Minute"],
				$itemDate["Second"],
				$itemDate["Month"],
				$itemDate["Day"],
				$itemDate["Year"]
			);
			$itemDate["STRING"] = ConvertTimeStamp($itemDate["TIME_STAMP"], "SHORT");
			$itemDate["STRING_FULL"] = ConvertTimeStamp($itemDate["TIME_STAMP"], "FULL");
			//echo $itemTimeStamp."<br />";
			//echo "|".$arItem["DATE"]."|".date("d.m.Y", $itemTimeStamp)."<br />";

			/************************************************************************************
			 *** Постфиксы для названий ключей чассивов дней недели и месяцев для русского языка
			 ***
			 ***	Именительный	Номинатив (Nominative)		Кто? Что?		Ru, RuN
			 ***	Родительный 	Генитив (Genitive)			Кого? Чего?		RuG
			 ***	Дательный		Датив (Dative)		 		Кому? Чему?		RuD
			 ***	Винительный		Аккузатив (Accusative)		Кого? Что?		RuA
			 ***	Творительный	Аблатив (Instrumentative)	Кем? Чем?		RuI
			 ***	Предложный		Препозитив (Preposition)	О ком? О чём?	RuP
			 ***/

			switch($itemDate["Month"]) {
				case 1:
					$itemDate["MonthEn"] = "January";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('January');
					$itemDate["MonthRuG"] = GetMessage('JanuaryG');
					break;
				case 2:
					$itemDate["MonthEn"] = "February";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('February');
					$itemDate["MonthRuG"] = GetMessage('FebruaryG');
					break;
				case 3:
					$itemDate["MonthEn"] = "March";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('March');
					$itemDate["MonthRuG"] = GetMessage('MarchG');
					break;
				case 4:
					$itemDate["MonthEn"] = "April";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('April');
					$itemDate["MonthRuG"] = GetMessage('AprilG');
					break;
				case 5:
					$itemDate["MonthEn"] = "May";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('May');
					$itemDate["MonthRuG"] = GetMessage('MayG');
					break;
				case 6:
					$itemDate["MonthEn"] = "June";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('June');
					$itemDate["MonthRuG"] = GetMessage('JuneG');
					break;
				case 7:
					$itemDate["MonthEn"] = "July";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('July');
					$itemDate["MonthRuG"] = GetMessage('JulyG');
					break;
				case 8:
					$itemDate["MonthEn"] = "August";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('August');
					$itemDate["MonthRuG"] = GetMessage('AugustG');
					break;
				case 9:
					$itemDate["MonthEn"] = "September";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('September');
					$itemDate["MonthRuG"] = GetMessage('SeptemberG');
					break;
				case 10:
					$itemDate["MonthEn"] = "October";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('October');
					$itemDate["MonthRuG"] = GetMessage('OctoberG');
					break;
				case 11:
					$itemDate["MonthEn"] = "November";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('November');
					$itemDate["MonthRuG"] = GetMessage('NovemberG');
					break;
				case 12:
					$itemDate["MonthEn"] = "December";
					$itemDate["MonthRu"] = $itemDate["MonthRuN"] = GetMessage('December');
					$itemDate["MonthRuG"] = GetMessage('DecemberG');
					break;
			}

			$itemDate["DayOfWeek"] = date("w", $itemDate["TIME_STAMP"]);
			switch ($itemDate["DayOfWeek"]) {
				case 1:
					$itemDate["DayOfWeekRu"] = $itemDate["DayOfWeekRuN"] = GetMessage('Monday');
					$itemDate["DayOfWeekRuA"] = GetMessage('MondayA');
					$itemDate["DayOfWeekEn"] = "Monday";
					break;
				case 2:
					$itemDate["DayOfWeekRu"] = GetMessage('Tuesday');
					$itemDate["DayOfWeekRuA"] = GetMessage('TuesdayA');
					$itemDate["DayOfWeekEn"] = "Tuesday";
					break;
				case 3:
					$itemDate["DayOfWeekRu"] = GetMessage('Wednesday');
					$itemDate["DayOfWeekRuA"] = GetMessage('WednesdayA');
					$itemDate["DayOfWeekEn"] = "Wednesday";
					break;
				case 4:
					$itemDate["DayOfWeekRu"] = GetMessage('Thursday');
					$itemDate["DayOfWeekRuA"] = GetMessage('ThursdayA');
					$itemDate["DayOfWeekEn"] = "Thursday";
					break;
				case 5:
					$itemDate["DayOfWeekRu"] = GetMessage('Friday');
					$itemDate["DayOfWeekRuA"] = GetMessage('FridayA');
					$itemDate["DayOfWeekEn"] = "Friday";
					break;
				case 6:
					$itemDate["DayOfWeekRu"] = GetMessage('Saturday');
					$itemDate["DayOfWeekRuA"] = GetMessage('SaturdayA');
					$itemDate["DayOfWeekEn"] = "Saturday";
					break;
				case 7:
					$itemDate["DayOfWeekRu"] = GetMessage('Sunday');
					$itemDate["DayOfWeekRuA"] = GetMessage('SundayA');
					$itemDate["DayOfWeekEn"] = "Sunday";
			}

			return $itemDate;
		}

		/****************************************************
		 * Эти ф-ии позволяют работать с двумя				*
		 * массивами, в которых есть соотношения			*
		 * значений по поряковому номеру.					*
		 * Удобно использоват для разработки фотогалереи	*
		 * на множественных свойствах.						*
		 * т.е. есть три множ. св-ва						*
		 * ORIGINAL, DETAIL, PREVIEW.						*
		 * По сути это одни и те же фотки только с			*
		 * разным разрешением. Потому необходимо			*
		 * определять номер множ. значения в одном			*
		 * свойстве, по номеру множ. значения (фотки)		*
		 * в другом свойсте									*
		 * pr0n1x: 2009										*
		 ****************************************************/
		/**
		 *	Ф-ия показывает порядкоывай номер ключа массива
		 * @param array $array	- массив
		 * @param string $key		- искомый ключ
		 * @return int | false	- проядковый номер ключа
		 */
		static public function getNumOfKey($array, $key) {
			$array = array_keys($array);
			$array = array_flip($array);
			if ( array_key_exists($key, $array) ) {
				return $array[$key];
			}
			else return false;
		}

		/**
		 * Ф-ия получает имя ключа по порядковому номеру элемента
		 * @param array $array			- масстив
		 * @param int $num				- порядковый номер элемента
		 * @return string | int |false	- ключ элемента под искомым порядковым номером
		 */
		static public function getKeyBySeqNum($array, $num) {
			$arrayNums = array_keys($array);
			if ( array_key_exists($num, $arrayNums) ) {
				return $arrayNums[$num];
			}
			else return false;
		}

		/**
		 *	Ф-ия находит имя ключа в массиве $arrayHaystack порядковый номер которого
		 *	тот же что и у ключа $needleKey в массиве $arrayWithKey
		 * @param array $arrayHaystack
		 * @param string | int $needleKey
		 * @param array $arrayWithKey
		 * @return string | int | false
		 */
		static public function getKeyOfSameNumber($arrayHaystack, $needleKey, $arrayWithKey) {
			//d($arrayHaystack, true);
			//d($arrayWithKey, true);
			//d($needleKey);
			//d(getNumOfKey($arrayWithKey, $needleKey));
			return self::getKeyBySeqNum(
				$arrayHaystack,
				self::getNumOfKey($arrayWithKey, $needleKey)
			);
		}
		static public function getKeyOfSameNumberEx(&$arrayHaystack, $needleKey, $arrayWithKey) {
			//	d($arrayHaystack, true);
			//	d($arrayWithKey, true);
			//	d($needleKey);
			//	d(getNumOfKey($arrayWithKey, $needleKey));
			if (count($arrayWithKey) <= count($arrayHaystack)) {
				return self::getKeyBySeqNum(
					$arrayHaystack,
					self::getNumOfKey($arrayWithKey, $needleKey)
				);
			}
			else {
				return $needleKey;
			}
		}

		/**
		 * Debug data print
		 * @param mixed $mixed
		 * @param mixed $collapse
		 * @param bool $bPrint
		 */
		static public function debug($mixed, $collapse = null, $bPrint = true) {
			if(!$bPrint) {
				return;
			}
			static $arCountFuncCall = 0;
			static $arCountFuncCallWithTitleKey = array();
			$arCountFuncCall++;

			$bCollapse = false;
			if($collapse !== null) {
				$bCollapse = true;
				if( is_string($collapse) && strlen($collapse)>0) {
					if( !@isset($arCountFuncCallWithTitleKey[$collapse]) ) {
						$arCountFuncCallWithTitleKey[$collapse] = 0;
					}
					$arCountFuncCallWithTitleKey[$collapse]++;

					$elemTitle = $collapse."#".$arCountFuncCallWithTitleKey[$collapse];
					$elemId = rand(1,500).$collapse."#".$arCountFuncCallWithTitleKey[$collapse];
				}
				else {
					$elemTitle = "dData#".$arCountFuncCall;
					$elemId = rand(1,500).$arCountFuncCall;
				}
				$elemId = str_replace(array("'", '"'), "_", $elemId);
			}
			?>
			<?php if($bCollapse):?>
				<a	href="javascript:void(0)"
					  style="display: block;background: white; border:1px dotted #5A82CE;padding:3px; text-shadow: none; color: #5A82CE;"
					  onclick="document.getElementById('<?php echo $elemId?>').style.display = ( document.getElementById('<?php echo $elemId?>').style.display == 'none')?'block':'none'"
					>
					<?php echo $elemTitle?>
				</a>
				<div id="<?php echo $elemId?>" style="text-align: left; display:none; background-color: #b1cdef; position: absolute; z-index: 10000;">
			<?php endif?>

			<pre style="text-align: left; text-shadow: none; color: black;"><?php print_r($mixed);?></pre>

			<?php if ($bCollapse):?>
				</div>
			<?php endif;
		}
	}
}
