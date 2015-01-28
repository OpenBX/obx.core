<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\DBEntityEditor;

use OBX\Core\Exceptions\DBEntityEditor\ConfigError as Err;
use OBX\Core\MessagePool;
use OBX\Core\Tools;

IncludeModuleLangFile(__FILE__);

/**
 * Class Config
 * @package OBX\Core\DBEntityEditor
 *
 * В этот класс так же стоит добавить разума
 * => необходимы умные проверки связей, короые покажут что
 *    например если мы из сущности Страна делаем ссылку поле сущности Город,
 *    то скорее всего в рамках сущности Страна нуеобходимо сгруппировать выдачу по
 *    идентификатору Страны, а данные таблицы сущгности Город использовать для
 *    агрегатных ф-ий типа count(), group_concat() и др.
 *    Данный класс должен уведомлять программиста о подобных ситуациях и предлагать соотв. варианты.
 *
 * => Из предыдущего пункта следует, что конфиг должен полнимать когда связь
 *    "один ко многим" относительно него самого, а когда "многие к одному".
 *    Город отностительно Страны сявзан "многие к одному"
 *    Страна отностельно города "один ко многим"
 *
 * => Связь поля с primary_key с другим полем - это явно связь 1-M
 *    так же связь 1-М когда связь идет по полю входящему в уникальный индекс состоящий из одного поля.
 *    ...типичный пример
 *    CODE varchar(255) not null,
 *    unique(CODE)
 *    ...
 */

class Config implements IConfig
{
	protected $moduleID = null;
	protected $eventsID = null;

	protected $MessagePool = null;

	protected $configPath = null;
	protected $namespace = null;
	protected $class = null;
	protected $classPath = null;
	protected $version = null;
	protected $langPrefix = null;
	protected $title = null;
	protected $description = null;
	protected $errorNothingToUpdate = null;
	protected $errorNothingToDelete = null;
	protected $tableName = null;
	protected $tableAlias = null;
	protected $fields = array();
	protected $unique = array();
	protected $index = array();
	protected $reference = array();
	protected $defaultSort = array();
	protected $defaultGroupBy = array();
	protected $parentRefConfig = null;
	protected $readSuccess = false;

	/**
	 * @param string $entityConfigFile
	 * @param self $referencedConfig
	 * @throws Err
	 */
	public function __construct($entityConfigFile, self $referencedConfig = null) {
		$this->parentRefConfig = $referencedConfig;
		$this->MessagePool = new MessagePool();
		if( !is_file(OBX_DOC_ROOT.$entityConfigFile) ) {
			throw new Err('', Err::E_OPEN_CFG_FAILED);
		}
		$this->configPath = self::normalizePath($entityConfigFile);
		$jsonConfig = file_get_contents(OBX_DOC_ROOT.$this->configPath);
		$configData = json_decode($jsonConfig, true);
		if(null === $configData) {
			throw new Err(
				array('JSON_ERROR' => Tools::getJsonErrorMsg()),
				Err::E_PARSE_CFG_FAILED
			);
		}
		$this->initialEntityData($configData);
		$this->initVersion($configData);
		$this->initEntityClass($configData);
		$this->initTableName($configData);
		$this->initLangData($configData);
		$this->initFields($configData);
		$this->initReferences($configData);
		$this->initIndex($configData);
		$this->initUnique($configData);
		$this->initDefaultSort($configData);
		$this->initDefaultGroupBy($configData);
		// Ставим метку завершения чтения, на тот случай если кто-то напишет такой код,
		// в котором объект будет доступен для работы уже после выброса исключения
		$this->readSuccess = true;
	}

	protected function initialEntityData(&$configData) {
		if( empty($configData['module'])
			&& !is_dir(OBX_DOC_ROOT.$configData['module'])
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/include.php')
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/install/index.php')
		) {
			throw new Err('', Err::E_CFG_NO_MOD);
		}
		$this->moduleID = $configData['module'];
		if( empty($configData['events_id']) ) {
			throw new Err('', Err::E_CFG_NO_EVT_ID);
		}
		$this->eventsID = $configData['events_id'];
	}

	protected function initVersion(&$configData) {
		//$this->_version = \CUpdateClient::GetModuleVersion($this->_entityModuleID);
		if(!empty($configData['version']) && strpos($configData['version'], '.') !== false) {
			if( null !== ($arVersion = Tools::parseVersion($configData['version'])) ) {
				$this->version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
			}
		}
		if(empty($this->version)) {
			$modulePath = OBX_DOC_ROOT.'/bitrix/modules/'.$this->moduleID;
			if(is_file($modulePath.'/install/version.php')) {
				/** @noinspection PhpIncludeInspection */
				$returnVersionArray = include $modulePath.'/install/version.php';
				if(!empty($arModuleVersion['VERSION'])
					&& null !== ($arVersion=Tools::parseVersion($arModuleVersion['VERSION']))
				) {
					$this->version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
				}
				elseif(!empty($returnVersionArray['VERSION'])
					&& null !== ($arVersion=Tools::parseVersion($returnVersionArray['VERSION']))
				) {
					$this->version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
				}
				else {
					$moduleClass = str_replace('.', '_', $this->moduleID);
					if(self::validateClassName($moduleClass) ) {
						if( !class_exists($moduleClass) ) {
							/** @noinspection PhpIncludeInspection */
							require_once OBX_DOC_ROOT.$modulePath.'/install/index.php';
						}
						if( class_exists($moduleClass) ) {
							$moduleInstaller = new $moduleClass;
							if( null !== ($arVersion = Tools::parseVersion($moduleInstaller->VERSION)) ) {
								$this->version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
							}
						}
					}
				}
			}
			if(empty($this->version)) {
				throw new Err('', Err::E_VERSION_IS_EMPTY);
			}
		}
	}

