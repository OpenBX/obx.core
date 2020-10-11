<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto pr0n1x@yandex.ru
 * @copyright 2017 Devtop
 */


namespace OBX\Core\Components;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Data\Cache;
use CBitrixComponent;
use CBitrixComponentTemplate;

Loc::loadLanguageFile(__FILE__);

/**
 * Class Ajax
 * @package OBX\Core\Components
 * @property-read string $callId - идентификатор ajax-вызова
 * @property-read string $name - имя компонента
 * @property-read string $template - шаблон компонента
 * @property-read bool $isAjaxHitNow - признак, того, что раотает ajax-взов (обращение к компоненту через ajax-обработчик)
 * @property-read bool $useTildaKeys - признак использования в качестве значений сохраняемых параметров компонента мырых данных (тильда-ключей) $arParams
 * @property-read array $actualParams - список кешируемых ключей $arParams
 * @property-read array $paramsKeys - список кешируемых ключей $arParams (синоним $actualParams)
 * @property-read array $params - значения параметров компонента
 * @property-read bool $useStrictCacheControl - режим контроля консистентности кеша параметров компонента. по умолчанию: true
 * @property array $additionalData - дополнительные кешируемые данные типа фильтров для bitrix:news.list или bitrix:catalog.section
 * @property array $globalsExport - массив ключей в $additionalParams, которые будут экспортированы в $GLOBALS в ajax-обработчике
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
	 * Потому сохраняем доп. значения в это поле (в кеш),
	 * и вытаскиваем из кеша во время ajax-вызова.
	 */
	private $_additionalData = null;

	/**
	 * @var array|null - ключи $additionalData, которые будут экспортированы
	 * в $GLOBALS в ajax-обработчике. Формат: ['KEY_IN_$GLOBALS' => 'KEY_IN_$additionalData']
	 */
	private $_globalsExport = null;

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

	private $_cacheDir = null;

	/** @var \Bitrix\Main\Data\Cache|null - объект менеджера кеширования  */
	static private $cache = null;

	/**
	 * @var bool - контроль чтения поля $callId.cn
	 * Если программист добавит значения
	 * в additionalData, то callId примет новое значение - это повлечет перегенерацию callId.
	 * Если при этом программист уже получил значение callId,
	 * то велика вероятность, что он забудет получить новое значение callId.
	 * Потому мы через контроль чтения callId запретим ему получить callId раньше чем
	 * он изменить additionalData. Бросим ему исключение при попытке задать additionalData
	 * после того как callId был прочитан.
	 */
	private $_callIdAlreadyRead = false;

	/**
	 * @var bool - контроль за сохранением по аналогии с $_callIdAlreadyRead
	 * Необхоидмо запретить программисту задавать additionalData и globalsExport после того, как он
	 * сохранил данные в кеш. Иначе программист рано или поздно забудет пересохранить
	 * параметры которые задаст в additionalData и/или в globalsExport
	 */
	private $_paramsAlreadySaved = false;

	/**
	 * @var bool Включет проверку целостности
	 * описанную в доке для переменных
	 * $_callIdAlreadyRead, $_paramsAlreadySaved и $_paramsAlreadyRead
	 */
	private $_useStrictCacheControl = true;

	/**
	 * @var bool - контроль чтения поля $params по аналогии с $_callIdAlreadyRead
	 * Необхоидмо запретить программисту задавать additionalData после того, как он
	 * прочитал $params, поскольку после того как программист задаст значние $additionalData
	 * в параметрах появится как минимум ещё один ключ,
	 * и полученные им ранее массив параметров будет отличаться от фактического занчения $params
	 */
	private $_paramsAlreadyRead = false;

	const CACHE_AUTO_TTL = 36000000;
	const CACHE_SESSION_TTL = 300;
	const CACHE_INIT_DIR = 'ajax_call';

	/**
	 * @const Имя ключа в массиве (например $arResult или $_REQUEST),
	 * который содержит значение $callId
	 */
	const MARKER_CALL_ID = 'AJAX_CALL_ID';

	/**
	 * @const Имя ключа в массиве (например $arResult),
	 * который содержит признак того,
	 * что вызов осуществляется по ajax (isAjaxHitNow)
	 */
	const MARKER_CALL_IS_AJAX = 'COMPONENT_CALL_IS_AJAX';

	/**
	 * @const Имя ключа в массиве $arParams,
	 * который содержит контрольную сумму данных $additionalData
	 */
	const MARKER_ADDITIONAL_STAMP = 'AJAX_ADDITIONAL_DATA_STAMP';

	/**
	 * @const - то же что Ajax::MARKER_CALL_IS_AJAX - устаревшее.
	 * @deprecated
	 */
	const AJAX_CALL_PARAMS_MARKER = self::MARKER_CALL_IS_AJAX;

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
		$this->_useTildaKeys = (bool) $useTildaKeys;
		$this->_params = array();
		list($this->_component,
			$this->_name,
			$this->_template,
			$arParams,
			$this->_cacheType,
			$this->_cacheTime
			) = self::getComponentData($component);
		if( array_key_exists(self::MARKER_CALL_IS_AJAX, $arParams)
			&& $arParams[self::MARKER_CALL_IS_AJAX] == 'Y'
		) {
			$this->_isAjaxHitNow = true;
		}
		$arParams[self::MARKER_CALL_IS_AJAX] = 'Y';
		$this->_params[self::MARKER_CALL_IS_AJAX] = 'Y';

		if( array_key_exists(self::MARKER_ADDITIONAL_STAMP, $arParams) ) {
			// Это не обязательно, но так первоначальный callId в ajax-хите будет
			// таким же как в НЕ-ajax-овом хите перед заполнением additionalData и globalsExport
			unset($arParams[self::MARKER_ADDITIONAL_STAMP]);
		}

		if( $fillDummyObjectByArray && is_array($component) ) {
			if( empty($component['callId']) ) {
				//todo: throw
			}
			$this->_name = $component['name'];
			$this->_template = $component['template'];
			$this->_useTildaKeys = $component['useTildaKeys'];
			$this->_params = $arParams;
			$this->_actualParams = array_keys($component['params']);
			$this->_callId = $component['callId'];
			if( array_key_exists('additionalData', $component) ) {
				$this->_additionalData = $component['additionalData'];
			}
			if( array_key_exists('globalsExport', $component) ) {
				$this->_globalsExport = $component['globalsExport'];
			}
		}
		else {
			$this->makeCallId($arParams, $this->_useTildaKeys);
		}
		self::clearSavedOutdatedSessionParams();
	}

	protected function makeCallId($arParams, $useTildaKeys) {
		$actualParamsSerialized = '';
		$useTildaKeys = (bool) $useTildaKeys;
		$tildaPrefix = (true === $useTildaKeys)?'~':'';

		$this->_callId = null;
		$this->_actualParams = [];
		$this->_params = [];

		if( !empty($this->_additionalData) ) {
			$serializedAdditionalData = empty($this->_additionalData)?'':serialize($this->_additionalData);
			$serializedGlobalsExport = empty($this->_globalsExport)?'':serialize($this->_globalsExport);
			$arParams[self::MARKER_ADDITIONAL_STAMP] = md5($serializedAdditionalData.$serializedGlobalsExport);
			if( true === $useTildaKeys ) {
				$arParams[$tildaPrefix.self::MARKER_ADDITIONAL_STAMP] = $arParams[self::MARKER_ADDITIONAL_STAMP];
			}
		}
		$actualParams = array_keys($arParams);

		foreach ($actualParams as &$actualParamName) {
			if ( '~' === substr($actualParamName, 0, 1)
				|| self::MARKER_CALL_IS_AJAX === $actualParamName
			) {
				continue;
			}
			if (array_key_exists($tildaPrefix . $actualParamName, $arParams)) {
				$actualFieldValue = $arParams[$tildaPrefix . $actualParamName];
				$this->_actualParams[] = $actualParamName;
				$this->_params[$actualParamName] = $actualFieldValue;
				if (is_array($actualFieldValue)) {
					$actualFieldValue = implode(',', $actualFieldValue);
				}
				$actualParamsSerialized .= '[' . $actualParamName . ': ' . $actualFieldValue . '];';
			}
		}
		$this->_callId = md5(SITE_ID.':'.$this->_name.':'.$this->_template.':'.$actualParamsSerialized);
	}

	public function __get($name) {
		switch($name) {
			case 'callId':
				$this->_callIdAlreadyRead = true;
				return $this->_callId;
			case '_debug_callId': return $this->_callId;
			case 'name': return $this->_name;
			case 'template': return $this->_template;
			case 'isAjaxHitNow': return $this->_isAjaxHitNow;
			case 'useTildaKeys': return $this->_useTildaKeys;
			case 'params':
				$this->_paramsAlreadyRead = true;
				return $this->_params;
			case '_debug_params': return $this->_params;
			case 'cacheTime': return $this->_cacheTime;
			case 'cacheDir': return $this->_cacheDir;
			case 'paramsKeys': return $this->_actualParams;
			case 'actualParams': return $this->_actualParams;
			case 'additionalData': return $this->_additionalData;
			case 'globalsExport': return $this->_globalsExport;
			case 'useStrictCacheControl': return $this->_useStrictCacheControl;
			default:
				throw new ObjectPropertyException($name);
		}
	}

	public function __set($name, $value) {
		switch($name) {
			case 'additionalData':
				$this->_setAdditionalData($value);
				break;
			case 'globalsExport':
				$this->_setGlobalsExport($value);
				break;
			case 'useStrictCacheControl':
				$this->_useStrictCacheControl = (bool) $value;
				break;
			default:
				throw new ObjectPropertyException($name);
		}
	}

	private function _throwOnModifyAdditionalData() {
		if( $this->_useStrictCacheControl ) {
			if( $this->_callIdAlreadyRead ) {
				throw new NotSupportedException(
					'Modifying of $additionalData is denied after $callId has been read.'
					.PHP_EOL.'You can set $useStrictCacheControl field to false. But do it responsibly.'
				);
			}
			if( $this->_paramsAlreadyRead ) {
				throw new NotSupportedException(
					'Modifying of $additionalData is denied after $params ($arParams) has been read.'
					.PHP_EOL.'You can set $useStrictCacheControl field to false. But do it responsibly.'
				);
			}
			if( $this->_paramsAlreadySaved ) {
				throw new NotSupportedException(
					'Modifying of $additionalData is denied after $params ($arParams) has been saved.'
					.PHP_EOL.'You can set $useStrictCacheControl field to false. But do it responsibly.'
				);
			}
		}
	}

	private function _setAdditionalData(&$value) {
		if( empty($this->_additionalData) && (!is_array($value) || empty($value)) ) {
			$this->_additionalData = null;
			return false;
		}
		$this->_throwOnModifyAdditionalData();
		$this->_additionalData = $value;
		$this->makeCallId($this->_params, false);
		return true;
	}

	private function _setGlobalsExport(&$value) {
		if( empty($this->_globalsExport) && (!is_array($value) || empty($value)) ) {
			$this->_globalsExport = null;
			return false;
		}
		$this->_throwOnModifyAdditionalData();
		if( !is_array($value) || empty($value) ) {
			$this->makeCallId($this->_params, false);
			$this->_globalsExport = null;
			return true;
		}
		$this->_globalsExport = [];
		foreach($value as $globalKey => $additionalDataKey) {
			$additionalDataKey = trim($additionalDataKey);
			if( empty($additionalDataKey) ) {
				continue;
			}
			if( !array_key_exists($additionalDataKey, $this->_additionalData) ) {
				continue;
			}
			if( is_numeric($globalKey) ) {
				throw new NotSupportedException('Incorrect format of $globalsExport.'
					.PHP_EOL.'You should to follow this [\'KEY_IN_$GLOBALS\' => \'KEY_IN_$additionalData\']'
					.PHP_EOL.'Numeric keys does not support.'
				);
			}
			//todo: добавить валидацию имени переменной, хранимой в ключе
			$this->_globalsExport[$globalKey] = $additionalDataKey;
		}
		if( empty($this->_globalsExport) ) {
			$this->_globalsExport = null;
		}
		$this->makeCallId($this->_params, false);
		return true;
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
			'params' => $this->_params,
			'additionalData' => $this->_additionalData,
			'globalsExport' => $this->_globalsExport
		];
		if( ! $this->_isAjaxHitNow && $this->_cacheTime > 0 ) {
			if( !$cache->startDataCache($this->_cacheTime+60, $this->_callId, self::CACHE_INIT_DIR) ) {
				//todo: throw
			}
			/** @noinspection PhpParamsInspection */
			$cache->endDataCache($cachedParams);
		}
		if( ! $this->_isAjaxHitNow
			&& isset($_REQUEST['clear_cache'])
			&& 'Y' === $_REQUEST['clear_cache']
		) {
			self::saveParamsToSession($cachedParams);
		}
		$this->_paramsAlreadySaved = true;
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
				array_keys($componentData['params']),
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

		$bitrixComponent = (($component instanceof CBitrixComponent)
			? $component
			: (($component instanceof CBitrixComponentTemplate)
				? $component->__component
				: null
			)
		);
		if( null !== $bitrixComponent ) {
			$name = $bitrixComponent->getName();
			$template = $bitrixComponent->getTemplateName();
			$arParams = $bitrixComponent->arParams;
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
		// restore bool values to string markers
		foreach($arParams as $paramName => &$paramValue) {
			if (is_scalar($paramValue)) {
				$paramValue = is_bool($paramValue)
					? ($paramValue ? 'Y' : 'N')
					: ((string) $paramValue);
			}
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

	public function exportGlobals() {
		foreach($this->_globalsExport as $globalKey => $additionalDataKey) {
			if( array_key_exists($additionalDataKey, $this->_additionalData) ) {
				$GLOBALS[$globalKey] = $this->_additionalData[$additionalDataKey];
			}
		}
	}

	static public function includeAjaxComponent() {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		if( empty($_REQUEST[Ajax::MARKER_CALL_ID]) ) {
			ShowError('Не передан идентификатор ajax-вызова компонента ['.Ajax::MARKER_CALL_ID.']');
			return null;
		}
		$ajaxComponent = Ajax::getByCallId($_REQUEST[Ajax::MARKER_CALL_ID]);
		if( !($ajaxComponent instanceof Ajax) ) {
			ShowError('Данные ajax-вызова компонента не найдены');
			return null;
		}

		$ajaxComponent->updateSessionParamsTimeout();
		$ajaxComponent->exportGlobals();
		$componentReturnValue = $APPLICATION->IncludeComponent(
			$ajaxComponent->name,
			$ajaxComponent->template,
			$ajaxComponent->params,
			false, ['HIDE_ICONS' => 'Y']
		);
		return $componentReturnValue;
	}
}
