<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 DevTop
 */

namespace OBX\Core\LessCss;

use OBX\Core\Exceptions\LessCss\LessCssError;

class Connector
{

	const DEF_LESSJS_PATH = '/bitrix/js/obx.core/less-1.7.3.min.js';

	static protected $instances = array();
	static protected $cacheSitesIDList = null;

	final static public function getInstance($siteID = SITE_ID) {
		if(empty($siteID) && !defined(SITE_ID)) {
			throw new LessCssError('SITE_ID wasn\'t defined');
		}
		if( array_key_exists($siteID, static::$instances) ) {
			return static::$instances[$siteID];
		}
		$class = get_called_class();
		/**
		 * @var self $LessConnector
		 */
		$LessConnector = new $class($siteID);
		static::$instances[$siteID] = $LessConnector;
		return $LessConnector;
	}

	final static public function getSitesIDList() {
		if(null === self::$cacheSitesIDList) {
			self::$cacheSitesIDList = array();
			/** @noinspection PhpDynamicAsStaticMethodCallInspection */
			$rsSiteList = \CSite::GetList($by='SORT', $order='ASC');
			while($arSite = $rsSiteList->Fetch()) {
				self::$cacheSitesIDList[] = $arSite['ID'];
			}
		}
		return self::$cacheSitesIDList;
	}

	protected $_siteID = null;
	protected $_arFiles = array();
	protected $_arFilesSort = array();
	protected $_FilesCounter = 0;
	protected $_bProduction = null;
	protected $_lessCompiledExt = '.css';
	protected $_lessJSPath = null;
	protected $_bFilesConnected = false;
	protected $_bJSHeadConnected = false;
	protected $_bConnectJSFileAfterLessFiles = false;

	protected function __construct($siteID) {
		$arSiteIDList = self::getSitesIDList();
		if( !in_array($siteID, $arSiteIDList) ) {
			throw new LessCssError('', LessCssError::E_SITE_NOT_FOUND);
		}
		$this->_siteID = $siteID;
	}

	public function isProductionReady() {
		if($this->_bProduction === null) {
			$optLessProduction = \COption::GetOptionString('obx.core', 'LESSCSS_PROD_READY_'.$this->_siteID, 'N');
			if($optLessProduction == 'Y') {
				$this->_bProduction = true;
			}
			else {
				$this->_bProduction = false;
			}
		}
		return $this->_bProduction;
	}

