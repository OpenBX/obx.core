<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\PhpGenerator;

use OBX\Core\Exceptions\PhpGenerator\PhpClassError as Err;

class PhpClass implements IClass {

	private $namespace = null;
	private $uses = array();
	private $bUseLangFile = false;
	private $className = null;
	private $abstract = false;
	private $extends = null;
	private $implements = array();
	private $variables = array();
	private $variablesDynamicInit = array();
	private $methods = array();
	private $constants = array();
	private $langPrefix = null;
	private $langMessages = array();

	public function __construct($class) {
		$arClass = self::splitClassNameFromNamespace($class);
		$this->setNamespace($arClass['namespace']);
		$this->setClassName($arClass['className']);
	}

	public function getNamespace() {
		return $this->namespace;
	}
	public function setNamespace($namespace) {
		if(null === $namespace) {
			$this->namespace;
			return;
		}
		$namespace = trim($namespace);
		if(!self::validateClass($namespace)) {
			throw new Err('', Err::E_WRG_NAMESPACE);
		}
		$this->namespace = $namespace;
	}
	public function getClassName() {
		return $this->className;
	}
	public function setClassName($className) {
		$className = trim($className);
		if(!self::validateClassName($className)) {
			throw new Err('', Err::E_WRG_NAMESPACE);
		}
		$this->className = $className;
	}
	public function getBaseClass() {
		return $this->extends;
	}
	public function setBaseClass($class) {
		$class = trim($class);
		if(!self::validateClass($class)) {
			throw new Err('', Err::E_WRG_NAMESPACE);
		}
		$this->extends = $class;
	}
	public function getImplementation() {
		return $this->implements;
	}
	public function setImplementation($interfacesList) {
		if(!is_array($interfacesList) || empty($interfacesList) ) {
			throw new Err('', Err::E_SET_WRG_IMPL);
		}
		foreach($interfacesList as $interface) {
			$interface = trim($interface);
			if( !self::validateClass($interface) ) {
				throw new Err('', Err::E_SET_WRG_IMPL);
			}
			if(in_array($interface, $this->implements)) {
				throw new Err('', Err::E_SET_IMPL_IF_EXISTS);
			}
			$this->implements[] = $interface;
		}
	}
	public function getUses() {
		return $this->uses;
	}
	public function setUses($usesList) {
		if(!is_array($usesList) || empty($usesList) ) {
			throw new Err('', Err::E_SET_WRG_IMPL);
		}
		foreach($usesList as $use => $alias) {
			if(is_numeric($use)) {
				$use = $alias;
				$alias = null;
			}
			$use = trim($use, '\\ ');
			$alias = trim($alias, '\\ ');
			if( !self::validateNamespace($use) ) {
				throw new Err('', Err::E_SET_WRG_IMPL);
			}
			if(empty($alias)) {
				$arUsePath = self::splitClassNameFromNamespace($use);
				$alias = $arUsePath['className'];
			}
			if( !self::validateClass($alias) ) {
				throw new Err('', Err::E_SET_WRG_IMPL);
			}
			if(!empty($this->uses[$use])) {
				throw new Err('', Err::E_SET_USES_CLASS_EXIST);
			}
			if(in_array($alias, $this->uses)) {
				throw new Err('', Err::E_SET_USES_ALIAS_EXIST);
			}
			$this->uses[$use] = $alias;
		}
	}

	public function getLangPrefix() {
		return $this->langPrefix;
	}

	public function setLangPrefix($langPrefix) {
		$langPrefix = strtoupper(trim($langPrefix));
		if(!empty($langPrefix)) {
			if(self::validateLangMessageID($langPrefix)) {
				$this->langPrefix = $langPrefix;
			}
		}
	}

	public function getMethodsList($bFullDescription = false) {
		if(true === $bFullDescription) {
			return $this->methods;
		}
		else {
			return array_keys($this->methods);
		}
	}
	public function getMethod($name) {
		$name = trim($name);
		if(empty($this->methods[$name])) {
			throw new Err('', Err::E_GET_MET_NO_FOUND);
		}
		return $this->methods[$name];
	}

