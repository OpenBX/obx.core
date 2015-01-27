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

class Config implements IConfig
{
	protected $_moduleID = null;
	protected $_eventsID = null;

	protected $MessagePool = null;

	protected $_configPath = null;
	protected $_namespace = null;
	protected $_class = null;
	protected $_classPath = null;
	protected $_version = null;
	protected $_langPrefix = null;
	protected $_title = null;
	protected $_errorNothingToUpdate = null;
	protected $_errorNothingToDelete = null;
	protected $_tableName = null;
	protected $_tableAlias = null;
	protected $_fields = array();
	protected $_unique = array();
	protected $_index = array();
	protected $_reference = array();
	protected $_defaultSort = array();
	protected $_defaultGroupBy = array();
	protected $_parentRefConfig = null;
	protected $_readSuccess = false;

	protected $_createTable = array();

	/**
	 * @param string $entityConfigFile
	 * @param self $referencedConfig
	 * @throws Err
	 */
	public function __construct($entityConfigFile, self $referencedConfig = null) {
		$this->_parentRefConfig = $referencedConfig;
		$this->MessagePool = new MessagePool();
		if( !is_file(OBX_DOC_ROOT.$entityConfigFile) ) {
			throw new Err('', Err::E_OPEN_CFG_FAILED);
		}
		$this->_configPath = self::normalizePath($entityConfigFile);
		$jsonConfig = file_get_contents(OBX_DOC_ROOT.$this->_configPath);
		$configData = json_decode($jsonConfig, true);
		if(null === $configData) {
			throw new Err(
				array('JSON_ERROR' => Tools::getJsonErrorMsg()),
				Err::E_PARSE_CFG_FAILED
			);
		}
		$this->_initialEntityData($configData);
		$this->_initVersion($configData);
		$this->_initEntityClass($configData);
		$this->_initTableName($configData);
		$this->_initLangData($configData);
		$this->_initFields($configData);
		$this->_initReferences($configData);
		$this->_initIndex($configData);
		$this->_initUnique($configData);
		$this->_initDefaultSort($configData);
		$this->_initDefaultGroupBy($configData);
		// Ставим метку завершения чтения, на тот случай если кто-то напишет такой код,
		// в котором объект будет доступен для работы уже после выброса исключения
		$this->_readSuccess = true;
	}

	protected function _initialEntityData(&$configData) {
		if( empty($configData['module'])
			&& !is_dir(OBX_DOC_ROOT.$configData['module'])
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/include.php')
			&& !is_file(OBX_DOC_ROOT.$configData['module'].'/install/index.php')
		) {
			throw new Err('', Err::E_CFG_NO_MOD);
		}
		$this->_moduleID = $configData['module'];
		if( empty($configData['events_id']) ) {
			throw new Err('', Err::E_CFG_NO_EVT_ID);
		}
		$this->_eventsID = $configData['events_id'];
	}

	protected function _initVersion(&$configData) {
		//$this->_version = \CUpdateClient::GetModuleVersion($this->_entityModuleID);
		if(!empty($configData['version']) && strpos($configData['version'], '.') !== false) {
			if( null !== ($arVersion = $this->_parseVersion($configData['version'])) ) {
				$this->_version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
			}
		}
		if(empty($this->_version)) {
			$modulePath = OBX_DOC_ROOT.'/bitrix/modules/'.$this->_moduleID;
			if(is_file($modulePath.'/install/version.php')) {
				/** @noinspection PhpIncludeInspection */
				$returnVersionArray = include $modulePath.'/install/version.php';
				if(!empty($arModuleVersion['VERSION'])
					&& null !== ($arVersion=$this->_parseVersion($arModuleVersion['VERSION']))
				) {
					$this->_version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
				}
				elseif(!empty($returnVersionArray['VERSION'])
					&& null !== ($arVersion=$this->_parseVersion($returnVersionArray['VERSION']))
				) {
					$this->_version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
				}
				else {
					$moduleClass = str_replace('.', '_', $this->_moduleID);
					if(self::validateClassName($moduleClass) ) {
						if( !class_exists($moduleClass) ) {
							/** @noinspection PhpIncludeInspection */
							require_once OBX_DOC_ROOT.$modulePath.'/install/index.php';
						}
						if( class_exists($moduleClass) ) {
							$moduleInstaller = new $moduleClass;
							if( null !== ($arVersion = $this->_parseVersion($moduleInstaller->VERSION)) ) {
								$this->_version = $arVersion['MAJOR'].'.'.$arVersion['MINOR'].'.'.$arVersion['FIXES'];
							}
						}
					}
				}
			}
			if(empty($this->_version)) {
				throw new Err('', Err::E_VERSION_IS_EMPTY);
			}
		}
	}

