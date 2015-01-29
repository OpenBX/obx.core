<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\Exceptions\DBEntityEditor;
use OBX\Core\Exceptions\AError;

class ConfigError extends AError {
	const _DIR_ = __DIR__;
	const _FILE_ = __FILE__;
	const LANG_PREFIX = 'OBX_CORE_ENT_GEN_CFG_';

	const E_OPEN_CFG_FAILED = 1;			// Не удалось открыть файл
	const E_PARSE_CFG_FAILED = 2;			// Не удалось прочитать файл
	const E_CFG_NO_MOD = 3;					// На задан модуль
	const E_CFG_NO_EVT_ID = 4;				// Не задан идентификатор событий
	const E_CFG_WRG_NAMESPACE = 5;			// Неверно задано пространство имен класса сущности
	const E_CFG_WRG_CLASS_NAME = 6;			// Неверно задан класс сущности
	const E_CFG_NO_CLASS_PATH = 7;			// Не задан путь сохранения класса сущности (это папка, не файл)
	const E_CFG_FLD_LIST_IS_EMPTY = 8;		// Список полей пуст
	const E_VERSION_IS_EMPTY = 9;			// не указана версия сущности и не удалось получить её из версии модуля

	const E_CFG_TBL_WRG_NAME = 10;			// Неверно указано имя таблицы
	const E_CFG_TBL_WRG_ALIAS = 11;			// Неверно укзан псевдоним сущности

	const E_CFG_FLD_WRG_NAME = 21;			// Неверно указано имя поля сущности
	const E_CFG_FLD_WRG_TYPE = 22;			// Неверно указан тип поля сущности
	const E_CFG_FLD_EX_WRG_REF = 23;		// Неверн указано поле связанной сущности в поле исходной сущности

	const E_CFG_WRG_IDX = 30;				// Неверно задан индекс
	const E_CFG_WRG_IDX_FLD = 31;			// Неверно задано поле индекса
	const E_CFG_WRG_UQ_IDX = 35;			// Неверно задан уникальный индекс
	const E_CFG_WRG_UQ_IDX_FLD = 36;		// Неверно задано поле уникального индекса

	const E_CFG_REF_WRG_NAME = 40;			// Неверное имя связи
	const E_CFG_REF_WRG_ALIAS = 41;			// Неверно казан псевдоним связанной сущности
	const E_CFG_REF_ALIAS_NOT_UQ = 42;		// Псевдоним связанной сущности уже задан для другой связи
	const E_CFG_REF_READ_ENTITY_FAIL = 43;	// Неудалось прочитать конфигурацию связанной сущности
	const E_CFG_REF_WRG_JOIN_TYPE = 44;		// Неверно указан тип JOIN-а связпнной таблицы/сущности
	const E_CFG_REF_WRG_CONDITION = 45;		// Неверно задано условие связи
	const E_CFG_REF_ENTITY_SAME_CLASS = 46;	// Класс связанной сущности имеет тот же класс, что и искходная сущность

	const E_CFG_WRG_DEF_SORT = 50;			// Неверно указана сортировка по умолчанию
	const E_CFG_WRG_DEF_GRP_BY = 51;		// Неверно указанна группировка по умолчанию

	const E_GET_FLD_NOT_FOUND = 70;			// Не удалось получить поле, имя задано неверно (метод getField)
	const E_SAVE_CFG_FAILED = 80;			// Не удалось сохранить конфигурацию сущности

	//const E_CFG_
}