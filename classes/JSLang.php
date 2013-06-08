<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
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

	/**
	 * @param $moduleName
	 * @return static
	 */
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

	protected function _getNode($domain, $langID = LANGUAGE_ID, $bCreateInitArray = false) {
		$arDomain = explode('.', $domain);
		$refNode = &$this->_arDomains;
		$refPrevNode = null;
		$lastNodeName = null;
		$domainChain = null;
		foreach($arDomain as $nodeName) {
			$domainChain .= ($domainChain===null)?$nodeName:'.'.$nodeName;
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
					$this->_arText[$domainChain.'.'.self::OVERRIDE_NODE_KEY] = $this->_arText[$domainChain];
					unset($this->_arText[$domainChain]);
					$refPrevNode[$nodeName] = array(
						self::OVERRIDE_NODE_KEY => &$this->_arText[$domainChain.'.'.self::OVERRIDE_NODE_KEY]
					);
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
		$domain = $this->_moduleName.'.lang.'.$langID.((strlen($domain)>0)?'.':'').$domain;
		$this->_arText[$domain] = $text;
		$arNode = $this->_getNode($domain, $langID);
		if( array_key_exists($arNode['NODE_NAME'], $arNode['CONTAINER'])
			&& is_array($arNode['CONTAINER'][$arNode['NODE_NAME']])
			&& !empty($arNode['CONTAINER'][$arNode['NODE_NAME']])
		) {
			$this->_arText[$domain.'.'.self::OVERRIDE_NODE_KEY] = $this->_arText[$domain];
			$arNode['CONTAINER'][$arNode['NODE_NAME']][self::OVERRIDE_NODE_KEY] = &$this->_arText[$domain.'.'.self::OVERRIDE_NODE_KEY];
			unset($this->_arText[$domain]);
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

	public function showHead($domain = '', $langID = LANGUAGE_ID) {
		$this->showJSInitDomain($domain, $langID);
		$this->showDomainScript($domain, $langID);
	}

	public function getHead($domain = '', $langID = LANGUAGE_ID) {
		return $this->getJSInitDomain($domain, $langID, true).$this->getDomainScript($domain, $langID);
	}

	public function showJSInitDomain($domain = '', $langID = LANGUAGE_ID) {
		/**
		 * @var \CMain $APPLICATION
		 */
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array(&$this, 'getJSInitDomain'), $domain, $langID, true);
	}
	public function getJSInitDomain($domain = '', $langID = LANGUAGE_ID, $bCheckInit = false) {
		$this->_initDomain($domain, $langID);
		$return = '';
		$bHeadReturned = false;
		foreach($this->_arJSInitializedNodes as $nameChain => &$bInit) {
			if( !$bCheckInit || !$bInit ) { //&& ! array_key_exists($nameChain, $this->_arText) ) {
				if( ! $bHeadReturned ) {
					$return .= '<script type="text/javascript">'."\n";
					$bHeadReturned = true;
				}
				$bCheckInit && $bInit = true;
//				$return .=
//<<<JS
//	if( typeof( {$nameChain} ) == 'undefined' ) {
//		{$nameChain} = {};
//	}
//
//JS;
				$return .= 'if( typeof( '.$nameChain.' ) == \'undefined\' ) { '.$nameChain.' = {}; }'."\n";
			}
		}
		if( $bHeadReturned ) {
			$return .= '</script>'."\n";
		}
		return $return;
	}

	protected function _initDomain($domain, $langID) {
		$domain = $this->_moduleName.'.lang.'.$langID.((strlen($domain)>0)?'.':'').$domain;
		$arDomain = explode('.', $domain);
		$initDomainChain = null;
		foreach($arDomain as $nodeName) {
			$initDomainChain .= ($initDomainChain===null)?$nodeName:'.'.$nodeName;
			if( !array_key_exists($initDomainChain, $this->_arJSInitializedNodes) ) {
				$this->_arJSInitializedNodes[$initDomainChain] = false;
			}
		}
		$arNode = $this->_getNode($domain, $langID);
		$this->__initDomain($arNode['CONTAINER'][$arNode['NODE_NAME']], $domain);
	}
	protected function __initDomain(&$refNode, $parentDomain) {
		foreach($refNode as $nodeName => &$message) {
			if( is_array($message) ) {
				if( !array_key_exists($parentDomain.'.'.$nodeName, $this->_arJSInitializedNodes) ) {
					$this->_arJSInitializedNodes[$parentDomain.'.'.$nodeName] = false;
				}
				$this->__initDomain($message, $parentDomain.'.'.$nodeName);
			}
		}
	}


	public function showDomainScript($domain = '', $langID = LANGUAGE_ID) {
		/**
		 * @var \CMain $APPLICATION
		 */
		global $APPLICATION;
		$APPLICATION->AddBufferContent(array(&$this, 'getDomainScript'), $domain, $langID);
	}

	public function getDomainScript($domain = '', $langID = LANGUAGE_ID, $bSingleObject = false) {
		if( $domain != '' && ! preg_match('~[a-z0-9A-Z\_](\.[a-z0-9A-Z\_]){0,9}~', $domain) ) {
			return '';
		}
		$return = '<script type="text/javascript">'."\n";
		$domain = $this->_moduleName.'.lang.'.$langID.((strlen($domain)>0)?'.':'').$domain;
		$arNode = $this->_getNode($domain);
		if($bSingleObject) {
			$return .= $domain.' = '.json_encode($arNode['CONTAINER'][$arNode['NODE_NAME']]);
		}
		else {
			$return .= $this->_encodeDomain($arNode['CONTAINER'][$arNode['NODE_NAME']], $domain);
		}
		$return .= '</script>'."\n";
		return $return;
	}

	protected function _encodeDomain(&$refNode, $parentDomainChain) {
		$jsString = '';
		foreach( $refNode as $nodeName => &$message ) {
			if( is_string($message) ) {
				$jsString .= $parentDomainChain.'.'.$nodeName.' = "'.$message.'";'."\n";
			}
			elseif( is_array($message) ) {
				$jsString .= $this->_encodeDomain($message, $parentDomainChain.'.'.$nodeName);
			}
		}
		return $jsString;
	}
}