	public function getHead() {
		$returnString = '';
		uksort($this->_arFiles, array($this, '__sortLessFiles'));
		foreach($this->_arFiles as $lessFilePath) {
			$compiledLessFilePath = substr($lessFilePath, 0, -5).$this->_lessCompiledExt;
			if(!$this->isProductionReady()) {
				$returnString .= '<link rel="stylesheet/less" type="text/css" href="'.$lessFilePath.'">'."\n";
			}
			else {
				$returnString .= '<link rel="stylesheet" type="text/css" href="'.$compiledLessFilePath.'">'."\n";
			}
		}
		return $returnString;
	}
	public function getJSHead() {
		$returnString = '';
		if( null === $this->_lessJSPath ) {
			$this->_lessJSPath = self::DEF_LESSJS_PATH;
		}
		$returnString .= '<script type="text/javascript"> less = { env: \'development\' }; </script>'."\n";
		$returnString .= '<script type="text/javascript" src="'.$this->_lessJSPath.'"></script>'."\n";
		//$returnString .= '<script type="text/javascript">less.watch();</script>'."\n";
		return $returnString;
	}
	public function showHead() {
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array($this, 'getHead'));
		$this->_bFilesConnected = true;
		if( $this->_bConnectJSFileAfterLessFiles ) {
			$APPLICATION->AddBufferContent(array($this, 'getJSHead'));
			$this->_bConnectJSFileAfterLessFiles = false;
			$this->_bJSHeadConnected = true;
		}
	}
	public function showJSHead($bWaitWhileLessFilesConnected = true) {
		if( $bWaitWhileLessFilesConnected && !$this->_bFilesConnected ) {
			$this->_bConnectJSFileAfterLessFiles = true;
			return;
		}
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array($this, 'getJSHead'));
		$this->_bJSHeadConnected = true;
	}
	public function setLessJSPath($lessJSPath, $bShowLessHead = true) {
		if( strpos($lessJSPath, 'less')===false || substr($lessJSPath, -3)!='.js' ) {
			throw new LessCSSError('', LessCSSError::E_LESS_JS_FILE_NOT_FOUND);
		}
		if( is_file(OBX_DOC_ROOT.$lessJSPath) ) {
			$this->_lessJSPath = $lessJSPath;
		}
		elseif( is_file(OBX_DOC_ROOT.SITE_TEMPLATE_PATH.'/'.$lessJSPath) ) {
			$lessJSPath = str_replace(array('//', '\\'), '/', SITE_TEMPLATE_PATH.'/'.$lessJSPath);
			$lessJSPath = str_replace('../', '', $lessJSPath);
			$lessJSPath = '/'.trim($lessJSPath, '/');
			$this->_lessJSPath = $lessJSPath;
		}
		else {
			throw new LessCSSError('', LessCSSError::E_LESS_JS_FILE_NOT_FOUND);
		}
		if( $bShowLessHead ) {
			if( !$this->_bFilesConnected ) {
				$this->_bConnectJSFileAfterLessFiles = false;
				$this->showHead();
			}
			if( !$this->_bJSHeadConnected ) {
				$this->showJSHead();
			}
		}
		return true;
	}
	public function getLessJSPath() {
		return $this->_lessJSPath;
	}
	public function setCompiledExt($ext) {
		if( preg_match('~^\.[a-zA-Z0-9\_\-]*\.css$~', $ext)) {
			$this->_lessCompiledExt = $ext;
		}
	}
	/**
	 * @param $lessFilePath
	 * @param int $sort
	 * @return bool
	 */
	public function addFile($lessFilePath, $sort = 500) {
		if( !in_array($lessFilePath, $this->_arFiles) ) {
			if( substr($lessFilePath, -5) == '.less' ) {
				$compiledLessFilePath = substr($lessFilePath, 0, -5).$this->_lessCompiledExt;
				$sort = intval($sort);
				if( is_file(OBX_DOC_ROOT.$lessFilePath)
					|| (
						is_file(OBX_DOC_ROOT.$compiledLessFilePath)
						&& $this->isProductionReady())
				) {
					$this->_arFiles[$this->_FilesCounter] = $lessFilePath;
					$this->_arFilesSort[$this->_FilesCounter] = $sort;
					$this->_FilesCounter++;
					return true;
				}
				elseif(
					is_file(OBX_DOC_ROOT.SITE_TEMPLATE_PATH.'/'.$lessFilePath)
					|| (
						is_file(OBX_DOC_ROOT.SITE_TEMPLATE_PATH.'/'.$compiledLessFilePath)
						&& $this->isProductionReady()
					)
					//					// На случай если мы будем комипировать less в папку css
					//					|| (
					//						substr($compiledLessFilePath, 0, 5) == 'less/'
					//						&& $this->isLessProductionReady()
					//						&& is_file(OBX_DOC_ROOT.SITE_TEMPLATE_PATH.'/css/'.substr($compiledLessFilePath, 5))
					//					)
				) {
					$this->_arFiles[$this->_FilesCounter] = SITE_TEMPLATE_PATH.'/'.$lessFilePath;
					$this->_arFilesSort[$this->_FilesCounter] = $sort;
					$this->_FilesCounter++;
					return true;
				}
			}
		}
		return false;
	}
	public function getFilesList($lessCompiledFileExt = null) {
		if($lessCompiledFileExt === null) {
			$this->setCompiledExt($lessCompiledFileExt);
		}
		return $this->_arFiles;
	}

	public function clearFilesList() {
		$this->_arFiles = array();
		$this->_arFilesSort = array();
	}

	/**
	 * @static
	 * @param \CBitrixComponent|\CBitrixComponentTemplate|string $component
	 * @param null $lessFilePath
	 * @param $sort
	 * @return bool
	 */
	public function addComponentFile($component, $lessFilePath = null, $sort = 500) {
		/**
		 * @global \CMain $APPLICATION
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
			$component = str_replace(array('\\', '//'), '/', $component);
			if(
				($bxrootpos = strpos($component, BX_ROOT.'/templates')) !== false
				||
				($bxrootpos = strpos($component, BX_ROOT.'/components')) !== false
			) {
				$component = substr($component, $bxrootpos);
			}
			if( ($extpos = strrpos($component, '.php')) !== false
				|| ($extpos = strrpos($component, '.less')) !== false
			) {
				if( $dirseppos = strrpos($component, '/') ) {
					$templateFolder = substr($component, 0, $dirseppos);
					if($lessFilePath == null && strrpos($component, '.less') !== false) {
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
			if( is_file(OBX_DOC_ROOT.$templateFolder.'/style.less')
				|| (is_file(OBX_DOC_ROOT.$templateFolder.'/style.less.css')
					&& $this->isProductionReady())
			) {
				$lessFilePath = str_replace(
					array('//', '///'), array('/', '/'),
					$templateFolder.'/style.less'
				);
				if( !in_array($lessFilePath, $this->_arFiles) ) {
					$this->_arFiles[$this->_FilesCounter] = $lessFilePath;
					$this->_arFilesSort[$this->_FilesCounter] = $sort;
					$this->_FilesCounter++;
					return true;
				}
				return true;
			}
		}
		elseif( is_file(OBX_DOC_ROOT.$templateFolder.'/'.$lessFilePath)
			|| (is_file(OBX_DOC_ROOT.$templateFolder.'/'.$lessFilePath.'.css')
				&& $this->isProductionReady() )
		) {
			$lessFilePath = str_replace(
				array('//', '///'), array('/', '/'),
				$templateFolder.'/'.$lessFilePath
			);
			if( substr($lessFilePath, -5) == '.less' ) {
				if( !in_array($lessFilePath, $this->_arFiles) ) {
					$this->_arFiles[$this->_FilesCounter] = $lessFilePath;
					$this->_arFilesSort[$this->_FilesCounter] = $sort;
					$this->_FilesCounter++;
					return true;
				}
			}
		}
		return false;
	}
	public function setProductionReady($bCompiled = true) {
		$this->_bProduction = ($bCompiled)?true:false;
	}

	public function __sortLessFiles($fileIndexA, $fileIndexB) {
		$sortA = intval($this->_arFilesSort[$fileIndexA] * 100 + $fileIndexA);
		$sortB = intval($this->_arFilesSort[$fileIndexB] * 100 + $fileIndexB);
		if($sortA == $sortB) return 0;
		return ($sortA < $sortB)? -1 : 1;
	}

	static public function showBufferContentHead($templateID){

	}
}
