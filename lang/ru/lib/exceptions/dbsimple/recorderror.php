<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

use OBX\Core\Exceptions\DBSimple\RecordError as _;
$MESS[_::ID._::E_RECORD_ENTITY_NOT_SET] = 'Не задан объект сущности записи';
$MESS[_::ID._::E_READ_NOT_DBS_RESULT] = 'Не удалось прочитать Запись(Record) из результата выборки из базы данных. Результат должен быть задан класом OBX\\Core\\DBSimple\\DBResult';
$MESS[_::ID._::E_READ_NO_IDENTITY_FIELD] = 'Не удалось прочитать Запись(Record). Результат не соодержит значения первичного или уникального ключа';
$MESS[_::ID._::E_WRONG_DB_RESULT_ENTITY] = 'Результат выборки из базы данных не соответствует сущности Записи(Record)';
$MESS[_::ID._::E_SET_PRIMARY_KEY_VALUE] = 'Нелья зменять значение поля первичного ключа Записи';
$MESS[_::ID._::E_FIND_RECORD] = 'Не удалось найти запись';
$MESS[_::ID._::E_SAVE_FAILED] = 'Не удалось сохранить запись: #ERROR#';
$MESS[_::ID._::E_GET_WRONG_FIELD] = 'Обращение к несуществующему полю «#FIELD#»';
$MESS[_::ID._::E_SET_WRONG_FIELD] = 'Запись несуществущего поля «#FIELD#»';
$MESS[_::ID._::E_READ_BY_UQ_NOT_ALL_FLD] = 'Невозможно прочитать запись по значениям полей уникального ключа. Не все поля заполнены';
$MESS[_::ID._::E_GET_LAZY_FIELD] = 'Ошибка ленивой загрузки поля «#FIELD#» #REASON#';
//$MESS[_::LANG_PREFIX._::E_] = '';