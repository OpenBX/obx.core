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

	protected $_customParamsJSLib = '/bitrix/js/obx.core/component.params.js';

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

	/**
	 * @param $name
	 * @param int $cols
	 * @param int $rows
	 * @return array
	 * @deprecated - есть штатный тип FILE
	 */
	public function getTextArea($name, $cols = 32, $rows = 4) {
		$cols = intval($cols);
		$rows = intval($rows);
		return array(
			'NAME' => $name,
			'TYPE' => 'CUSTOM',
			'JS_DATA' => $cols.'||'.$rows,
			'JS_FILE' => $this->_customParamsJSLib,
			'JS_EVENT' => 'obx.componentParams.showTextArea'
		);
	}

	public function getListChooser($arParameter) {
		//TODO: Сделать обработку $arParameter
		return array(
			'NAME' => $arParameter['NAME'],
			'TYPE' => 'CUSTOM',
			'JS_DATA' => \CUtil::PhpToJSObject($arParameter),
			'JS_FILE' => $this->_customParamsJSLib,
			'JS_EVENT' => 'obx.componentParams.showRadioList'
		);
	}

}