<?php
/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @license Affero GPLv3           **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

$MESS["OBX_DB_SIMPLE_ERR_NOTHING_TO_DELETE"] = "Ошибка удаления. Запись не найдена.";
$MESS["OBX_DB_SIMPLE_ERR_ADD_DUP_PK"] = "Ошибка создания записи. Запись со значением поля #PK_NAME# = «#PK_VALUE#» уже существует";
$MESS["OBX_DB_SIMPLE_ERR_ADD_DUP_UNIQUE"] = "Ошибка создания. Запись со значением уникального индекса (#FLD_LIST#) = (#FLD_VALUES#) уже существует";
$MESS["OBX_DB_SIMPLE_ERR_UPD_DUP_UNIQUE"] = "Ошибка обновления. Запись со значением уникального индекса (#FLD_LIST#) = (#FLD_VALUES#) уже существует";
$MESS["OBX_DB_SIMPLE_ERR_ADD_MISS_FIELD"] = "Не заполено (или неверно) обязательное поле «#FIELD#» при создании записи";
$MESS["OBX_DB_SIMPLE_ERR_UPD_NOTHING_TO_UPDATE"] = "Ошибка обновления. Запись не найдена";
$MESS["OBX_DB_SIMPLE_ERR_CANT_DEL_WITHOUT_PK"] = "Невозможно удалить запись из таблицы «#TABLE#» с использованием метода delete(). Для таблицы не предусмотрен первичный ключ.";
?>