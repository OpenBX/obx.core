<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto pr0n1x@yandex.ru
 * @copyright 2017 Devtop
 */


namespace OBX\Core\Components;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Data\Cache;
use CBitrixComponent;
use CBitrixComponentTemplate;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Ajax
 * @package OBX\Core\Components
 * @property-read string $callId
 * @property-read string $name
 * @property-read string $template
 * @property-read bool $isAjaxHitNow
 * @property-read bool $useTildaKeys;
 * @property-read array $actualParams
 * @property-read array $params
 * @property array $additionalData
 */
class Ajax {
	private $_callId = null;
	private $_name = null;
	private $_template = null;
	private $_actualParams = null;
	private $_params = null;
	private $_additionalData = null;
	private $_useTildaKeys = false;
	private $_isAjaxHitNow = false;
	static private $cache = null;
	const CACHE_TTL = 3600;
	const CACHE_INIT_DIR = 'ajax_call';
	const AJAX_CALL_PARAMS_MARKER = 'COMPONENT_AJAX_CALL';

	private function __construct($component, $actualFields = null, $useTildaKeys = false, $fillDummyObjectByArray = false) {
		$this->_params = array();
		list($this->_name, $this->_template, $arParams) = self::getComponentData($component);
		$this->_useTildaKeys = !!$useTildaKeys;
		if( array_key_exists(self::AJAX_CALL_PARAMS_MARKER, $arParams)
			&& $arParams[self::AJAX_CALL_PARAMS_MARKER] == 'Y'
		) {
			$this->_isAjaxHitNow = true;
		}
		$arParams[self::AJAX_CALL_PARAMS_MARKER] = 'Y';
		$this->_params[self::AJAX_CALL_PARAMS_MARKER] = 'Y';

		if( $fillDummyObjectByArray && is_array($component) ) {
			if( empty($component['callId']) ) {
				//todo: throw
			}
			$this->_name = $component['name'];
			$this->_template = $component['template'];
			$this->_useTildaKeys = $component['useTildaKeys'];
			$this->_params = $arParams;
			$this->_actualParams = $component['actualParams'];
			$this->_callId = $component['callId'];
			if( array_key_exists('additionalData', $component) ) {
				$this->additionalData = $component['additionalData'];
			}
		}
		else {
			$actualParamsSerialized = '';
			$tildaPrefix = (true === $this->_useTildaKeys)?'~':'';
			if( empty($actualFields) || !is_array($actualFields) ) {
				$actualFields = array_keys($arParams);
			}
			foreach ($actualFields as &$actualFieldName) {
				if (substr($actualFieldName, 0, 1) === '~') {
					continue;
				}
				if (array_key_exists($tildaPrefix . $actualFieldName, $arParams)) {
					$actualFieldValue = $arParams[$tildaPrefix . $actualFieldName];
					$this->_actualParams[] = $actualFieldName;
					$this->_params[$actualFieldName] = $actualFieldValue;
					if (is_array($actualFieldValue)) {
						$actualFieldValue = implode(',', $actualFieldValue);
					}
					$actualParamsSerialized .= '[' . $actualFieldName . ': ' . $actualFieldValue . '];';
				}
			}
			$this->_callId = md5(SITE_ID.':'.$this->_name.':'.$this->_template.':'.$actualParamsSerialized);
		}
	}

	public function __get($name) {
		switch($name) {
			case 'callId': return $this->_callId;
			case 'name': return $this->_name;
			case 'template': return $this->_template;
			case 'isAjaxHitNow': return $this->_isAjaxHitNow;
			case 'useTildaKeys': return $this->_useTildaKeys;
			case 'params': return $this->_params;
			case 'actualParams': return $this->_actualParams;
			case 'additionalData': return $this->_additionalData;
			default:
				throw new ObjectPropertyException($name);
		}
	}

	public function __set($name, $value) {
		switch($name) {
			case 'additionalData':
				if( is_array($value) && !empty($value) ) {
					$this->_additionalData = $value;
				}
				break;
			default:
				throw new ObjectPropertyException($name);
		}
	}

	public function saveParams() {
		$cache = self::getCache();
		if( ! $this->isAjaxHitNow &&
			$cache->startDataCache(self::CACHE_TTL, $this->_callId, self::CACHE_INIT_DIR)
		) {
			/** @noinspection PhpParamsInspection */
			$cache->endDataCache([
				'callId' => $this->_callId,
				'name' => $this->_name,
				'template' => $this->_template,
				'useTildaKeys' => $this->_useTildaKeys,
				'actualParams' => $this->_actualParams,
				'params' => $this->_params,
				'additionalData' => $this->_additionalData
			]);
		}
	}

	static public function prepare($component, $actualFields = null, $useTildaKeys = false, $immadiateSaveToCache = false) {
		$ajax = new self($component, $actualFields, $useTildaKeys);
		if( true === $immadiateSaveToCache ) {
			$ajax->saveParams();
		}
		return $ajax;
	}

	static private function getCache() {
		if( null === self::$cache ) {
			self::$cache = Cache::createInstance();
		}
		return self::$cache;
	}

	static public function getByCallId($callId) {
		$cache = self::getCache();
		if( $cache->initCache(self::CACHE_TTL, $callId, self::CACHE_INIT_DIR) ) {
			$componentData = $cache->getVars();
			return new self(
				$componentData,
				$componentData['actualParams'],
				$componentData['useTildaKeys'],
				true
			);
		}
		return null;
	}

	static private function getComponentData($component) {
		$name = null;
		$template = null;
		$arParams = null;
		if($component instanceof CBitrixComponent) {
			$name = $component->getName();
			$template = $component->getTemplateName();
			$arParams = $component->arParams;
		}
		elseif($component instanceof CBitrixComponentTemplate) {
			$name = $component->__component->getName();
			$template = $component->__component->getTemplateName();
			$arParams = $component->__component->arParams;
		}
		elseif(is_array($component)) {
			if( empty($component['name']) ) {
				throw new ArgumentNullException(Loc::getMessage('OBX_CMP_AJAX_WRONG_ARG_CMP_NAME'));
			}
			if( empty($component['template']) ) {
				throw new ArgumentNullException(Loc::getMessage('OBX_CMP_AJAX_WRONG_ARG_CMP_TEMPLATE'));
			}
			if( !is_array($component['params']) ) {
				throw new ArgumentTypeException(Loc::getMessage('OBX_CMP_AJAX_WRONG_ARG_CMP_PARAMS_TYPE'));
			}
			if( empty($component['params']) ) {
				throw new ArgumentNullException(Loc::getMessage('OBX_CMP_AJAX_WRONG_CMP_PARAMS'));
			}
			$name = (string) $component['name'];
			$template = (string) $component['template'];
			$arParams = $component['params'];
		}
		else {
			throw new ArgumentNullException(Loc::getMessage('OBX_CMP_AJAX_WRONG_ARG_CMP'));
		}
		return [
			$name,
			$template,
			$arParams
		];
	}
}