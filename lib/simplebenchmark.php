<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;


class SimpleBenchMark {
	static protected $arStart = array();
	static protected $arStop = array();
	static public function start($ID) {
		self::$arStart[$ID] = microtime(true);
		self::$arStop[$ID] = self::$arStart[$ID]-1;
	}

	static public function stop($ID) {
		self::$arStop[$ID] = microtime(true);
		return (self::$arStop[$ID] - self::$arStart[$ID]);
	}

	static public function getResult($ID) {
		return (self::$arStop[$ID] - self::$arStart[$ID]);
	}
} 