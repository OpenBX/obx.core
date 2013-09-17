<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

$arModuleVersion = array(
	"VERSION" => "1.1.3",
	"VERSION_DATE" => "2013-09-18",
);
return $arModuleVersion;

/**
 * [1.0.0]
 * | * Стабилизирован релиз
 *
 * [1.0.1]
 * |=== Сборщик ===
 * | * Исправлена ошибка подключения несущесвующих файлов update-оров
 * | * Изменена логика копирования файлов
 * |   updater.custom.(before|after).php находящихся в теле модуля
 *
 * [1.0.2]
 * | * Исправлена проблема работы под windows методов
 * |   OBX\Core\Tools::addComponentLess()
 * |   и
 * |   OBX\Core\Tools::addComponentDeferredJS()
 *
 * [1.1.0]
 * |=== Сборщик ===
 * | * Добавлена возможность установить/удалить исходные данные
 * |   и настройки модуля из консоли CModule::[Un]InstallData()
 * | * В формат конфигурационного файла добавлена
 * |   возможность выполнения команд: @include и @require
 * | * Теперь нет жесткой записимости от конфига в папке /bitrix/modules.build/%MODULE_ID%/release.obuild
 * | * Изменена логика подключения конфигов, ликвидирован макрос %BUILD_FOLDER%(/bitrix/modules.build/%MODULE_ID%/)
 * |
 * |=== Command Line Interface ===
 * | * Исправлена ошибка в запуске консоли
 * |
 * |=== Xml Parser ===
 * | * Добавлены классы для разбора xml-файлов в базу данных для дальнейшего импорта
 * |   - OBX\Core\Xml\Parser <- OBX\Core\Xml\ParserDB
 * |   - OBX\Core\Xml\Exceptions\ParserError
 * |
 *
 * [1.1.1]
 * |=== Xml Parser ===
 * | * Добавлена поддержка индексирования таблицы дерева парсера по значению аттрибутов xml-нод
 *
 * [1.1.2]
 * |=== Xml Parser ===
 * | * Исправлена ошибка в формировании фильтра при запросе в БД
 *
 * [1.1.3]
 * |=== Компоненты ===
 * | * obx:menu.iblock.list теперь в параметрах URL инфоблоков можно указать как ID инфоблока, так и его CODE
 * |=== DBSimple ===
 * | * Исправлен потенциальный warning недекларированного массива
 */