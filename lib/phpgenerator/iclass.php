<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\PhpGenerator;


interface IClass {
	function getNamespace();
	function setNamespace($namespace);
	function getClassName();
	function setClassName($className);
	function getBaseClass();
	function setBaseClass($class);
	function getImplementation();
	function setImplementation($interfacesList);
	function getUses();
	function setUses($usesList);
	function addMethod($access, $name, $argList, $code, $static = false, $abstract = false, $final = false);
	function getMethodsList();
	function getMethod($name);
	function addVariable($access, $name, $initialValue, $static = false);
	function getVariablesList();
	function getVariable($name);
	function addConstant($name, $value);
	function getConstantsList();
	function getConstant($name);
	function generateClass();
} 