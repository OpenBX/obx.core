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
 */
class Ajax {
	private $_callId = null;
	private $_name = null;
	private $_template = null;
	private $_actualParams = null;
	private $_params = null;
	private $_useTildaKeys = false;
	private $_isAjaxHitNow = false;
	static private $cache = null;
	const CACHE_TTL = 3600;
	const CACHE_INIT_DIR = 'ajax_call';
	const AJAX_CALL_PARAMS_MARKER = 'COMPONENT_AJAX_CALL';

	private function __construct($component, $actualFields = null, $useTildaKeys = false) {
		list($this->_name, $this->_template, $this->_params) = self::getComponentData($component);
		$this->_useTildaKeys = !!$useTildaKeys;
		if( array_key_exists(self::AJAX_CALL_PARAMS_MARKER, $this->_params)
			&& $this->_params[self::AJAX_CALL_PARAMS_MARKER] == 'Y'
		) {
			$this->_isAjaxHitNow = true;
		}
		else {
			$this->_params[self::AJAX_CALL_PARAMS_MARKER] = 'Y';
		}

		$actualParamsSerialized = '';
		if( !empty($actualFields) && is_array($actualFields) ) {
			foreach($actualFields as $actualFieldName) {
				if( substr($actualFieldName, 0, 1) === '~' ) {
					continue;
				}
				if(true === $this->_useTildaKeys) {
					$actualFieldName = '~'.$actualFieldName;
				}
				if( array_key_exists($actualFieldName, $this->_params) ) {
					$this->_actualParams[] = $actualFieldName;
					$actualFieldValue = $this->_params[$actualFieldName];
					if( is_array($actualFieldValue) ) {
						$actualFieldValue = implode(',', $actualFieldValue);
					}
					$actualParamsSerialized .= '['.$actualFieldName.': '.$actualFieldValue.'];';
				}
			}
		}
		$this->_callId = md5(SITE_ID.':'.$this->_name.':'.$this->_template.':'.$actualParamsSerialized);
	}

	public function __get($name) {
		switch($name) {
			case 'callId': return $this->_callId;
			case 'name': return $this->_name;
			case 'template': return $this->_template;
			case 'isAjaxHitNow': return $this->_isAjaxHitNow;
			case 'useTildaKeys': return $this->_useTildaKeys;
			case 'params':
				$sourceParams = [];
				foreach($this->_params as $paramName => &$paramValue) {
					$hasTilda = (substr($paramName, 0, 1) === '~');
					if($this->_useTildaKeys === $hasTilda) {
						$sourceParams[$paramName] = $paramValue;
					}
				}
				return $sourceParams;
			case 'actualParams': return $this->_actualParams;
			default:
				throw new ObjectPropertyException($name);
		}
	}

	static public function prepare($component, $actualFields = null, $useTildaKeys = false) {
		$cache = self::getCache();
		$ajax = new self($component, $actualFields, $useTildaKeys);
		$d=1;
		if( ! $ajax->isAjaxHitNow &&
			$cache->startDataCache(self::CACHE_TTL, $ajax->_callId, self::CACHE_INIT_DIR)
		) {
			/** @noinspection PhpParamsInspection */
			$cache->endDataCache([
				'callId' => $ajax->_callId,
				'name' => $ajax->_name,
				'template' => $ajax->_template,
				'useTildaKeys' => $ajax->_useTildaKeys,
				'actualParams' => $ajax->_actualParams,
				'params' => $ajax->_params
			]);
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
				$componentData['useTildaKeys']
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