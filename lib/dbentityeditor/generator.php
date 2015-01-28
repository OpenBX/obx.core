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


abstract class Generator implements IGenerator {
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

	protected function addMethod($access, $name, $argList, $code, $static = false, $abstract = false) {
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
		$methodDefinition = ((true===$abstract)?'abstract ':'')
			.$access
			.((true===$static)?' static':'')
			.'function '.$name.'('.implode(',', $argList).")"
		;
		$this->methods[$name] = array(
			'name' => $name,
			'definition' => $methodDefinition,
			'static' => !!$static,
			'abstract' => $abstract,
			'arguments' => $argList,
			'code' =>"\n{\n"
						."\n".$code."\n"
						."}\n"
		);
	}

	protected function addInitialVariable($access, $name, $initialValue, $static = false) {
		// TODO: Написать проверки
		$quote = '\'';
		if(is_bool($initialValue) ){
			$initialValue = $initialValue?'true':'false';
			$quote = '';
		}
		elseif(is_numeric($initialValue)) {
			$quote = '';
		}
		elseif(strpos($initialValue, 'const:')) {
			$initialValue = trim(str_replace('const:', '', $initialValue));
			if(self::validateConstName($initialValue)) {
				$quote = '';
			}
		}
		$this->variables[$name] = array(
			'name' => $name,
			'value' => $initialValue,
			'access' => $access,
			'static' => $static,
			'code' => ($static?'static ':'').$access.' $'.$name.' = '.$quote.$initialValue.$quote.';'
		);
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
