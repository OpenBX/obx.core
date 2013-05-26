<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @License GPLv3                            **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core;


class JSLang
{
	const DEFAULT_MODULE = 'obx';
	const OVERRIDE_NODE_KEY = '__node_name__';

	static protected $_arInstances = array();
	protected $_moduleName = self::DEFAULT_MODULE;
	protected $_arText = array();
	protected $_arDomains = array();
	protected $_arJSInitializedNodes = array();

	static public function getInstance($moduleName) {
		if( ! preg_match('~[a-z0-9A-Z\_](\.[a-z0-9A-Z\_]){0,1}~', $moduleName) ) {
			$moduleName = static::DEFAULT_MODULE;
		}
		if( ! array_key_exists($moduleName, static::$_arInstances) ) {
			static::$_arInstances[$moduleName] = new static($moduleName);
		}
		return static::$_arInstances[$moduleName];
	}

	protected function __construct($moduleName) {
		$this->_moduleName = $moduleName;
	}
	final protected function __clone() {}

	public function getModuleName() {
		return $this->_moduleName;
	}
	public function getDomainsData() {
		return $this->_arDomains;
	}

	protected function _getNode($domain, $langID = LANGUAGE_ID) {
		$domain = $this->_moduleName.'.lang.'.$langID.'.'.$domain;
		$arDomain = explode('.', $domain);
		$refNode = &$this->_arDomains;
		$refPrevNode = null;
		$lastNodeName = null;
		$initDomainChain = null;
		foreach($arDomain as $nodeName) {
			$initDomainChain .= ($initDomainChain===null)?$nodeName:'.'.$nodeName;
			$this->_arJSInitializedNodes[$initDomainChain] = false;
			if( !array_key_exists($nodeName, $refNode) ) {
				$refNode[$nodeName] = array();
				$refPrevNode = &$refNode;
				$refNode = &$refPrevNode[$nodeName];
				$lastNodeName = $nodeName;
			}
			else {
				if( is_array($refNode[$nodeName]) ) {
					$refPrevNode = &$refNode;
					$refNode = &$refPrevNode[$nodeName];
					$lastNodeName = $nodeName;
				}
				elseif( is_string($refNode[$nodeName]) ) {
					$refPrevNode = &$refNode;
					$refPrevNode[$nodeName] = array(self::OVERRIDE_NODE_KEY => $refNode[$nodeName]);
					$refNode = &$refPrevNode[$nodeName];
					$lastNodeName = $nodeName;
				}
			}
		}
		return array(
			'NODE_NAME' => $lastNodeName,
			'CONTAINER' => &$refPrevNode,
		);
	}

	public function addMessage($domain, $text, $langID = LANGUAGE_ID) {
		if( ! preg_match('~[a-z0-9A-Z\_](\.[a-z0-9A-Z\_]){0,9}~', $domain) ) {
			return false;
		}
		$this->_arText[$domain] = $text;
		$arNode = $this->_getNode($domain, $langID);
		if( array_key_exists($arNode['NODE_NAME'], $arNode['CONTAINER'])
			&& is_array($arNode['CONTAINER'][$arNode['NODE_NAME']])
			&& !empty($arNode['CONTAINER'][$arNode['NODE_NAME']])
		) {
			$arNode['CONTAINER'][$arNode['NODE_NAME']][self::OVERRIDE_NODE_KEY] = &$this->_arText[$domain];
		}
		else {
			$arNode['CONTAINER'][$arNode['NODE_NAME']] = &$this->_arText[$domain];
		}
		return true;
	}

	public function getMessage($domain, $langID = LANGUAGE_ID) {
		$domain = $this->_moduleName.'.lang.'.$langID.'.'.$domain;
		if( array_key_exists($domain, $this->_arText) ) {
			return $this->_arText[$domain];
		}
		return '';
	}

	public function getJSInitDomain($domain, $langID = LANGUAGE_ID) {

	}



	public function showDomain($domain, $langID = LANGUAGE_ID) {

	}

	public function getDomain() {

	}
}