	protected function initEntityClass(&$configData) {
		$configData['namespace'] = ''.trim($configData['namespace'], ' \\');
		//$configData['namespace'] = str_replace('\\\\', '\\', $configData['namespace']);
		if( !self::validateNamespace($configData['namespace']) ) {
			throw new Err('', Err::E_CFG_WRG_NAMESPACE);
		}
		$this->namespace = $configData['namespace'];

		$configData['class'] = ''.trim($configData['class'], ' \\');
		if( !self::validateClassName($configData['class']) ) {
			throw new Err('', Err::E_CFG_WRG_CLASS_NAME);
		}
		$this->class = $configData['class'];

		$this->classPath = 'lib/'
			.strtolower(str_replace('\\', '/', $this->namespace))
			.'/'.$this->class.'.php'
		;
		if( !empty($configData['class_path']) ) {
			//throw new Err('', Err::E_CFG_NO_CLASS_PATH);
			$this->classPath = self::normalizePath($configData['class_path']);
		}
		if(null !== $this->parentRefConfig) {
			$refClass = $this->parentRefConfig->getNamespace().'\\'.$this->parentRefConfig->getClass();
			if($refClass == $this->namespace.'\\'.$this->class) {
				throw new Err('', Err::E_CFG_REF_ENTITY_SAME_CLASS);
			}
		}
	}

	protected function initTableName(&$configData) {
		$configData['table_alias'] = trim($configData['table_alias']);
		$configData['table_name'] = trim($configData['table_name']);
		if( !self::validateTblAlias($configData['table_alias']) ) {
			throw new Err('', Err::E_CFG_TBL_WRG_ALIAS);
		}
		if( !self::validateTblName($configData['table_name']) ) {
			throw new Err('', Err::E_CFG_TBL_WRG_NAME);
		}
		$this->tableName = $configData['table_name'];
		$this->tableAlias = $configData['table_alias'];
		$this->langPrefix = str_replace('\\', '_', strtoupper($this->namespace.'\\'.$this->class));
		if(!empty($configData['lang_prefix'])) {
			$configData['lang_prefix'] = strtoupper(trim($configData['lang_prefix']));
			if(preg_match('~[A-Z0-9\\_\\-/\\|]~', $configData['lang_prefix'])) {
				$this->langPrefix = $configData['lang_prefix'];
			}
		}
	}

	protected function initLangData(&$configData) {
		$this->title = array(
			'lang' => '%_ENTITY_TITLE',
			'ru' => $this->langPrefix.'_ENTITY_TITLE',
			'en' => $this->langPrefix.'_ENTITY_TITLE'
		);
		$this->description = array(
			'lang' => '%_ENTITY_DESCRIPTION',
			'ru' => $this->langPrefix.'_ENTITY_DESCRIPTION',
			'en' => $this->langPrefix.'_ENTITY_DESCRIPTION'
		);
		$this->errorNothingToUpdate = array(
			'lang' => '%_E_NOTHING_TO_UPDATE',
			'ru' => $this->langPrefix.'_E_NOTHING_TO_UPDATE',
			'en' => $this->langPrefix.'_E_NOTHING_TO_UPDATE'
		);
		$this->errorNothingToDelete = array(
			'lang' => '%_E_NOTHING_TO_DELETE',
			'ru' => $this->langPrefix.'_E_NOTHING_TO_DELETE',
			'en' => $this->langPrefix.'_E_NOTHING_TO_DELETE'
		);
		if(!empty($configData['title']) && is_array($configData['title'])) {
			if(!empty($configData['title']['lang'])) $this->title['lang'] = $configData['title']['lang'];
			if(!empty($configData['title']['ru'])) $this->title['ru'] = $configData['title']['ru'];
			if(!empty($configData['title']['en'])) $this->title['en'] = $configData['title']['en'];
		}
		if(!empty($configData['description']) && is_array($configData['title'])) {
			if(!empty($configData['description']['lang'])) $this->title['lang'] = $configData['description']['lang'];
			if(!empty($configData['description']['ru'])) $this->title['ru'] = $configData['description']['ru'];
			if(!empty($configData['description']['en'])) $this->title['en'] = $configData['description']['en'];
		}
		if(!empty($configData['error_nothing_to_delete']) && is_array($configData['error_nothing_to_delete'])) {
			if(!empty($configData['error_nothing_to_delete']['lang'])) $this->errorNothingToDelete['lang'] = $configData['error_nothing_to_delete']['lang'];
			if(!empty($configData['error_nothing_to_delete']['ru']))   $this->errorNothingToDelete['ru'] = $configData['error_nothing_to_delete']['ru'];
			if(!empty($configData['error_nothing_to_delete']['en']))   $this->errorNothingToDelete['en'] = $configData['error_nothing_to_delete']['en'];
		}
		if(!empty($configData['error_nothing_to_update']) && is_array($configData['error_nothing_to_update'])) {
			if(!empty($configData['error_nothing_to_update']['lang'])) $this->errorNothingToUpdate['lang'] = $configData['error_nothing_to_update']['lang'];
			if(!empty($configData['error_nothing_to_update']['ru']))   $this->errorNothingToUpdate['ru'] = $configData['error_nothing_to_update']['ru'];
			if(!empty($configData['error_nothing_to_update']['en']))   $this->errorNothingToUpdate['en'] = $configData['error_nothing_to_update']['en'];
		}
	}

