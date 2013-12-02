<?php
namespace OBX\Core\Test;


class TestIncFileOne {

	const _FILE_ = __FILE__;

	static protected $data = array();

	static public function getFilePath() {
		return static::_FILE_;
	}

	static public function setStaticData($key, $value){
		static::$data[$key] = $value;
	}

	static public function getStaticData() {
		return static::$data;
	}

	static public function getClass() {
		return __CLASS__;
	}
}