<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

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

	/**
	 * @param $arParameter
	 * @param array $arChooserCurVals - если arParameter[MULTIPLE] == Y
	 * 			тогда надо обазательно пробрасывать arCurrentValues["PARAM_NAME"] во второй агрумент
	 * @return array
	 *
	 * @example
	 * $arChooserValues = (empty($arCurrentValues['CHOOSER'])?array():$arCurrentValues['CHOOSER']);
	 * $arComponentParameters['PARAMETERS']['CHOOSER'] = $ParameterTools->getListChooser(
	 * 		array(
	 * 			'NAME' => 'CHOOSER',
	 * 			'VALUES' => array(
	 * 				'key1' => 'значение 1',
	 * 				'key2' => 'значение 2',
	 * 				'key3' => 'значение 3',
	 * 				'key4' => 'значение 4',
	 * 			),
	 * 			'DEFAULT' => 'key2',
	 * 			'MULTIPLE' => 'Y',
	 * 			'PARENT' => 'BASE'
	 * 		),
	 * 		$arChooserValues
	 * );
	 */
	public function getListChooser($arParameter, &$arChooserCurVals = array()) {
		if(!is_array($arChooserCurVals)) $arChooserCurVals = array();
		$arParameter['IX_CUR_VALS'] = array_flip($arChooserCurVals);
		//TODO: Сделать обработку $arParameter
		return array(
			'NAME' => $arParameter['NAME'],
			'TYPE' => 'CUSTOM',
			'JS_DATA' => \CUtil::PhpToJSObject($arParameter),
			'JS_FILE' => $this->_customParamsJSLib,
			'JS_EVENT' => 'obx.componentParams.showListChooser'
		);
	}

}