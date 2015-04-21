<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/
use OBX\Core\Exceptions\Http\DownloadError as _;
$MESS[_::ID._::E_NO_ACCESS_DWN_FOLDER] = 'Нет доступа во временную папку для загрузки файлов';
$MESS[_::ID._::E_WRONG_PROTOCOL] = 'Неверно указан протокол (необходим http)';
$MESS[_::ID._::E_CONN_FAIL] = 'Не удалось соединиться с сервером';
$MESS[_::ID._::E_CANT_OPEN_DWN_FILE] = 'Не удалось открыть файл для загрузки';
$MESS[_::ID._::E_CANT_WRT_2_DWN_FILE] = 'Не удалось записать в файл для загрузки';
$MESS[_::ID._::E_CANT_SAVE_NOT_FINISHED] = 'Не удалось сохранить файл. Загрузка не завершена';
$MESS[_::ID._::E_CANT_SAVE_TO_FOLDER] = 'Не удалось сохранить загруженный файл. Доступ к папке запрещен';