	protected function initFields(&$configData) {
		/** @global \CDatabase $DB */
		global $DB;
		// Обработка данных полей
		if(empty($configData['fields']) || !is_array($configData['fields'])) {
			throw new Err('', Err::E_CFG_FLD_LIST_IS_EMPTY);
		}
		foreach($configData['fields'] as $fieldAlias => &$rawField) {
			$fieldAlias = trim($fieldAlias);
			if( !self::validateTblAlias($fieldAlias) ) {
				throw new Err('', Err::E_CFG_TBL_WRG_NAME);
			}
			$fieldType = ''.trim($rawField['type']);
			if( !$this->checkExistsType($fieldType) ) {
				throw new Err('', Err::E_CFG_FLD_WRG_TYPE);
			}
			$codeStrUpper = strtoupper($fieldAlias);
			// Задаем набор возможных опций поля
			$field = array(
				'code' => $fieldAlias,
				'type' => $fieldType,
				'user_type' => null,
				'length' => null,
				'unsigned' => false,
				'auto_increment' => false,
				'primary_key' => false,
				'deny_null' => false,
				'deny_zero' => false,
				'no_check' => false,
				'required' => false,
				'required_error' => array(
					'lang' => '%_ERR_REQUIRED_'.$codeStrUpper,
					'ru' => 'REQUIRED__'.$codeStrUpper.'__FIELD',
					'en' => 'REQUIRED__'.$codeStrUpper.'__FIELD'
				),
				'default' => null,
				'validator' => null,
				'break_invalid' => false,
				'get' => array(
					'ref' => null,
					'if_null_return' => null,
					'sub_query' => null,
					'sub_query_4_filter' => null,
					'required_tables' => null,
					'required_group_by' => null
				),
				'selected_by_default' => true,
				'title' => array(
					'lang' => '%_FLD_TITLE_OF_'.$codeStrUpper,
					'ru' => 'TITLE_OF__'.$codeStrUpper.'__FIELD',
					'en' => 'TITLE_OF__'.$codeStrUpper.'__FIELD'
				),
				'description' => array(
					'lang' => '%_FLD_DSCR_OF_'.$codeStrUpper,
					'ru' => 'DESCRIPTION_OF__'.$codeStrUpper.'__FIELD',
					'en' => 'DESCRIPTION_OF__'.$codeStrUpper.'__FIELD'
				),
			);
			if('ex' !== $field['type']) {
				$field['selected_by_default'] = true;
			}
			// Заменяем дефолтные параметры поля, на те, что указаны в конфиге
			foreach($field as $fldAttrName => &$fldAttrValue) {
				if(is_bool($fldAttrValue)) {
					if(isset($rawField[$fldAttrName]) ) {
						if( $rawField[$fldAttrName] === !$fldAttrValue) {
							$fldAttrValue = !$fldAttrValue;
						}
					}
				}
				if(null === $fldAttrValue && isset($rawField[$fldAttrName])) {
					$fldAttrValue = ''.$rawField[$fldAttrName];
				}
				if(is_array($fldAttrValue)
					&& is_array($rawField[$fldAttrName])
					&& !empty($rawField[$fldAttrName])
				) {
					if(array_key_exists('lang', $fldAttrValue)) {
						if(!empty($rawField[$fldAttrName]['lang'])) $fldAttrValue['lang'] = $rawField[$fldAttrName]['lang'];
						if(!empty($rawField[$fldAttrName]['ru'])) $fldAttrValue['ru'] = $rawField[$fldAttrName]['ru'];
						if(!empty($rawField[$fldAttrName]['en'])) $fldAttrValue['en'] = $rawField[$fldAttrName]['en'];
					}
					else {
						foreach($fldAttrValue as $fldSubAttrName => &$fldSubAttrValue) {
							if(is_bool($fldSubAttrValue)) {
								if(isset($rawField[$fldAttrName][$fldSubAttrName]) ) {
									if( $rawField[$fldAttrName][$fldSubAttrName] === !$fldSubAttrValue) {
										$fldSubAttrValue = !$fldSubAttrValue;
									}
								}
							}
							if(null === $fldSubAttrValue && isset($rawField[$fldAttrName][$fldSubAttrName])) {
								$fldSubAttrValue = ''.$rawField[$fldAttrName][$fldSubAttrName];
							}
						} unset($fldSubAttrName, $fldSubAttrValue);
					}
				}
			} unset($fldAttrName, $fldAttrValue);
			if(!empty($field['default'])) {
				$field['default'] = $DB->ForSql($field['default']);
			}

			$this->fields[$fieldAlias] = $field;
		} unset($field, $rawField);
	}

