<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

$MESS["OBX_VISITORS_ID_NAME"] = "ID посетителя";
$MESS["OBX_VISITORS_ID_DESCR"] = "Целое число";
$MESS["OBX_VISITORS_COOKIE_ID_NAME"] = "Идентификатор посетителя";
$MESS["OBX_VISITORS_COOKIE_ID_DESCR"] = "Хранится в куках, строка в 32 символа";
$MESS["OBX_VISITORS_USER_ID_NAME"] = "ID пользователя битрикс";
$MESS["OBX_VISITORS_USER_ID_DESCR"] = "Может быть 0, если посетитель не авторизован. Может повторяться для разных COOKIE_ID.";

$MESS["OBX_VISITORS_ERROR_REQ_FLD_COOKIE_ID"] = "При добавлении посетителя в БД обязательно нужно указать COOKIE_ID.";
$MESS["OBX_VISITORS_ERROR_WRONG_COOKIE_ID"] = "Неверно указан идентификатор COOKIE_ID";
$MESS["OBX_VISITORS_ERROR_DUP_ADD_ID"] = "Посетитель с таким ID уже существует в БД.";
$MESS["OBX_VISITORS_ERROR_DUP_ADD_COOKIE_ID"] = "Нельзя добавить, посетитель с таким COOKIE_ID уже существует в БД.";
$MESS["OBX_VISITORS_ERROR_DUP_UPD_COOKIE_ID"] = "Нельзя обновить, посетитель с таким COOKIE_ID уже существует в БД.";
$MESS["OBX_VISITORS_MESSAGE_NOTHING_TO_DELETE"] = "Нелья удалить посетителя - такого посетителя нет в БД.";
$MESS["OBX_VISITORS_ERROR_NOTHING_TO_UPDATE"] = "Ошибка обновления - посетитель не найден в БД.";