	public function getVariablesList($bFullDescription = false) {
		if(true === $bFullDescription) {
			return $this->variables;
		}
		else {
			return array_keys($this->variables);
		}
	}
	public function getVariable($name) {
		$name = trim($name);
		if(empty($this->variables[$name])) {
			throw new Err('', Err::E_GET_VAR_NO_FOUND);
		}
		return $this->variables[$name];
	}
	public function getConstantsList($bFullDescription = false) {
		if(true === $bFullDescription) {
			return $this->constants;
		}
		else {
			return array_keys($this->constants);
		}
	}
	public function getConstant($name) {
		$name = trim($name);
		if(empty($this->constants[$name])) {
			throw new Err('', Err::E_GET_CONST_NO_FOUND);
		}
		return $this->constants[$name];
	}

	public function addVariable($access, $name, $initialValue, $static = false, $bLangAutoRegister = false) {
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
		$value = self::convertValue2PhpCode($initialValue, "\t", $bDynamicValue, $valueType, $langRegister);
		$this->variables[$name] = array(
			'name' => $name,
			'value' => $value,
			'type' => $valueType,
			'access' => $access,
			'static' => $static,
			'definition' => ($static?'static ':'').$access.' $'.$name,
			'dynamicValue' => $bDynamicValue,
			'php' => ($static?'static ':'').$access.' $'.$name.' = '.($bDynamicValue?'null':$value).';'
		);
		if($bDynamicValue) {
			$this->variablesDynamicInit[$name] = &$this->variables[$name];
		}
		if(true === $bLangAutoRegister && !empty($langRegister)) {
			foreach($langRegister as $msgID => &$messagesList) {
				foreach($messagesList as $lang => &$message) {
					$this->setLangMessage($msgID, $lang, $message);
				}
			}
		}
	}
	public function addVariableIfNotNull($access, $name, $initialValue, $static = false, $bLangAutoRegister = false) {
		if(null !== $initialValue) {
			$this->addVariable($access, $name, $initialValue, $static, $bLangAutoRegister);
		}
	}
	public function addVariableIfNotEmpty($access, $name, $initialValue, $static = false, $bLangAutoRegister = false) {
		if(!empty($initialValue)) {
			$this->addVariable($access, $name, $initialValue, $static, $bLangAutoRegister);
		}
	}

