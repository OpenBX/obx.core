<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Components;


class Parameters {
	static protected $_arInstances = array();

	/**
	 * @return self
	 */
	static public function getInstance() {
		$class = get_called_class();
		if( !array_key_exists($class, self::$_arInstances) ) {
			self::$_arInstances[$class] = new $class;
		}
		return self::$_arInstances[$class];
	}

	protected function __construct() {}
	final private function __clone() {}

	public function getTextArea($name, $cols = 32, $rows = 4) {
		$cols = intval($cols);
		$rows = intval($rows);
		return array(
			'NAME' => $name,
			'TYPE' => 'CUSTOM',
			'JS_DATA' => $cols.'||'.$rows,
			'JS_FILE' => '/bitrix/js/obx.core/component.params.js',
			'JS_EVENT' => 'obx.componentParams.showTextArea'
		);
	}
}