	protected function _parseVersion($version) {
		$arVersion = explode('.', $version);
		if(count($arVersion) >= 3
			&& is_numeric($arVersion[0])
			&& is_numeric($arVersion[1])
			&& is_numeric($arVersion[2])
		) {
			return array(
				'MAJOR' => intval($arVersion[0]),
				'MINOR' => intval($arVersion[1]),
				'FIXES' => intval($arVersion[2])
			);
		}
		return null;
	}

	protected function _initEntityClass(&$configData) {
		$configData['namespace'] = ''.trim($configData['namespace'], ' \\');
		//$configData['namespace'] = str_replace('\\\\', '\\', $configData['namespace']);
		if( !self::validateNamespace($configData['namespace']) ) {
			throw new Err('', Err::E_CFG_WRG_NAMESPACE);
		}
		$this->_namespace = $configData['namespace'];

		$configData['class'] = ''.trim($configData['class'], ' \\');
		if( !self::validateClassName($configData['class']) ) {
			throw new Err('', Err::E_CFG_WRG_CLASS_NAME);
		}
		$this->_class = $configData['class'];

		$this->_classPath = 'lib/'
			.strtolower(str_replace('\\', '/', $this->_namespace))
			.'/'.$this->_class.'.php'
		;
		if( !empty($configData['class_path']) ) {
			//throw new Err('', Err::E_CFG_NO_CLASS_PATH);
			$this->_classPath = self::normalizePath($configData['class_path']);
		}
		if(null !== $this->_parentRefConfig) {
			$refClass = $this->_parentRefConfig->getNamespace().'\\'.$this->_parentRefConfig->getClass();
			if($refClass == $this->_namespace.'\\'.$this->_class) {
				throw new Err('', Err::E_CFG_REF_ENTITY_SAME_CLASS);
			}
		}
	}

	protected function _initTableName(&$configData) {
		$configData['table_alias'] = trim($configData['table_alias']);
		$configData['table_name'] = trim($configData['table_name']);
		if( !self::validateTblAlias($configData['table_alias']) ) {
			throw new Err('', Err::E_CFG_TBL_WRG_ALIAS);
		}
		if( !self::validateTblName($configData['table_name']) ) {
			throw new Err('', Err::E_CFG_TBL_WRG_NAME);
		}
		$this->_tableName = $configData['table_name'];
		$this->_tableAlias = $configData['table_alias'];
		$this->_langPrefix = str_replace('\\', '_', strtoupper($this->_namespace.'\\'.$this->_class));
		if(!empty($configData['lang_prefix'])) {
			$configData['lang_prefix'] = strtoupper(trim($configData['lang_prefix']));
			if(preg_match('~[A-Z0-9\\_\\-/\\|]~', $configData['lang_prefix'])) {
				$this->_langPrefix = $configData['lang_prefix'];
			}
		}
	}

	protected function _initLangData(&$configData) {
		$this->_title = array(
			'lang' => '%_ENTITY_TITLE',
			'ru' => $this->_langPrefix.'_ENTITY_TITLE',
			'en' => $this->_langPrefix.'_ENTITY_TITLE'
		);
		$this->_errorNothingToUpdate = array(
			'lang' => '%_E_NOTHING_TO_UPDATE',
			'ru' => $this->_langPrefix.'_E_NOTHING_TO_UPDATE',
			'en' => $this->_langPrefix.'_E_NOTHING_TO_UPDATE'
		);
		$this->_errorNothingToDelete = array(
			'lang' => '%_E_NOTHING_TO_DELETE',
			'ru' => $this->_langPrefix.'_E_NOTHING_TO_DELETE',
			'en' => $this->_langPrefix.'_E_NOTHING_TO_DELETE'
		);
		if(!empty($configData['title']) && is_array($configData['title'])) {
			if(!empty($configData['title']['lang'])) $this->_title['lang'] = $configData['title']['lang'];
			if(!empty($configData['title']['ru'])) $this->_title['ru'] = $configData['title']['ru'];
			if(!empty($configData['title']['en'])) $this->_title['en'] = $configData['title']['en'];
		}
		if(!empty($configData['error_nothing_to_delete']) && is_array($configData['error_nothing_to_delete'])) {
			if(!empty($configData['error_nothing_to_delete']['lang'])) $this->_errorNothingToDelete['lang'] = $configData['error_nothing_to_delete']['lang'];
			if(!empty($configData['error_nothing_to_delete']['ru']))   $this->_errorNothingToDelete['ru'] = $configData['error_nothing_to_delete']['ru'];
			if(!empty($configData['error_nothing_to_delete']['en']))   $this->_errorNothingToDelete['en'] = $configData['error_nothing_to_delete']['en'];
		}
		if(!empty($configData['error_nothing_to_update']) && is_array($configData['error_nothing_to_update'])) {
			if(!empty($configData['error_nothing_to_update']['lang'])) $this->_errorNothingToUpdate['lang'] = $configData['error_nothing_to_update']['lang'];
			if(!empty($configData['error_nothing_to_update']['ru']))   $this->_errorNothingToUpdate['ru'] = $configData['error_nothing_to_update']['ru'];
			if(!empty($configData['error_nothing_to_update']['en']))   $this->_errorNothingToUpdate['en'] = $configData['error_nothing_to_update']['en'];
		}
	}