	protected function initReferences(&$configData){
		/** @global \CDatabase $DB */
		global $DB;
		// Наполнение ссылок на другие таблицы или сущности
		$rawReferenceList = array();
		if(!empty($configData['reference']) && is_array($configData['reference'])) {
			foreach($configData['reference'] as $rawReference) {
				$reference = array(
					"table" => null,
					"entity" => null,
					"alias" => null,
					"type" => null,
					"condition" => null,
					"reference_field" => null,
					"self_field" => null
				);
				foreach($reference as $refParam => &$refParamValue) {
					if(!empty($rawReference[$refParam]) && is_string($rawReference[$refParam]) ) {
						$refParamValue = $rawReference[$refParam];
					}
				}
				$rawReferenceList[] = $reference;
			} unset($rawReference);
		}

		if(!empty($rawReferenceList)) {
			foreach($rawReferenceList as &$reference) {
				$reference['fields'] = null;
				if(!empty($reference['entity'])) {
					try {
						$referenceConfigPath = self::normalizePath($reference['entity']);
						if(substr($referenceConfigPath, 0, 1) != '/') {
							$curConfigDir = dirname($this->configPath);
							$referenceConfigPath = $curConfigDir.'/'.$reference['entity'];
						}
						$referenceConfigPath = self::normalizePath($referenceConfigPath);
						if(null !== $this->parentRefConfig
							&& $this->parentRefConfig->getConfigPath() == $referenceConfigPath
						) {
							$refEntity = $this->parentRefConfig;
						}
						else {
							$refEntity = new self($referenceConfigPath, $this);
						}
						$reference['fields'] = $refEntity->getFieldsList(true);
						// Если алиас связанной таблицы случайно не заполнен, то надо попробовать
						// взять алиас из объекта связанной сущности + проверить не использовали ли мы его уже
						// (т.е. не занят ли другой связанной таблицей относительно текущей сущносии)
						// и только если алиас из объекта не подошел, только тогда выбрасываем исключение на неправильный алиас
						if( (empty($reference['alias']) || !self::validateTblAlias($reference['alias']))
							&& !array_key_exists($refEntity->getAlias(), $this->reference)
						) {
							$reference['alias'] = $refEntity->getAlias();
						}
						$reference['table'] = $refEntity->getTableName();
					}
					catch(Err $e) {
						if($e->getCode() == Err::E_CFG_REF_ENTITY_SAME_CLASS) {
							throw $e;
						}
						throw new Err(array(
							'#REASON#' => $e->getMessage().' ('.Err::LANG_PREFIX.$e->getCode().')'
						), Err::E_CFG_REF_READ_ENTITY_FAIL);
					}
				}
				else{
					if(!empty($reference['table']) && !self::validateTblName($reference['table'])) {
						throw new Err('', Err::E_CFG_REF_WRG_NAME);
					}
					$rsColumns = $DB->Query('SHOW COLUMNS FROM '.$reference['table']);
					$refColumnsList = array();
					while( $column = $rsColumns->Fetch() ) {
						$refColumnsList[] = $column['Field'];
					}
					if(empty($refColumnsList)) {
						throw new Err(array(
							'#REASON#' => 'OBX_CORE_DBENTITY_EDITOR_E_FETCH_TABLE_COLUMNS_FAILED'
						), Err::E_CFG_REF_READ_ENTITY_FAIL);
					}
					$reference['fields'] = $refColumnsList;
				}
				if( empty($reference['alias']) || !self::validateTblAlias($reference['alias']) ) {
					throw new Err('', Err::E_CFG_REF_WRG_ALIAS);
				}
				if( array_key_exists($reference['alias'], $this->reference) ) {
					throw new Err('', Err::E_CFG_REF_ALIAS_NOT_UQ);
				}
				if(empty($reference['type'])) {
					throw new Err('', Err::E_CFG_REF_WRG_JOIN_TYPE);
				}
				switch($reference['type']) {
					case 'left_join':
					case 'right_join':
					case 'cross':
						break;
					default:
						throw new Err('', Err::E_CFG_REF_WRG_JOIN_TYPE);
				}

				$refCondition = self::parseReferenceCondition($reference['condition']);
				if(null === $refCondition) {
					throw new Err('', Err::E_CFG_REF_WRG_CONDITION);
				}
				if($refCondition['left']['table'] == $this->tableAlias
					&& $refCondition['right']['table'] == $reference['alias']
				) {
					$reference['self_field'] = $refCondition['left']['field'];
					$reference['reference_field'] = $refCondition['right']['field'];
				}
				elseif($refCondition['right']['table'] == $this->tableAlias
					&& $refCondition['left']['table'] == $reference['alias']
				) {
					$reference['self_field'] = $refCondition['right']['field'];
					$reference['reference_field'] = $refCondition['left']['field'];
				}
				else {
					throw new Err('', Err::E_CFG_REF_WRG_CONDITION);
				}
				if(!in_array($reference['reference_field'], $reference['fields'])
					|| empty($this->fields[$reference['self_field']])
				) {
					throw new Err('', Err::E_CFG_REF_WRG_CONDITION);
				}
				$this->reference[$reference['alias']] = $reference;
			}
		}
	}

