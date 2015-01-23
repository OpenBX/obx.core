<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\DBEntityEditor;


interface IConfig {
	function getModuleID();
	function getEventsID();
	function getNamespace();
	function getClass();
	function getAlias();
	function getTableName();
	function getFieldsList();
	function getField($fieldCode);
	function isReadSuccess();
	function getCreateTableCode();
}
