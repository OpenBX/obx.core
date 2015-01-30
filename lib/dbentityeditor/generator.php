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
use OBX\Core\PhpGenerator\PhpClass;

abstract class Generator implements IGenerator {
	/** @var null|\OBX\Core\DBEntityEditor\IConfig  */
	protected $config = null;

	protected $class = null;

	final public function __construct(IConfig $config){
		if( null === $config || !$config->isReadSuccess()) {
			throw new Err('', Err::E_CFG_INCORRECT);
		}
		$this->config = $config;
		$this->class = new PhpClass();
		$this->class->setNamespace($this->config->getNamespace());
		$this->class->setClassName($this->config->getClass());
		$this->__init($config);
	}

	abstract protected function __init();





	// Interface
	public function generateEntityClass() {
		//TODO: Написать код генерации класса
	}
}
