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

use OBX\Core\Exceptions\DBEntityEditor\GeneratorError as Err;
use OBX\Core\PhpGenerator\AClass;
use OBX\Core\PhpGenerator\IClass;

abstract class Generator extends AClass implements IGenerator, IClass {
	/** @var null|\OBX\Core\DBEntityEditor\IConfig  */
	protected $config = null;

	protected $namespace = null;
	protected $uses = array();
	protected $className = null;
	protected $abstract = false;
	protected $extends = null;
	protected $implements = array();
	protected $variables = array();
	protected $methods = array();

	final public function __construct(IConfig $config){
		if( null === $config || !$config->isReadSuccess()) {
			throw new Err('', Err::E_CFG_INCORRECT);
		}
		$this->config = $config;
		$this->__init($config);
	}

	abstract protected function __init();

	protected function addMethod($access, $name, $argList, $code, $static = false, $abstract = false, $final = false) {
		if(true === $abstract) {
			$this->abstract = true;
		}
		switch($access) {
			case 'public':
			case 'protected':
			case 'private':
				break;
			default:
				throw new Err('', Err::E_ADD_MET_WRG_ACCESS);
		}
		if(!self::validateMethodName($name)) {
			throw new Err('', Err::E_ADD_MET_WRG_NAME);
		}
		foreach($argList as $argument) {
			if(strpos($argument, ':')!==false) {
				list($argument, $argumentType) = explode(':', $argument);
				$argumentType = trim($argumentType);
				if(!empty($argumentType) ) {
					if(!self::validateClass($argumentType)) {
						throw new Err('', Err::E_ADD_MET_WRG_ARG_TYPE);
					}
					$argument = $argumentType.' '.$argument;
				}

			}
			if(!self::validateVariableName($argument)) {
				throw new Err('', Err::E_ADD_MET_WRG_ARG_NAME);
			}
		}
		$methodDefinition =
			 ((true===$final)?'final ':'')
			.((true===$abstract)?'abstract ':'')
			.$access
			.((true===$static)?'static ':'')
			.'function '.$name.'('.implode(',', $argList).")"
		;
		$this->methods[$name] = array(
			'name' => $name,
			'definition' => $methodDefinition,
			'static' => !!$static,
			'abstract' => $abstract,
			'arguments' => $argList,
			'value' =>"\n{\n\n".$code."\n}\n",
			'php' => $methodDefinition."\n{\n\n".$code."\n}\n"
		);
	}

	protected function addVariable($access, $name, $initialValue, $static = false) {
		$access = trim($access);
		switch($access) {
			case 'public':
			case 'protected':
			case 'private':
				break;
			default:
				throw new Err('', Err::E_ADD_VAR_WRG_ACCESS);
		}
		$name = trim($name);
		if(!self::validateVariableName($name)) {
			throw new Err('', Err::E_ADD_VAR_WRG_NAME);
		}
		$qt = '\'';
		if(null === $initialValue) {
			$qt = ''; $initialValue = 'null';
		}
		elseif(is_bool($initialValue)) {
			$qt = '';
			$initialValue = ($initialValue?'true':false);
		}
		elseif(is_numeric($initialValue)) {
			$qt = '';
		}
		elseif(is_string($initialValue) || is_object($initialValue)) {
			$initialValue = ''.$initialValue;
			if('const:' === substr($initialValue, 0, 6)) {
				$qt = '';
				$initialValue = trim(substr($initialValue, 6));
				if(!self::validateConstName($initialValue)) {
					throw new Err('', Err::E_ADD_VAR_INIT_VAL_NOT_CONST);
				}
			}
			else {
				$initialValue = str_replace('\\', '\\\\', $initialValue);
				$initialValue = str_replace('\'', '\\\'', $initialValue);
			}
		}
		elseif(is_array($initialValue)) {
			$initialValue = Tools::convertArray2PhpCode($initialValue, "\t\t\t");
		}
		$this->variables[$name] = array(
			'name' => $name,
			'value' => $qt.$initialValue.$qt,
			'access' => $access,
			'static' => $static,
			'definition' => ($static?'static ':'').$access.' $'.$name,
			'php' => ($static?'static ':'').$access.' $'.$name.' = '.$qt.$initialValue.$qt.';'
		);
	}

	protected function addVariableIfNotNull($access, $name, $initialValue, $static = false) {
		if(null !== $initialValue) {
			$this->addVariable($access, $name, $initialValue, $static);
		}
	}
	protected function addVariableIfNotEmpty($access, $name, $initialValue, $static = false) {
		if(!empty($initialValue)) {
			$this->addVariable($access, $name, $initialValue, $static);
		}
	}




	protected function addConstant($name, $value) {

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
	static protected function validateClass($class) {
		if(strlen($class) > 254
			//SomeVeryIncredibleNameOfNamespaceOrFuckingClass - 48 symbols
			|| !preg_match('~(?:[a-zA-Z\_][a-zA-Z0-9\_]{0,50}(?:\\\\?))+~', $class)
		) {
			return false;
		}
		return true;
	}

	// Interface
	public function generateEntityClass() {
		//TODO: Написать код генерации класса
	}
}
