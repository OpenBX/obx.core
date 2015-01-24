<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\DBSimple;

interface IEntityStatic
{
	//static function getInstance();
	static function add($arFields);
	static function update($arFields, $bNotUpdateUniqueFields = false);
	static function delete($PRIMARY_KEY_VALUE);
	static function deleteByFilter($arFields);
	static function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	static function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	static function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnDBResult = false);
	static function getLastQueryString();
}