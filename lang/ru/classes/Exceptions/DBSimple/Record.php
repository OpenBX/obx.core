<?php
use OBX\Core\Exceptions\DBSimple\RecordError as _;
$MESS[_::LANG_PREFIX._::E_RECORD_ENTITY_NOT_SET] = 'Не задан объект сущности записи';
$MESS[_::LANG_PREFIX._::E_CANT_READ_FROM_DB_RESULT] = 'Не удалось прочитать Запись(Record) из результата выборки из базы данных';
$MESS[_::LANG_PREFIX._::E_WRONG_DB_RESULT_ENTITY] = 'Результат выборки из базы данных не соответствует сущности Записи(Record)';
$MESS[_::LANG_PREFIX._::E_CANT_SET_PRIMARY_KEY_VALUE] = 'Нелья зменять значение поля первичного ключа Записи';
$MESS[_::LANG_PREFIX._::E_CANT_FIND_RECORD] = 'Не удалось найти запись';
$MESS[_::LANG_PREFIX._::E_SAVE_FAILED] = 'Не удалось сохранить запись: #ERROR#';
//$MESS[_::LANG_PREFIX._::E_] = '';