	protected function _initFields(&$configData) {
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
			$codeStrUpper = strtoupper($rawField['code']);
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

			$this->_fields[$fieldAlias] = $field;
		} unset($field, $rawField);
	}

	protected function _initReferences(&$configData){
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
						$curConfigDir = dirname($this->_configPath);
						$referenceConfigPath = $curConfigDir.'/'.$reference['entity'];
						$referenceConfigPath = self::normalizePath($referenceConfigPath);
						if(null !== $this->_parentRefConfig
							&& $this->_parentRefConfig->getConfigPath() == $referenceConfigPath
						) {
							$refEntity = $this->_parentRefConfig;
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
							&& !array_key_exists($refEntity->getAlias(), $this->_reference)
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
				if( array_key_exists($reference['alias'], $this->_reference) ) {
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
				if($refCondition['left']['table'] == $this->_tableAlias
					&& $refCondition['right']['table'] == $reference['alias']
				) {
					$reference['self_field'] = $refCondition['left']['field'];
					$reference['reference_field'] = $refCondition['right']['field'];
				}
				elseif($refCondition['right']['table'] == $this->_tableAlias
					&& $refCondition['left']['table'] == $reference['alias']
				) {
					$reference['self_field'] = $refCondition['right']['field'];
					$reference['reference_field'] = $refCondition['left']['field'];
				}
				else {
					throw new Err('', Err::E_CFG_REF_WRG_CONDITION);
				}
				if(!in_array($reference['reference_field'], $reference['fields'])
					|| empty($this->_fields[$reference['self_field']])
				) {
					throw new Err('', Err::E_CFG_REF_WRG_CONDITION);
				}
				$this->_reference[$reference['alias']] = $reference;
			}
		}
	}

	protected function _initIndex(&$configData) {
		if(!empty($configData['index']) && is_array($configData['index'])) {
			foreach($configData['index'] as $indexName => &$indexConfig) {
				if(!self::validateTblAlias($indexName)) {
					throw new Err('', Err::E_CFG_WRG_IDX);
				}
				if(empty($indexConfig) || !is_array($indexConfig)) {
					throw new Err('', Err::E_CFG_WRG_IDX);
				}
				foreach($indexConfig as $fieldInUnique) {
					if(!self::validateTblAlias($fieldInUnique) || empty($this->_fields[$fieldInUnique])) {
						throw new Err('', Err::E_CFG_WRG_IDX);
					}
				}
			}
			$this->_index = $configData['index'];
		}
	}

	protected function _initUnique(&$configData) {
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
					if(!self::validateTblAlias($fieldInUnique) || empty($this->_fields[$fieldInUnique])) {
						throw new Err('', Err::E_CFG_WRG_UQ_IDX_FLD);
					}
				}
				$this->_unique[$rawUqIdxName] = array(
					'fields' => $rawUniqueConfig['fields'],
					'duplicate_error' => array(
						'lang' => '%_E_DUP_UQ_'.$rawUqIdxName,
						'ru' => 'ERR_DUP_UQ__'.$rawUqIdxName,
						'en' => 'ERR_DUP_UQ__'.$rawUqIdxName
					)
				);
				if(!empty($rawUniqueConfig['duplicate_error']['lang'])) {
					$this->_unique[$rawUqIdxName]['duplicate_error']['lang'] = $rawUniqueConfig['duplicate_error']['lang'];
				}
				if(!empty($rawUniqueConfig['duplicate_error']['ru'])) {
					$this->_unique[$rawUqIdxName]['duplicate_error']['ru'] = $rawUniqueConfig['duplicate_error']['ru'];
				}
				if(!empty($rawUniqueConfig['duplicate_error']['en'])) {
					$this->_unique[$rawUqIdxName]['duplicate_error']['en'] = $rawUniqueConfig['duplicate_error']['en'];
				}
			}

		}
	}

	protected function _initDefaultSort(&$configData) {
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
					if($sortByField['table'] == $this->_tableAlias) {
						if( empty($this->_fields[$sortByField['field']])
							|| 'ex' == $this->_fields[$sortByField['field']]['type']
							|| '' == $this->_fields[$sortByField['field']]['type']
						) {
							throw new Err('', Err::E_CFG_WRG_DEF_SORT);
						}
					}
					else {
						if(empty($this->_reference[$sortByField['table']])) {
							throw new Err('', Err::E_CFG_WRG_DEF_SORT);
						}
						if( empty($this->_reference[ $sortByField['table'] ][ $sortByField['field'] ]) ) {
							throw new Err('', Err::E_CFG_WRG_DEF_SORT);
						}
					}
					$this->_defaultSort[$sortByField['table'].'.'.$sortByField['field']] = $rawSort['order'];
				}
				else {
					if( empty($this->_fields[$rawSort['by']]) ) {
						throw new Err('', Err::E_CFG_WRG_DEF_SORT);
					}
					if('ex' == $this->_fields[$rawSort['by']]['type']) {
						$this->_defaultSort[$rawSort['by']] = $rawSort['order'];
					}
					else {
						$this->_defaultSort[$this->_tableAlias.'.'.$rawSort['by']] = $rawSort['order'];
					}
				}
			}
		}
	}

	protected function _initDefaultGroupBy(&$configData) {
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
					if($groupByField['table'] == $this->_tableAlias) {
						if( empty($this->_fields[$groupByField['field']])
							|| 'ex' == $this->_fields[$groupByField['field']]['type']
							|| '' == $this->_fields[$groupByField['field']]['type']
						) {
							throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
						}
					}
					else {
						if(empty($this->_reference[$groupByField['table']])) {
							throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
						}
						if( empty($this->_reference[ $groupByField['table'] ][ $groupByField['field'] ]) ) {
							throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
						}
					}
					$this->_defaultGroupBy[] = $groupByField['table'].'.'.$groupByField['field'];
				}
				else {
					if( empty($this->_fields[$rawGroupBy]) ) {
						throw new Err('', Err::E_CFG_WRG_DEF_GRP_BY);
					}
					if('ex' == $this->_fields[$rawGroupBy]['type']) {
						$this->_defaultGroupBy[] = $rawGroupBy;
					}
					else {
						$this->_defaultGroupBy[] = $this->_tableAlias.'.'.$rawGroupBy;
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
		return $this->_moduleID;
	}
	public function getEventsID() {
		return $this->_eventsID;
	}
	public function getNamespace() {
		return $this->_namespace;
	}
	public function getClass() {
		return $this->_class;
	}
	public function getAlias() {
		return $this->_tableAlias;
	}
	public function getTableName() {
		return $this->_tableName;
	}
	public function getFieldsList($bOWnFields = false) {
		if(true === $bOWnFields) {
			$ownFields = array();
			foreach($this->_fields as $fieldAlias => &$field) {
				if($field['type'] != 'ex' && $field['type'] != '') {
					$ownFields[] = $fieldAlias;
				}
			}
			return $ownFields;
		}
		return array_keys($this->_fields);
	}
	public function getField($fieldCode) {
		if(array_key_exists($fieldCode, $this->_fields)) {
			throw new Err('', Err::E_GET_FLD_NOT_FOUND);
		}
		return $this->_fields[$fieldCode];
	}

	public function getIndex() {
		return $this->_index;
	}

	public function getUnique() {
		return $this->_unique;
	}

	public function isReadSuccess() {
		return $this->_readSuccess;
	}

	public function getCreateTableCode() {
		/** \CDatabase $DB */
		global $DB;
		$createCode = 'create table if not exists '.$this->_tableName."(\n";
		$fieldCount = count($this->_fields);
		$iField = 0;
		$primaryKey = null;
		foreach($this->_fields as &$field) {
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
		if( !empty($this->_unique) || !empty($this->_index) ) {
			$createCode .= ",\n";
			foreach($this->_unique as $uniqueCode => $unique) {
				$createCode .= "\tunique ".$uniqueCode.'('.implode(', ', $unique['fields']).')';
			}
			if(!empty($this->_index)) {
				$createCode .= ",\n";
				foreach($this->_index as $indexCode => $index) {
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
		// TODO: Написать получение кода конфига из класса
		// TODO: Написать методы __sleep и __wakeup
	}

	public function getConfigPath() {
		return $this->_configPath;
	}
	public function saveConfigFile() {
		//TODO: Написать сохранение конфига
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