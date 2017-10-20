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
	private $_cacheType = 'A';
	private $_cacheTime = 3600;
	static private $cache = null;
	const CACHE_AUTO_TTL = 36000000;
	const CACHE_SESSION_TTL = 300;
	const CACHE_INIT_DIR = 'ajax_call';
	const AJAX_CALL_PARAMS_MARKER = 'COMPONENT_AJAX_CALL';

	private function __construct($component, $actualFields = null, $useTildaKeys = false, $fillDummyObjectByArray = false) {
		$this->_params = array();
		list($this->_name,
			$this->_template,
			$arParams,
			$this->_cacheType,
			$this->_cacheTime
			) = self::getComponentData($component);
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
		self::clearSavedOutdatedSessionParams();
	}

	public function __get($name) {
		switch($name) {
			case 'callId': return $this->_callId;
			case 'name': return $this->_name;
			case 'template': return $this->_template;
			case 'isAjaxHitNow': return $this->_isAjaxHitNow;
			case 'useTildaKeys': return $this->_useTildaKeys;
			case 'params': return $this->_params;
			case 'cacheTime': return $this->_cacheTime;
			case 'cacheDir': return $this->_cacheDir;
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
		$cachedParams = [
			'callId' => $this->_callId,
			'name' => $this->_name,
			'template' => $this->_template,
			'useTildaKeys' => $this->_useTildaKeys,
			'actualParams' => $this->_actualParams,
			'params' => $this->_params,
			'additionalData' => $this->_additionalData
		];
		if( ! $this->isAjaxHitNow &&
			$this->_cacheTime > 0 &&
			$cache->startDataCache($this->_cacheTime+60, $this->_callId, self::CACHE_INIT_DIR)
		) {
			/** @noinspection PhpParamsInspection */
			$cache->endDataCache($cachedParams);
		}
		if( ! $this->isAjaxHitNow
			&& isset($_REQUEST['clear_cache'])
			&& 'Y' === $_REQUEST['clear_cache']
		) {
			self::saveParamsToSession($cachedParams);
		}
	}

	static private function saveParamsToSession(&$cachedParams) {
		if( !isset($_SESSION['OBX_AJAX_CALL_PARAMS'])
			|| !is_array($_SESSION['OBX_AJAX_CALL_PARAMS']) ) {
			$_SESSION['OBX_AJAX_CALL_PARAMS'] = [];
		}
		$_SESSION['OBX_AJAX_CALL_PARAMS'][$cachedParams['callId']] = $cachedParams;
		$_SESSION['OBX_AJAX_CALL_PARAMS'][$cachedParams['callId']]['timeout'] = time()+self::CACHE_SESSION_TTL;
	}

	static public function _updateSessionParamsTimeout($callId) {
		if( !isset($_SESSION['OBX_AJAX_CALL_PARAMS'])
			|| !is_array($_SESSION['OBX_AJAX_CALL_PARAMS'])
			|| empty($_SESSION['OBX_AJAX_CALL_PARAMS'][$callId]['timeout'])
		) {
			return false;
		}
		$_SESSION['OBX_AJAX_CALL_PARAMS'][$callId]['timeout'] = time()+self::CACHE_SESSION_TTL;
		return true;
	}

	public function updateSessionParamsTimeout() {
		self::_updateSessionParamsTimeout($this->_callId);
	}

	static public function clearSavedOutdatedSessionParams() {
		if( isset($_SESSION['OBX_AJAX_CALL_PARAMS'])
			&& is_array($_SESSION['OBX_AJAX_CALL_PARAMS']) ) {
			foreach($_SESSION['OBX_AJAX_CALL_PARAMS'] as $callId => $cachedParams) {
				if( time() >= intval($cachedParams['timeout'])) {
					unset($_SESSION['OBX_AJAX_CALL_PARAMS'][$callId]);
				}
			}
		}
	}

	static public function prepare($component, $actualFields = null, $useTildaKeys = false, $immediateSaveToCache = false) {
		$ajax = new self($component, $actualFields, $useTildaKeys);
		if( true === $immediateSaveToCache ) {
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
		$callId = trim($callId);
		if( empty($callId) ) return null;
		$cache = self::getCache();
		$componentData = null;
		if( $cache->initCache(self::CACHE_AUTO_TTL, $callId, self::CACHE_INIT_DIR) ) {
			$componentData = $cache->getVars();
		}
		elseif( array_key_exists($callId, $_SESSION['OBX_AJAX_CALL_PARAMS'])
			&& !empty($_SESSION['OBX_AJAX_CALL_PARAMS'][$callId])
		) {
			$componentData = $_SESSION['OBX_AJAX_CALL_PARAMS'][$callId];
		}
		if( !empty($componentData) ) {
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
		$cacheType = null;
		$cacheTime = null;

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
		$cacheTime = intval($arParams['CACHE_TIME']);
		$cacheType = isset($arParams['CACHE_TYPE'])?$arParams['CACHE_TYPE']:'A';
		if( $cacheTime <= 0 && 'A' === $cacheType ) {
			$cacheTime = self::CACHE_AUTO_TTL;
		}
		return [
			$name,
			$template,
			$arParams,
			$cacheType,
			$cacheTime
		];
	}
}