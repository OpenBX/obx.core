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
	/** @var \CBitrixComponent|null - Объект вызываемого компонента */
	private $_component = null;

	/** @var string|null - идентификатор ajax-вызова */
	private $_callId = null;

	/** @var string|null - имя компонента */
	private $_name = null;

	/** @var string|null - шаблон компонента */
	private $_template = null;

	/** @var array|null - список значимых (сохраняемых) ключей $arParams */
	private $_actualParams = null;

	/** @var array|null - значения значимых параметров $arParams */
	private $_params = null;

	/**
	 * @var array|null - дополнительные данные
	 * Например фактическое содержимое фильтра, передаваемого в глобальной переменной,
	 * имя которой передано в параметрах. Нам необходимо сохранить в кеш ajax-вызова
	 * не только параметры, но и само содержимое фильра, поскольку внутри ajax-вызова
	 * интересующего нас занчения в GLOBALS уже не будет (хит новый же...).
	 * Потому сохраняем в это поле, сохраняем в кеш и вытаскиваем из кеша
	 * во время ajax-вызова.
	 */
	private $_additionalData = null;

	/**
	 * @var bool - признак использования сырых значений arParams полученных в шаблоне компонента.
	 * Треюуется это, потому что многие компоненты модифицируют переменную $arParams,
	 * что не позволяет внутри шаблона компонента получить те знаения $arParams,
	 * которые были исползованы в коде вызова компонента.
	 * Решается банальным получением значений из параметров с префиксом "~",
	 * В методах-наследниках \CBitrixComponent::onPrepareComponentParams()
	 * параметры с тильдой не принято модифицировать - это противоречит
	 * назначению параметров с тильдой.
	 */
	private $_useTildaKeys = false;

	/** @var bool - признак того, что сейчас осуществляется ajax-вызов */
	private $_isAjaxHitNow = false;

	/** @var string - тип кеша */
	private $_cacheType = 'A';

	/** @var int - Вермя кеширования (сек.) */
	private $_cacheTime = 3600;

	/** @var \Bitrix\Main\Data\Cache|null - объект менеджера кеширования  */
	static private $cache = null;

	const CACHE_AUTO_TTL = 36000000;
	const CACHE_SESSION_TTL = 300;
	const CACHE_INIT_DIR = 'ajax_call';
	const AJAX_CALL_PARAMS_MARKER = 'COMPONENT_AJAX_CALL';
	const ADDITIONAL_DATA_MARKER = 'COMPONENT_AJAX_ADDITIONAL_STAMP';

	/**
	 * @param \CBitrixComponentTemplate|\CBitrixComponent|array $component -
	 * 			объект шаблона компонента или самого компонента
	 * или массив описывающий компонент
	 * @param null $actualFields - сохраняемые (пробрасываемые) колючи arParams.
	 * 			Значение null эквивалентно array_keys($arParams) - т.е.
	 * 			получает все значения
	 * @param bool $useTildaKeys - в качестве занчений параметров компонента в ajax-вызове
	 * 			использовать первичные (сырые) значений arParams, ключи которых
	 * 			имеют вид ~PARAM_NAME
	 * @param bool $fillDummyObjectByArray - служебный параметр. Создать объек не из обхекта
	 * 			\CBitrixComponentTemplate или \CBitrixComponent,
	 * 			а из простого массива определенноый структуры
	 */
	private function __construct($component, $actualFields = null, $useTildaKeys = false, $fillDummyObjectByArray = false) {
		$this->_params = array();
		list($this->_component,
			$this->_name,
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
			$this->makeCallId($arParams);
		}
		self::clearSavedOutdatedSessionParams();
	}

	protected function makeCallId($arParams) {
		$actualParamsSerialized = '';
		$tildaPrefix = (true === $this->_useTildaKeys)?'~':'';
		if( empty($actualFields) || !is_array($actualFields) ) {
			$actualFields = array_keys($arParams);
		}
		$this->_callId = null;
		$this->_actualParams = [];
		$this->_params = [];
		foreach ($actualFields as &$actualFieldName) {
			if ( '~' === substr($actualFieldName, 0, 1)
				|| self::AJAX_CALL_PARAMS_MARKER === $actualFieldName
			) {
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

	/**
	 * Сохранить параметры компонента в кеш (или в сессию, в случае использования clear_cache=Y)
	 */
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

	/**
	 * Сохранить значения параметров компонентов в сессии
	 * @param $cachedParams
	 */
	static private function saveParamsToSession(&$cachedParams) {
		if( !isset($_SESSION['OBX_AJAX_CALL_PARAMS'])
			|| !is_array($_SESSION['OBX_AJAX_CALL_PARAMS']) ) {
			$_SESSION['OBX_AJAX_CALL_PARAMS'] = [];
		}
		$_SESSION['OBX_AJAX_CALL_PARAMS'][$cachedParams['callId']] = $cachedParams;
		$_SESSION['OBX_AJAX_CALL_PARAMS'][$cachedParams['callId']]['timeout'] = time()+self::CACHE_SESSION_TTL;
	}

	/**
	 * Обновить значение таймаута хранения значений параметров сохраненных в сессии
	 * по идентификатору ajax-вызова компонента
	 * @param $callId
	 * @return bool
	 */
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

	/**
	 * Обновить значение таймаута хранения значений параметров сохраненных в сессии
	 * текущего объекта
	 */
	public function updateSessionParamsTimeout() {
		self::_updateSessionParamsTimeout($this->_callId);
	}

	/**
	 * Очистить все устаревшие сохраненные значения кеша параметров ajax-компонентов.
	 * Подобная очистка проиводится при каждом обращении к ajax-компоненту, но есть вероятность,
	 * что пользователь не зайдет в обозримом периоде в раздел, в котором применяется ajax-вызовы компонента,
	 * а значит значения могу зависнуть в сессии на долго. Потому можно переодически подчищать их этим методом
	 */
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

	/**
	 * @param $component
	 * @param null $actualFields - сохраняемые (пробрасываемые) колючи arParams
	 * @param bool $useTildaKeys - в качестве занчений параметров компонента в ajax-вызове
	 * 			использовать первичные (сырые) значений arParams, ключи которых
	 * 			имеют вид ~PARAM_NAME
	 * @param bool $immediateSaveToCache - сразу сохранить значения параметров компонента,
	 * 			без явного вызова метода saveParams().
	 * 			Нельзя использовать если применяется поле additionalData
	 * @return Ajax
	 */
	static public function prepare($component, $actualFields = null, $useTildaKeys = false, $immediateSaveToCache = false) {
		$ajax = new self($component, $actualFields, $useTildaKeys);
		if( true === $immediateSaveToCache ) {
			$ajax->saveParams();
		}
		return $ajax;
	}

	/**
	 * Возвращает объект менеджера кеша
	 * @return Cache|null
	 */
	static private function getCache() {
		if( null === self::$cache ) {
			self::$cache = Cache::createInstance();
		}
		return self::$cache;
	}

	/**
	 * Получить массив параметров компонента из кеша
	 * по идентификатору ajax-вызов (callId)
	 * @param $callId
	 * @return null|Ajax
	 */
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

	/**
	 * Получить данные компонента по объекту
	 * \CBitrixComponentTemplate или \CBitrixComponent
	 * или из простого массива определенного формата
	 * @param $component
	 * @return array
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	static private function getComponentData($component) {
		$bitrixComponent = null;
		$name = null;
		$template = null;
		$arParams = null;
		$cacheType = null;
		$cacheTime = null;

		if($component instanceof CBitrixComponent) {
			$bitrixComponent = $component;
			$name = $component->getName();
			$template = $component->getTemplateName();
			$arParams = $component->arParams;
		}
		elseif($component instanceof CBitrixComponentTemplate) {
			$bitrixComponent = $component->__component;
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
			$bitrixComponent,
			$name,
			$template,
			$arParams,
			$cacheType,
			$cacheTime
		];
	}
}