<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core;
class EventD {
	const PATH_TO_USRINC_DIR = '/bitrix/php_interface/';
	const PATH_TO_EVENT_D = '/bitrix/php_interface/event.d';

	public function connectAllEvents() {
		$pathEventD = $_SERVER['DOCUMENT_ROOT'].self::PATH_TO_EVENT_D;

		if( !is_dir($pathEventD)) {
			return false;
		}

		$dirEventD = opendir($pathEventD);
		$arFilesList = array();
		self::fillDirFilesList($dirEventD, $arFilesList);

		if( is_dir($pathEventD.'/'.SITE_ID) ) {
			$dirSiteEventD = opendir($pathEventD.'/'.SITE_ID);
			self::fillDirFilesList($dirSiteEventD, $arFilesList, SITE_ID.'/');
		}

		foreach($arFilesList as $eventFileName) {
			$eventFilePath = $pathEventD.'/'.$eventFileName;
			@include $eventFilePath;
		}
	}

	static protected function fillDirFilesList(&$dirHandler, array &$arFilesList, $pathPrefix = '') {
		while ( $elementOfDir = readdir($dirHandler) ) {
			if (
				$elementOfDir != '..'
				&& $elementOfDir != '.'
				&& substr($elementOfDir, strlen($elementOfDir)-4, strlen($elementOfDir)) == '.php'
			) {
				$arFilesList[] = $pathPrefix.$elementOfDir;
			}
		}
	}
}
//RegisterModuleDependences('main', 'OnPageStart', 'obx.core', 'OBX\Core\EventD', 'connectAllEvents', '10');