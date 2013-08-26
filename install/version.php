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
	"VERSION" => "1.0.3",
	"VERSION_DATE" => "2013-08-26",
);
return $arModuleVersion;

/**
 * [1.0.0]
 * | * Стабилизирован релиз
 *
 * [1.0.1]
 * | * Builder: исправлена ошибка подключения несущесвующих файлов update-оров
 * | * Builder: изменена логика копирования файлов updater.custom.(before|after).php находящихся в теле модуля
 *
 * [1.0.2]
 * | * Исправлена проблема работы под windows методов
 * |   OBX\Core\Tools::addComponentLess()
 * |   и
 * |   OBX\Core\Tools::addComponentDeferredJS()
 *
 * [1.0.3]
 * | * Добавлена возможность установить/удалить исходные данные
 * |   и настройки модуля из консоли CModule::[Un]InstallData()
 */