<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;
class EventD {
	const BITRIX_EVENT_D = '/bitrix/php_interface/event.d';
	const LOCAL_EVENT_D = '/local/php_interface/event.d';

	public static function connectAllEvents() {
		self::connectBitrixUsrEvents();
		self::connectLocalUsrEvents();
	}

	public static function connectBitrixUsrEvents() {
		$pathEventD = $_SERVER['DOCUMENT_ROOT'].self::BITRIX_EVENT_D;
		if( !is_dir($pathEventD)) {
			return;
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
			/** @noinspection PhpIncludeInspection */
			@include $eventFilePath;
		}
	}
	public static function connectLocalUsrEvents() {
		$pathEventD = $_SERVER['DOCUMENT_ROOT'].self::LOCAL_EVENT_D;
		if( !is_dir($pathEventD)) {
			return;
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
			/** @noinspection PhpIncludeInspection */
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