	public function getVariableDynamicInitCode($varName = null) {
		if(null !== $varName) {
			if(!empty($this->variablesDynamicInit[$varName])) {
				$varValue = $this->variablesDynamicInit[$varName]['value'];
				$varValue = str_replace("\n\t", "\n\t\t", $varValue);
				$varValue = str_replace(
					'Loc::getMessage(\'%_',
					'Loc::getMessage(\''.$this->langPrefix.'_',
					$varValue
				);
				return "\t\t\$this->".$varName.' = '.$varValue.";\n";
			}
			return '';
		}
		else {
			$result = '';
			foreach($this->variables as &$variable) {
				$result .= $this->getVariableDynamicInitCode($variable['name']);
			}
			return $result;
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
			.((true===$static)?'static ':'')
			.$access.' '
			.'function '.$name.'('.implode(',', $argList).")"
		;
		$this->methods[$name] = array(
			'name' => $name,
			'definition' => $methodDefinition,
			'static' => !!$static,
			'abstract' => $abstract,
			'arguments' => $argList,
			'value' =>"\n\t{\n\n".$code."\n\t}",
			'php' => $methodDefinition."\n\t{\n\n".$code."\n\t}"
		);
	}

	public function addConstant($name, $initialValue) {
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
		elseif(is_string($initialValue)) {
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
		$this->constants[$name] = array(
			'name' => $name,
			'value' => $qt.$initialValue.$qt,
			'definition' => 'const '.$name,
			'php' => 'const '.$name.' = '.$qt.$initialValue.$qt.';'
		);
	}

	public function useLangFile($bUse = true) {
		if($bUse === false) {
			$this->bUseLangFile = false;
		}
		else {
			$this->bUseLangFile = true;
		}
	}

	public function generateClass() {
		$phpClass = '<?'."php\n\n";
		$phpClass .= 'namespace '.$this->namespace.";\n";
		foreach($this->uses as $use => $alias) {
			$phpClass .= "\nuse ".$use.' as '.$alias.';';
		}
		if(true === $this->bUseLangFile) {
			$phpClass .= "\n\nuse Bitrix\\Main\\Localization\\Loc;";
			$phpClass .= "\nLoc::loadMessages(__FILE__);";
		}
		$phpClass .= "\n\n".($this->abstract?'abstract ':'').'class '.$this->className;
		if(!empty($this->extends)) {
			$phpClass .= ' extends '.$this->extends;
		}
		if(!empty($this->implements)) {
			$phpClass .= ' implements '.implode(',', $this->implements);
		}
		$phpClass .= "\n{\n";


		if(!empty($this->constants)) {
			foreach($this->constants as $const) {
				$phpClass .= "\t".$const['php']."\n";
			}
		}

		if(!empty($this->variables)) {
			$phpClass .= "\n";
			foreach($this->variables as $variable) {
				$phpClass .= "\t".$variable['php']."\n";
			}
		}

		if(!empty($this->methods)) {
			$phpClass .= "\n";
			foreach($this->methods as $method) {
				$phpClass .= "\t".$method['php']."\n";
			}
		}

		$phpClass .= "\n}\n";
		return $phpClass;
	}

	/**
	 * Возвращает строку с php-кодом массива переданного на вход
	 * @param Array $value - входной массив, для вывода в виде php-кода
	 * @param String $whiteOffset - отступ от начала каждй строки(для красоты)
	 * @param Array $langRegister - если в массиве попадаются элементы для выноса в языковые файлы,
	 * 								они регистрируются в этой ссылке
	 * @param bool &$bDynamicValue
	 * @param string &$valueType
	 * @return string
	 */
	static public function convertValue2PhpCode($value, $whiteOffset = '', &$bDynamicValue = false, &$valueType = null, &$langRegister = null) {
		$bDynamicValue = (true === $bDynamicValue)?true:false;
		$strResult = '';
		if(!is_array($value)) {
			$qt = '\'';
			if(null === $value) {
				$valueType = 'null';
				$qt = ''; $value = 'null';
			}
			elseif(is_bool($value)) {
				$valueType = 'bool';
				$qt = '';
				$value = ($value?'true':false);
			}
			elseif(is_numeric($value)) {
				if(is_int($value)) {
					$valueType = 'int';
				}
				elseif(is_float($value)) {
					$valueType = 'float';
				}
				elseif( floatval($value) == intval($value) ) {
					$valueType = 'int';
					$value = intval($value);
				}
				else {
					$value = 'float';
					$value = floatval($value);
				}
				$qt = '';
			}
			elseif(is_string($value) || is_object($value)) {
				$valueType = 'string';
				$value = ''.$value;
				$dblColonPos = strpos($value, '::');
				if(false !== $dblColonPos) {
					if($dblColonPos == 0) {
						$qt = '';
						$value = trim(substr($value, 2));
						$bDynamicValue = true;
					}
					elseif($dblColonPos > 0) {
						list($constClass, $constName) = explode('::', $value);
						$constClass = trim($constClass);
						$constName = trim($constName);
						if('' === $constClass) {
							$qt = '';
							$bDynamicValue = true;
							$value = $constName;
						}
						elseif('static' === $constClass && self::validateConstName($constName)) {
							$qt = '';
							$bDynamicValue = true;
							$value = 'static::'.$constName;
						}
						elseif('self' === $constClass && self::validateConstName($constName)) {
							$qt = '';
							$value = 'static::'.$constName;
						}
						elseif(self::validateClass($constClass) && self::validateConstName($constName)) {
							$qt = '';
							$value = $constClass.'::'.$constName;
						}
						else {
							$value = str_replace('\\', '\\\\', $value);
							$value = str_replace('\'', '\\\'', $value);
						}
					}
				}
				else {
					$value = str_replace('\\', '\\\\', $value);
					$value = str_replace('\'', '\\\'', $value);
				}
			}
			$strResult = $qt.$value.$qt;
		}
		elseif(!empty($value['lang']) && self::validateLangMessageID($value['lang'])) {
			$langRegister[$value['lang']] = array();
			$msgID = null;
			foreach($value as $lang => &$message) {
				if('lang' === $lang ) {
					$msgID = $message;
					$strResult .= "Loc::getMessage('".$msgID."')";
					$bDynamicValue = true;
					continue;
				}
				if( null !== $msgID && preg_match('~^[a-z]{2}$~', $lang) ) {
					if(empty($langRegister[$msgID]) || !is_array($langRegister[$msgID])) {
						$langRegister[$msgID] = array();
					}
					$langRegister[$msgID][$lang] = $message;
				}
			}
			$valueType = 'lang';
		}
		else {
			$valueType = 'array';
			$complexArray = true;
			if(count($value)==1) {
				list($firstElementKey, $firstElementValue) = each($value);
				if(!is_array($firstElementValue)) {
					$complexArray = false;
				}
				unset($firstElementKey, $firstElementValue);
				reset($value);
			}
			$strResult = 'array('.($complexArray?"\n":'');
			foreach($value as $arKey => &$arValue) {
				$pqt = '\'';
				if(is_numeric($arKey) && floatval($arKey) == intval($arKey)) {
					$pqt = '';
				}
				$strResult .= ($complexArray?$whiteOffset."\t":'')
					.$pqt.$arKey.$pqt." => "
					.self::convertValue2PhpCode($arValue, $whiteOffset."\t", $bDynamicValue, $notValueTypeVar=null, $langRegister)
					.($complexArray?",\n":'');
			}
			$strResult .= ($complexArray?$whiteOffset:'').')';
		}
		return $strResult;
	}

	public function setLangMessage($msgID, $lang, $message) {
		if( !array_key_exists($msgID, $this->langMessages)
			|| !is_array($this->langMessages[$msgID])
		) {
			$this->langMessages[$msgID] = array();
		}
		$this->langMessages[$msgID][$lang] = $message;
	}

	public function setLangMessageArray($langArray) {
		if(!empty($langArray['lang'])) {
			$msgID = null;
			foreach($langArray as $lang => $message) {
				$lang = strtolower(trim($lang));
				if('lang' === $lang && self::validateLangMessageID($message)) {
					$msgID = $message;
					continue;
				}
				if(preg_match('~^[a-z]{2}$~', $lang) && self::validateLangMessageID($msgID) ) {
					if(empty($this->langMessages[$msgID]) || !is_array($this->langMessages[$msgID])) {
						$this->langMessages[$msgID] = array();
					}
					$this->langMessages[$msgID][$lang] = $message;
				}
			}
		}
	}

	static private function validateLangMessageID($msgID) {
		if( preg_match('~[a-zA-Z0-9\\_\\-/\\|:]~', $msgID) ) {
			return true;
		}
		return false;
	}

	public function getLangMessages($msgID) {
		if(!empty($this->langMessages[$msgID]) && is_array($this->langMessages[$msgID])) {
			return $this->langMessages[$msgID];
		}
		return null;
	}

	public function getLangMessage($msgID, $lang) {
		if(!empty($this->langMessages[$msgID]) && !empty($this->langMessages[$msgID][$lang])) {
			return $this->langMessages[$msgID][$lang];
		}
		return null;
	}

	public function generateLangFiles() {
		//TODO: Написать класс генерации языковых файлов
	}

	static protected function validateClass($class) {
		if(strlen($class) > 254
			//SomeVeryIncredibleNameOfNamespaceOrFuckingClass - 48 symbols
			|| !preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50}(?:\\\\?))+~', $class)
		) {
			return false;
		}
		return true;
	}
	static protected function validateNamespace($namespace) {
		$namespace = trim($namespace.'\\ ').'\\';
		if(strlen($namespace) > 255
			//SomeVeryIncredibleNameOfNamespaceOrFuckingClass - 48 symbols
			|| !preg_match('~(?:[a-zA-Z][a-zA-Z0-9\_]{0,50}(?:\\\\))+~', $namespace)
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

	static private function splitClassNameFromNamespace($class) {
		$class = trim($class, ' \\');
		$arClass = array(
			'namespace' => null,
			'className' => null
		);
		if( !self::validateClass($class) ) {
			throw new Err('', Err::E_WRG_CLASS);
		}
		$lastSlashPos = strrpos($class, '\\');
		if($lastSlashPos !== false) {
			$arClass['namespace'] = substr($class, 0, $lastSlashPos);
			$arClass['className'] = substr($class, $lastSlashPos+1);
		}
		else {
			$arClass['className'] = $class;
		}
		return $arClass;
	}
}