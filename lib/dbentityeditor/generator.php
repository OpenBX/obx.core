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
use OBX\Core\PhpGenerator\PhpClass;

abstract class Generator implements IGenerator {
	/** @var null|\OBX\Core\DBEntityEditor\IConfig */
	protected $config = null;
	/** @var null|\OBX\Core\PhpGenerator\PhpClass */
	protected $phpClass = null;

	final public function __construct(IConfig $config){
		if( null === $config || !$config->isReadSuccess()) {
			throw new Err('', Err::E_CFG_INCORRECT);
		}
		$this->config = $config;
		//$this->class = new PhpClass($this->config->getNamespace().'\\'.$this->config->getClass());
		$this->phpClass = new PhpClass($this->config->getClass());
		$this->phpClass->setNamespace($this->config->getNamespace());
		$this->phpClass->useLangFile();
		$this->__init($config);
	}

	abstract protected function __init();

	public function getConfig() {
		return $this->config;
	}

	// Interface
	public function generateEntityClass() {
		return $this->phpClass->generateClass();
	}

	public function saveEntityClass($path = null) {
		if(empty($path)) {
			$path = $this->config->getClassPath();
		}
		if( !CheckDirPath(OBX_DOC_ROOT.$path) ) {
			throw new Err('', Err::E_CLASS_SAVE_FAILED);
		}
		file_put_contents(OBX_DOC_ROOT.$path, $this->phpClass->generateClass());
	}
}
