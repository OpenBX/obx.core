<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/
use OBX\Core\Exceptions\Xml\ParserError as _;
$MESS[_::LANG_PREFIX._::E_XML_FILE_NOT_FOUND] = 'файл не найден';
$MESS[_::LANG_PREFIX._::E_XML_FILE_CANT_OPEN] = 'Не удалось открыть yml-файл';
$MESS[_::LANG_PREFIX._::E_TMP_TBL_WRONG_NAME] = 'Не верно задано имя временной таблицы';
$MESS[_::LANG_PREFIX._::E_TMP_TBL_EXISTS] = 'Временная таблица уже существует';
$MESS[_::LANG_PREFIX._::E_ADD_ATTR_ON_EXISTS_TBL] = 'Невозможно добавить аттрибут, когда временная таблица уже создана';
$MESS[_::LANG_PREFIX._::E_ADD_IDX_NO_EXISTS_TBL] = 'Невозможно проиндексировать, временная таблица ещё не создана';
$MESS[_::LANG_PREFIX._::E_WRONG_ATTR_NAME] = 'Неверно задано имя аттрибута';
$MESS[_::LANG_PREFIX._::E_ATTR_EXISTS] = 'Аттрибут с именем поля "#FIELD#" уже существует';
$MESS[_::LANG_PREFIX._::E_XML_FILE_EXT_NOT_ALLOWED] = 'Неразрешенное расширение файла';
