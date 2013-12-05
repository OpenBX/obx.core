<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 05.12.13
 * Time: 17:53
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