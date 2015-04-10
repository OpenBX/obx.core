<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\PhpGenerator;


interface IClassEdit {
	function setNamespace($namespace);
	function setBaseClass($class);
	function setUses($usesList);
	function setLangPrefix($langPrefix);
	function setLangMessage($msgID, $lang, $message);
	function addMethod($access, $name, $argList, $code, $static = false, $abstract = false, $final = false);
	function addVariable($access, $name, $initialValue, $static = false);
	function addConstant($name, $value);
} 