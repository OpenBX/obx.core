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

use OBX\Core\Exceptions\PhpGenerator\ClassError as Err;

abstract class AbstractClass implements IClass {


	protected $namespace = null;
	protected $uses = array();
	protected $className = null;
	protected $abstract = false;
	protected $extends = null;
	protected $implements = array();
	protected $variables = array();
	protected $methods = array();

	public function getNamespace() {
		return $this->namespace;
	}
	public function setNamespace($namespace) {
		// TODO: Написать метод setNamespace
	}
	public function getClassName() {
		// TODO: Написать метод getClassName
	}
	public function setClassName($className) {
		// TODO: Написать метод setClassName
	}
	public function getBaseClass() {
		// TODO: Написать метод getBaseClass
	}
	public function setBaseClass($class) {
		// TODO: Написать метод setBaseClass
	}
	public function getImplementation() {
		// TODO: Написать метод getImplementation
	}
	public function setImplementation($interfacesList) {
		// TODO: Написать метод setImplementation
	}
	public function getUses() {
		// TODO: Написать метод getUses
	}
	public function setUses($usesList) {
		// TODO: Написать метод setUses
	}

	public function getMethodsList() {
		// TODO: Написать метод getMethodsList
	}
	public function getMethod($name) {
		// TODO: Написать метод getMethod
	}

	public function getVariablesList() {
		// TODO: Написать метод generateClass
	}
	public function getVariable($name) {
		// TODO: Написать метод generateClass
	}
	public function getConstantsList() {
		// TODO: Написать метод generateClass
	}
	public function getConstant($name) {
		// TODO: Написать метод generateClass
	}

	static protected function validateNamespace($namespace) {
		if(strlen($namespace) > 254
			//SomeVeryIncredibleNameOfNamespaceOrFuckingClass - 48 symbols
			|| !preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50}(?:\\\\?))+~', $namespace)
		) {
			return false;
		}
		return true;
	}
	static protected function validateClassName($className) {
		if(!preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50})+~', $className)) {
			return false;
		}
		return true;
	}
	static public function validateMethodName($name) {
		$name = trim($name);
		if(!preg_match('~(?:[a-zA-Z\_][a-zA-Z0-9\_]{0,50})+~', $name)) {
			return false;
		}
		return true;
	}
	static public function validateVariableName($name) {
		$name = trim($name);
		if(!preg_match('~(?:[a-zA-Z\_][a-zA-Z0-9\_]{0,50})+~', $name)) {
			return false;
		}
		return true;
	}
	static public function validateConstName($name) {
		$name = trim($name);
		if(!preg_match('~(?:[a-zA-Z\_][a-zA-Z0-9\_]{0,50})+~', $name)) {
			return false;
		}
		return true;
	}
} 