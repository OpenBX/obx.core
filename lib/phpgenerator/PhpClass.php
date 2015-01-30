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

class PhpClass extends AbstractClass {

	public function addVariable($access, $name, $initialValue, $static = false) {
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
			$initialValue = self::convertArray2PhpCode($initialValue, "\t\t\t");
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
	public function addVariableIfNotNull($access, $name, $initialValue, $static = false) {
		if(null !== $initialValue) {
			$this->addVariable($access, $name, $initialValue, $static);
		}
	}
	public function addVariableIfNotEmpty($access, $name, $initialValue, $static = false) {
		if(!empty($initialValue)) {
			$this->addVariable($access, $name, $initialValue, $static);
		}
	}

	public function addMethod($access, $name, $argList, $code, $static = false, $abstract = false, $final = false) {
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
					if(!self::validateClassName($argumentType)) {
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

	public function generateClass() {
		// TODO: Написать метод generateClass
		return '';
	}

	/**
	 * Возвращает строку с php-кодом массива переданного на вход
	 * @param Array $array - входной массив, для вывода в виде php-кода
	 * @param String $whiteOffset - отступ от начала каждй строки(для красоты)
	 * @return string
	 */
	static public function convertArray2PhpCode($array, $whiteOffset = '') {
		$strResult = "array(\n";
		foreach($array as $paramName => &$paramValue) {
			if(!is_array($paramValue)) {
				$qt = '\'';
				if(null === $paramValue) {
					$qt = ''; $paramValue = 'null';
				}
				elseif(is_bool($paramValue)) {
					$qt = '';
					$paramValue = ($paramValue?'true':false);
				}
				elseif(is_numeric($paramValue)) {
					$qt = '';
				}
				elseif(is_string($paramValue) || is_object($paramValue)) {
					$paramValue = ''.$paramValue;
					if('::' === substr($paramValue, 0, 2)) {
						$qt = '';
						$paramValue = trim(substr($paramValue, 2));
					}
					else {
						$paramValue = str_replace('\\', '\\\\', $paramValue);
						$paramValue = str_replace('\'', '\\\'', $paramValue);
					}
				}
				$strResult .= $whiteOffset."\t'".$paramName."' => ".$qt.$paramValue.$qt.",\n";
			}
			else {
				$strResult .= $whiteOffset
					."\t'".$paramName
					."' => ".self::convertArray2PhpCode($paramValue, $whiteOffset."\t")
					.",\n";
			}
		}
		$strResult .= $whiteOffset.")";
		return $strResult;
	}

	public function addConstant($name, $value) {
		// TODO: Написать метод generateClass
	}
}