	protected function initIndex(&$configData) {
		if(!empty($configData['index']) && is_array($configData['index'])) {
			foreach($configData['index'] as $indexName => &$indexConfig) {
				if(!self::validateTblAlias($indexName)) {
					throw new Err('', Err::E_CFG_WRG_IDX);
				}
				if(empty($indexConfig) || !is_array($indexConfig)) {
					throw new Err('', Err::E_CFG_WRG_IDX);
				}
				foreach($indexConfig as $fieldInUnique) {
					if(!self::validateTblAlias($fieldInUnique) || empty($this->fields[$fieldInUnique])) {
						throw new Err('', Err::E_CFG_WRG_IDX);
					}
				}
			}
			$this->index = $configData['index'];
		}
	}

	protected function initUnique(&$configData) {
		if(!empty($configData['unique']) && is_array($configData['unique'])) {
			foreach($configData['unique'] as $rawUqIdxName => &$rawUniqueConfig) {
				if(!self::validateTblAlias($rawUqIdxName)) {
					throw new Err('', Err::E_CFG_WRG_UQ_IDX);
				}
				if(empty($rawUniqueConfig) || !is_array($rawUniqueConfig)
					|| empty($rawUniqueConfig['fields']) || !is_array($rawUniqueConfig['fields'])
				) {
					throw new Err('', Err::E_CFG_WRG_UQ_IDX);
				}
				foreach($rawUniqueConfig['fields'] as $fieldInUnique) {
					if(!self::validateTblAlias($fieldInUnique) || empty($this->fields[$fieldInUnique])) {
						throw new Err('', Err::E_CFG_WRG_UQ_IDX_FLD);
					}
				}
				$this->unique[$rawUqIdxName] = array(
					'fields' => $rawUniqueConfig['fields'],
					'duplicate_error' => array(
						'lang' => '%_E_DUP_UQ_'.$rawUqIdxName,
						'ru' => 'ERR_DUP_UQ__'.$rawUqIdxName,
						'en' => 'ERR_DUP_UQ__'.$rawUqIdxName
					)
				);
				if(!empty($rawUniqueConfig['duplicate_error']['lang'])) {
					$this->unique[$rawUqIdxName]['duplicate_error']['lang'] = $rawUniqueConfig['duplicate_error']['lang'];
				}
				if(!empty($rawUniqueConfig['duplicate_error']['ru'])) {
					$this->unique[$rawUqIdxName]['duplicate_error']['ru'] = $rawUniqueConfig['duplicate_error']['ru'];
				}
				if(!empty($rawUniqueConfig['duplicate_error']['en'])) {
					$this->unique[$rawUqIdxName]['duplicate_error']['en'] = $rawUniqueConfig['duplicate_error']['en'];
				}
			}

		}
	}

	protected function initDefaultSort(&$configData) {
		if(!empty($configData['sort_by_default']) && is_array($configData['sort_by_default'])) {
			foreach($configData['sort_by_default'] as &$rawSort) {
				if( empty($rawSort) || !is_array($rawSort)
					|| empty($rawSort['by']) || empty($rawSort['order'])
				) {
					throw new Err('', Err::E_CFG_WRG_DEF_SORT);
				}
				$rawSort['by'] = trim($rawSort['by']);
				$rawSort['order'] = strtoupper(trim($rawSort['order']));
				switch($rawSort['order']) {
					case 'ASC':
					case 'DESC':
						break;
					default:
						throw new Err('', Err::E_CFG_WRG_DEF_SORT);
				}
				if(strpos($rawSort['by'], '.')!==false) {
					$sortByField = array('table'=>null, 'field'=>null);
					list($sortByField['table'], $sortByField['field']) = explode('.', $rawSort['by']);
					$sortByField['table'] = trim($sortByField['table']);
					$sortByField['field'] = trim($sortByField['field']);
					if($sortByField['table'] == $this->tableAlias) {
						if( empty($this->fields[$sortByField['field']])
							|| 'ex' == $this->fields[$sortByField['field']]['type']
							|| '' == $this->fields[$sortByField['field']]['type']
						) {
							throw new Err('', Err::E_CFG_WRG_DEF_SORT);
						}
					}
					else {
						if(empty($this->reference[$sortByField['table']])) {
							throw new Err('', Err::E_CFG_WRG_DEF_SORT);
						}
						if( empty($this->reference[ $sortByField['table'] ][ $sortByField['field'] ]) ) {
							throw new Err('', Err::E_CFG_WRG_DEF_SORT);
						}
					}
					$this->defaultSort[] = array(
						'by' => $sortByField['table'].'.'.$sortByField['field'],
						'order' => $rawSort['order']
					);
				}
				else {
					if( empty($this->fields[$rawSort['by']]) ) {
						throw new Err('', Err::E_CFG_WRG_DEF_SORT);
					}
					if('ex' == $this->fields[$rawSort['by']]['type']) {
						$this->defaultSort[] = array(
							'by' => $rawSort['by'],
							'order' => $rawSort['order']
						);
					}
					else {
						$this->defaultSort[] = array(
							'by' => $this->tableAlias.'.'.$rawSort['by'],
							'order' => $rawSort['order']
						);
					}
				}
			}
		}
	}

