<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/
use OBX\Core\Curl\Exceptions\RequestError as _;
$MESS[_::LANG_PREFIX._::E_CURL_NOT_INSTALLED] = 'Не установлена билиотека libcurl';
$MESS[_::LANG_PREFIX._::E_WRONG_PATH] = 'Путь указан неверно';
$MESS[_::LANG_PREFIX._::E_PERM_DENIED] = 'Нет прав на запись файла в папку';
$MESS[_::LANG_PREFIX._::E_FILE_NAME_TOO_LOG] = 'имя файла слишком длинное';
$MESS[_::LANG_PREFIX._::E_NO_ACCESS_DWN_FOLDER] = 'нет доступа во временную папку для загрузки файлов';
$MESS[_::LANG_PREFIX._::E_FILE_SAVE_FAILED] = 'Не удалось сохранить файл';