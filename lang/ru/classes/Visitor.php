<?php
/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @License GPLv3                  **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

$MESS["OBX_VISITORS_ID_NAME"] = "ID посетителя";
$MESS["OBX_VISITORS_ID_DESCR"] = "Целое число";
$MESS["OBX_VISITORS_COOKIE_ID_NAME"] = "Идентификатор посетителя";
$MESS["OBX_VISITORS_COOKIE_ID_DESCR"] = "Хранится в куках, строка в 32 символа";
$MESS["OBX_VISITORS_USER_ID_NAME"] = "ID пользователя битрикс";
$MESS["OBX_VISITORS_USER_ID_DESCR"] = "Может быть 0, если посетитель не авторизован. Может повторяться для разных COOKIE_ID.";

$MESS["OBX_VISITORS_ERROR_REQ_FLD_COOKIE_ID"] = "При добавлении посетителя в БД обязательно нужно указать COOKIE_ID.";
$MESS["OBX_VISITORS_ERROR_DUP_ADD_ID"] = "Посетитель с таким ID уже существует в БД.";
$MESS["OBX_VISITORS_ERROR_DUP_ADD_COOKIE_ID"] = "Нельзя добавить, посетитель с таким COOKIE_ID уже существует в БД.";
$MESS["OBX_VISITORS_ERROR_DUP_UPD_COOKIE_ID"] = "Нельзя обновить, посетитель с таким COOKIE_ID уже существует в БД.";
$MESS["OBX_VISITORS_MESSAGE_NOTHING_TO_DELETE"] = "Нелья удалить посетителя - такого посетителя нет в БД.";
$MESS["OBX_VISITORS_ERROR_NOTHING_TO_UPDATE"] = "Ошибка обновления - посетитель не найден в БД.";

$MESS["OBX_VISITORS_HITS_ID_NAME"] = "ID хита посетителя";
$MESS["OBX_VISITORS_HITS_ID_DESCR"] = "Целое число";
$MESS["OBX_VISITORS_HITS_VISITOR_ID_NAME"] = "ID посетителя";
$MESS["OBX_VISITORS_HITS_VISITOR_ID_DESCR"] = "Привязка к первичному ключу таблицы посетителей";
$MESS["OBX_VISITORS_HITS_DATE_HIT_NAME"] = "Дата и время хита";
$MESS["OBX_VISITORS_HITS_DATE_HIT_DESCR"] = "Дата и время хита";
$MESS["OBX_VISITORS_HITS_SITE_ID_NAME"] = "ID текущего сайта";
$MESS["OBX_VISITORS_HITS_SITE_ID_DESCR"] = "ID текущего сайта";
$MESS["OBX_VISITORS_HITS_URL_NAME"] = "URL хита";
$MESS["OBX_VISITORS_HITS_URL_DESCR"] = "URL хита";

$MESS["OBX_VISITORS_HITS_ERROR_REQ_FLD_VISITOR_ID"] = "Ошибка - при добавлении хита обязательно указывать ID посетителя";
$MESS["OBX_VISITORS_HITS_ERROR_DUP_ADD_ID"] = "Ошибка добавления - хит с таким ID уже существует";
$MESS["OBX_VISITORS_HITS_MESSAGE_NOTHING_TO_DELETE"] = "Нелья удалить хит - элемента с таким ID нет в БД";
$MESS["OBX_VISITORS_HITS_ERROR_NOTHING_TO_UPDATE"] = "Ошибка обновления - хит не найден в БД";
?>