	protected function initDefaultGroupBy(&$configData) {
		if(!empty($configData['group_by_default']) && is_array($configData['group_by_default'])) {
			foreach($configData['group_by_default'] as &$rawGroupBy) {
				$rawGroupBy = trim($rawGroupBy);
				if( empty($rawGroupBy) ) {
					throw new Err('', Err::E_CFG_WRG_DEF_SORT);
				}
				if(strpos($rawGroupBy, '.')!==false) {
					$groupByField = array('table'=>null, 'field'=>null);
					list($groupByField['table'], $groupByField['field']) = explode('.', $rawGroupBy);
					$groupByField['table'] = trim($groupByField['table']);
					$groupByField['field'] = trim($groupByField['field']);
					if($groupByField['table'] == $this->tableAlias) {
						if( empty($this->fields[$groupByField['field']])
							|| 'ex' == $this->fields[$groupByField['field']]['type']
							|| '' == $this->fields[$groupByField['field']]['type']
						) {
							throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
						}
					}
					else {
						if(empty($this->reference[$groupByField['table']])) {
							throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
						}
						if( empty($this->reference[ $groupByField['table'] ][ $groupByField['field'] ]) ) {
							throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
						}
					}
					$this->defaultGroupBy[] = $groupByField['table'].'.'.$groupByField['field'];
				}
				else {
					if( empty($this->fields[$rawGroupBy]) ) {
						throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
					}
					if('ex' == $this->fields[$rawGroupBy]['type']) {
						$this->defaultGroupBy[] = $rawGroupBy;
					}
					else {
						$this->defaultGroupBy[] = $this->tableAlias.'.'.$rawGroupBy;
					}
				}
			}
		}
	}

	static protected function normalizePath($path) {
		Tools::_fixFilePath($path);
		return $path;
	}

