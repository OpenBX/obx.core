<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

use OBX\Core\Test\TestCase;
class TestInstallResFilePatterns extends TestCase {
	const _DIR_ = __DIR__;

	static protected $arResourcesPathPatterns = null;
	static protected $resPattern = null;
	static protected $testModulePath = null;

	static public function setUpBeforeClass() {
		self::$testModulePath = OBX_DOC_ROOT.BX_ROOT.'/modules/obx.market';

		$filenamePattern = '(?:[a-z-A-Z0-9\._\-]+)';
		self::$arResourcesPathPatterns = array(
			'\./install/admin/'.$filenamePattern.'\.php',
			'\./install/admin/ajax/'.$filenamePattern.'\.php',
			'\./install/components/'.$filenamePattern.'/'.$filenamePattern.'/',
			'\./install/wizards/'.$filenamePattern.'/'.$filenamePattern.'/',
			'\./install/js/'.$filenamePattern.'/'.'(?:/|'.$filenamePattern.'\.js)?',
			'\./install/images/'.$filenamePattern.'/'.$filenamePattern.'(?:/|\.(?:jpg|jpeg|png|gif))?',
			'\./install/tools/'.$filenamePattern.'/'.$filenamePattern.'\.php',
			'\./install/themes/\.default/icons/'.$filenamePattern.'(?:/|\.(?:jpg|jpeg|png|gif))',
			'\./install/themes/\.default/'.$filenamePattern.'(?:/|\.css)?',
			'\./install/php_interface/'.$filenamePattern.'/'.$filenamePattern.'\.php',
		);
		$bFirst = true;
		self::$resPattern .= '~(?:';
		foreach(self::$arResourcesPathPatterns as $patterChunk) {
			self::$resPattern .= ($bFirst?'':'|').$patterChunk;
			$bFirst = false;
		}
		self::$resPattern .= ')~';
	}

	protected function getFilesList($path, $curPosition) {
		$dirHandler = opendir($path.'/'.$curPosition);
		$arFSEntryList = array();
		while ( $fsEntry = readdir($dirHandler) ) {
			if ($fsEntry == '..' || $fsEntry == '.' ) continue;
			if(is_dir($path.'/'.$curPosition.'/'.$fsEntry)) {
				$arFSEntryList = array_merge($arFSEntryList, $this->getFilesList($path, $curPosition.'/'.$fsEntry));
			}
			else {
				$arFSEntryList[] = $curPosition.'/'.$fsEntry;
			}
		}
		return $arFSEntryList;
	}

	public function testPatterns() {
		$arFiles = $this->getFilesList(self::$testModulePath, './install');
		$arSkippedFiles = array();
		foreach($arFiles as $path) {
			$debug=1;
//			foreach(self::$arResourcesPathPatterns as $pattern) {
//				$bmatch = preg_match('~'.$pattern.'~', $path, $arMatches);
//				$debug=1;
//				if($bmatch) {
//					$debug=1;
//				}
//			}
			$bmatch = preg_match(self::$resPattern, $path, $arMatches);
			if(!$bmatch) {
				$arSkippedFiles[] = $path;
			}
			$debug=1;
		}
		$debug=1;
	}
}