<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\PhpGenerator;


interface IClassInfo {
	function getNamespace();
	function getClassName();
	function setClassName($className);
	function getBaseClass();
	function getImplementation();
	function setImplementation($interfacesList);
	function getUses();
	function getMethodsList($bFullDescription = false);
	function getMethod($name);
	function getVariablesList($bFullDescription = false);
	function getVariable($name);
	function getConstantsList($bFullDescription = false);
	function getConstant($name);
	function getLangPrefix();
	function getLangMessages($msgID);
	function getLangMessage($msgID, $lang);
} 