	static protected function validateNamespace($namespace) {
		if(strlen($namespace) > 254
			//SomeVeryIncredibleNameOfNamespaceOrFuckingClass - 48 symbols
			|| !preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50}(?:\\\\?))+~', $namespace)
		) {
			return false;
		}
		return true;
	}
	static protected function validateClassName($className) {
		if(!preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50})+~', $className)) {
			return false;
		}
		return true;
	}
	static protected function validateTblAlias($alias) {
		if(!preg_match('~[a-zA-Z][a-zA-Z0-9\_]{0,254}~', $alias)){
			return false;
		}
		return true;
	}
	static protected function validateTblName($name) {
		if(!preg_match('~[a-zA-Z][a-zA-Z0-9\_]{1,62}~', $name)) {
			return false;
		}
		return true;
	}

	static protected function parseReferenceCondition($condition) {
		$condition = trim(strtoupper($condition));
		if( strpos($condition, '=') === false ) {
			return null;
		}
		list($leftField, $rightField) = explode('=', $condition);
		$leftField = trim($leftField);
		$rightField = trim($rightField);
		if(empty($leftField) || empty($rightField)) {
			return null;
		}
		list($leftTableAlias, $leftFieldName) = explode('.', $leftField);
		list($rightTableAlias, $rightFieldName) = explode('.', $rightField);
		$leftTableAlias = trim($leftTableAlias);
		$leftFieldName = trim($leftFieldName);
		$rightTableAlias = trim($rightTableAlias);
		$rightFieldName = trim($rightFieldName);
		if( empty($leftTableAlias) || empty($leftFieldName)
			|| empty($rightTableAlias) || empty($rightFieldName)
		) {
			return null;
		}
		return array(
			'left'  => array('table' => $leftTableAlias,  'field' => $leftFieldName),
			'right' => array('table' => $rightTableAlias, 'field' => $rightFieldName)
		);
	}

	// Interface
	public function getModuleID() {
		return $this->moduleID;
	}
	public function getEventsID() {
		return $this->eventsID;
	}
	public function getNamespace() {
		return $this->namespace;
	}
	public function getClass() {
		return $this->class;
	}
	public function getAlias() {
		return $this->tableAlias;
	}
	public function getTableName() {
		return $this->tableName;
	}
	public function getFieldsList($bOWnFields = false) {
		if(true === $bOWnFields) {
			$ownFields = array();
			foreach($this->fields as $fieldAlias => &$field) {
				if($field['type'] != 'ex' && $field['type'] != '') {
					$ownFields[] = $fieldAlias;
				}
			}
			return $ownFields;
		}
		return array_keys($this->fields);
	}
	public function getField($fieldCode) {
		if(empty($this->fields[$fieldCode])) {
			throw new Err('', Err::E_GET_FLD_NOT_FOUND);
		}
		return $this->fields[$fieldCode];
	}

	public function getIndex() {
		return $this->index;
	}

	public function getUnique() {
		return $this->unique;
	}

	public function getReferences() {
		return $this->reference;
	}

	public function isReadSuccess() {
		return $this->readSuccess;
	}

	public function getCreateTableCode() {
		/** \CDatabase $DB */
		global $DB;
		$createCode = 'create table if not exists '.$this->tableName."(\n";
		$fieldCount = count($this->fields);
		$iField = 0;
		$primaryKey = null;
		foreach($this->fields as &$field) {
			if('ex' == $field['type'] || '' == $field['type']) {
				continue;
			}
			$iField++;
			$dataType = $this->cfgFieldType2MySQL($field);
			$deny_null = ' null';
			$default = '';
			$ai = '';
			if(true === $field['deny_null']) {
				$deny_null = ' not null';
			}
			if(!empty($field['default'])) {
				$default = ' default \''.$DB->ForSql($field['default']).'\'';
			}
			if(true === $field['auto_increment']) {
				$ai = ' auto_increment';
			}
			$comma = ($fieldCount > $iField)?',':'';
			$createCode .= "\t".$field['code'].' '.$dataType.$deny_null.$ai.$default.$comma."\n";
			if(null === $primaryKey && true === $field['primary_key']) {
				$primaryKey = 'primary key('.$field['code'].')';
			}
		}
		if(null !== $primaryKey) {
			$createCode .= "\t".$primaryKey;
		}
		if( !empty($this->unique) || !empty($this->index) ) {
			$createCode .= ",\n";
			foreach($this->unique as $uniqueCode => $unique) {
				$createCode .= "\tunique ".$uniqueCode.'('.implode(', ', $unique['fields']).')';
			}
			if(!empty($this->index)) {
				$createCode .= ",\n";
				foreach($this->index as $indexCode => $index) {
					$createCode .= "\tindex ".$indexCode.'('.implode(', ', $index).")\n";
				}
			}
			else {
				$createCode .= "\n";
			}
		}
		else {
			$createCode .= "\n";
		}
		$createCode .= ");\n";
		return $createCode;
	}
	public function getConfigContent() {
		$references = array();
		foreach($this->reference as $refAlias => &$reference) {
			$references[$refAlias] = array();
			if(!empty($reference['entity'])) {
				$references[$refAlias]['entity'] = $reference['entity'];
			}
			else {
				$references[$refAlias]['table'] = $reference['table'];
			}
			$references[$refAlias]['alias'] = $reference['alias'];
			$references[$refAlias]['type'] = $reference['type'];
			$references[$refAlias]['condition'] = $reference['condition'];
		}
		return self::jsonRemoveUnicodeSequences(self::jsonToReadable(json_encode(array(
			'module' => $this->moduleID,
			'namespace' => $this->namespace,
			'class' => $this->class,
			'class_path' => $this->classPath,
			'version' => $this->version,
			'events_id' => $this->eventsID,
			'lang_prefix' => $this->langPrefix,
			'title' => $this->title,
			'description' => $this->description,
			'error_nothing_to_delete' => $this->errorNothingToDelete,
			'error_nothing_to_update' => $this->errorNothingToUpdate,
			'table_name' => $this->tableName,
			'table_alias' => $this->tableAlias,
			'fields' => $this->fields,
			'unique' => $this->unique,
			'index' => $this->index,
			'group_by_default' => $this->defaultGroupBy,
			'sort_by_default' => $this->defaultSort,
			'reference' => $references
		))));
	}
	static protected function jsonToReadable($json){
		$tc = 0;        //tab count
		$r = '';        //result
		$q = false;     //quotes
		$t = "\t";      //tab
		$nl = "\n";     //new line

		for($i=0;$i<strlen($json);$i++){
			$c = $json[$i];
			if($c=='"' && $json[$i-1]!='\\') $q = !$q;
			if($q){
				$r .= $c;
				continue;
			}
			switch($c){
				case '{':
				case '[':
					$r .= $c . $nl . str_repeat($t, ++$tc);
					break;
				case '}':
				case ']':
					$r .= $nl . str_repeat($t, --$tc) . $c;
					break;
				case ',':
					$r .= $c;
					if($json[$i+1]!='{' && $json[$i+1]!='[') $r .= $nl . str_repeat($t, $tc);
					break;
				case ':':
					$r .= $c . ' ';
					break;
				default:
					$r .= $c;
			}
		}
		return $r;
	}
	static protected function fixBadUnicodeForJson($str) {
		$str = preg_replace("/\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1")).chr(hexdec("$2")).chr(hexdec("$3")).chr(hexdec("$4"))', $str);
		$str = preg_replace("/\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1")).chr(hexdec("$2")).chr(hexdec("$3"))', $str);
		$str = preg_replace("/\\\\u00([0-9a-f]{2})\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1")).chr(hexdec("$2"))', $str);
		$str = preg_replace("/\\\\u00([0-9a-f]{2})/e", 'chr(hexdec("$1"))', $str);
		return $str;
	}
	function jsonRemoveUnicodeSequences($json) {
		//return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", $json);
		// Уберем ключ /e - eval и сделаем через колбэк
		return preg_replace_callback("/\\\\u([a-f0-9]{4})/", function($matches) {
			return iconv('UCS-4LE','UTF-8',pack('V', hexdec('U'.$matches[1])));
		}, $json);
	}

	public function getConfigPath() {
		return $this->configPath;
	}
	public function saveConfigFile($path = null) {
		if(null === $path) {
			$path = $this->configPath;
		}
		$path = OBX_DOC_ROOT.$path;
		if( !CheckDirPath($path) ) {
			throw new Err('', Err::E_SAVE_CFG_FAILED);
		}
		file_put_contents($path, $this->getConfigContent());
	}

	protected function checkExistsType(&$type) {
		if('' === $type) $type = 'ex';
		switch($type) {
			case 'ex':
			case 'no_check':
			case 'pk_id':
			case 'int':
			case 'integer':
			case 'char':
			case 'text':
			case 'string':
			case 'code':
			case 'bool_char':
			case 'bchar':
			case 'real':
			case 'float':
			case 'ident':
			case 'datetime':
			case 'bx_lang_id':
			case 'iblock_id':
			case 'iblock_prop_id':
			case 'ib_prop_id':
			case 'iblock_element_id':
			case 'ib_element_id':
			case 'iblock_section_id':
			case 'ib_section_id':
			case 'user_id':
			case 'group_id':
			case 'user_group_id':
				return true;
			default:
				return false;
		}
	}



	public function cfgFieldType2MySQL(&$field) {
		$type = null;
		switch($field['type']) {
			case '':
			case 'ex':
				break;
			case 'int':
			case 'integer':
			case 'pk_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'char':
				break;
			case 'text':
				$type = 'text';
				break;
			case 'string':
				if(isset($field['length'])) {
					$field['length'] = intval($field['length']);
					if($field['length'] > 255 || $field['length'] < 1) {
						$field['length'] = 255;
					}
				} else $field['length'] = 255;
				$type = 'varchar('.$field['length'].')';
				break;
			case 'code':
				$type = 'varchar(15)';
				break;
			case 'bool_char':
			case 'bchar':
				$type = 'char(1)'.($this->checkUnsignedField($field)?' unsigned':'');
				break;
			case 'real':
			case 'float':
				//http://dev.mysql.com/doc/refman/5.0/en/precision-math-decimal-characteristics.html
				if(isset($field['length'])) {
					list($ldec, $rdec) = explode(',', $field['length']);
					$ldec = intval($ldec);
					$rdec = intval($rdec);
					if($ldec > 64 || $field['length'] < 1) $ldec = 18;
					if($rdec > $ldec ) $rdec = $ldec;
					if($rdec < 1) $ldec = 2;
					$field['length'] = $ldec.','.$rdec;
				} else $field['length'] = '18,2';
				$type = 'decimal('.$field['length'].')'.($this->checkUnsignedField($field)?' unsigned':'');
				break;
			case 'ident':
				$type = 'varchar(255)';
				break;
			case 'datetime':
				$type = 'datetime';
				break;
			case 'bx_lang_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_prop_id':
			case 'ib_prop_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_element_id':
			case 'ib_element_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'iblock_section_id':
			case 'ib_section_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'user_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			case 'group_id':
			case 'user_group_id':
				$type = $this->getIntFieldType4MySQL($field);
				break;
			default:
				throw new Err('', Err::E_CFG_FLD_WRG_TYPE);
		}
		return $type;
	}

	protected function getIntFieldType4MySQL(&$field) {
		if(isset($field['length'])) {
			$field['length'] = intval($field['length']);
			if($field['length'] > 11 || $field['length'] < 1) {
				$field['length'] = 11;
			}
		} else $field['length'] = 11;
		$unsigned = ($this->checkUnsignedField($field)?' unsigned':'');
		return 'int('.$field['length'].')'.$unsigned;
	}

	protected function checkUnsignedField(&$field) {
		if(true === $field['unsigned'] || 'pk_id' === $field['type']) {
			switch($field['type']) {
				case 'pk_id':
				case 'int':
				case 'integer':
				case 'float':
				case 'real':
				case 'bool_char':
				case 'bchar':
					return true; break;
				default: break;
			}
		}
		return false;
	}


} 