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

interface IEntity
{
	//static function getInstance();
	function add($arFields);
	function update($arFields, $bNotUpdateUniqueFields = false);
	function delete($PRIMARY_KEY_VALUE);
	function deleteByFilter($arFields);
	function getList($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	function getListArray($arSort = null, $arFilter = null, $arGroupBy = null, $arPagination = null, $arSelect = null, $bShowNullFields = true);
	function getByID($PRIMARY_KEY_VALUE, $arSelect = null, $bReturnDBResult = false);
	function getLastQueryString();
}