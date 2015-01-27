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
	protected $_config = null;

	protected $_abstract = false;
	protected $_extends = null;
	protected $_implements = array();

	protected $_variables = array();
	protected $_variablesAccess = array();
	protected $_variablesStatic = array();

	protected $_methods = array();
	protected $_methodsAccess = array();
	protected $_methodsStatic = array();
	protected $_methodAbstract = array();

	final public function __construct(IConfig $config){
		if( null === $config || !$config->isReadSuccess()) {
			throw new Err('', Err::E_CFG_INCORRECT);
		}
		$this->_config = $config;
		$this->__init($config);
	}

	abstract protected function __init();

	public function addMethod($access, $name, $argList, $code, $static = false, $abstract = false) {

	}

	public function addInitialVariable($access, $name, $value, $static = false) {

	}

	public function generateEntityClass() {

	}
}
