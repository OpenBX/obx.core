<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto pr0n1x@yandex.ru
 * @copyright 2016 OpenBX
 */

namespace OBX\Core\DomainStruct;


class DomainStructData
{
	const DOMAIN_MISSED_KEY = '_missed_key_';
	const DOMAIN_TITLE_KEY = '_title_';

	protected $domainStructData = null;

	public function __construct($flatData) {
		$flatData = (array) $flatData;
		static::mergeFlatDataToTree($this->domainStructData, $flatData);
	}

	public function getFlatData() {
		$path = '';
		static::_makeFlatDataFromTree($this->domainStructData, $flatData, $path);
		return $flatData;
	}

	static protected function _makeFlatDataFromTree(&$tree, &$flatData = [], $rootPath = '') {
		foreach($tree as $key => &$value) {
			if($key === static::DOMAIN_TITLE_KEY) continue;
			$path = $rootPath.(empty($rootPath)?'':'.').$key;
			if(is_array($value)) {
				if(array_key_exists(static::DOMAIN_TITLE_KEY, $value)) {
					$flatData[$path] = $value[static::DOMAIN_TITLE_KEY];
				}
				static::_makeFlatDataFromTree($value, $flatData, $path);
			}
			else {
				$flatData[$path] = $value;
			}
		}
	}


	static public function mergeFlatDataToTree(&$tree, &$flatData) {
		$refStructure = &$tree;
		foreach($flatData as $path => $value) {
			$refStructure = &static::accessDomainSubtreeReference($refStructure, $path);
			if(is_array($refStructure) && empty($refStructure)) {
				$refStructure = $value;
			}
			$refStructure = &$tree;
		}
		return $tree;
	}

	static public function & accessDomainSubtreeReference(&$tree, $path) {
		if(!is_array($tree)) $tree = [];
		$arPath = explode('.', $path);
		$refStructure = &$tree;
		foreach($arPath as $pathItem) {
			$pathItem = trim($pathItem);
			if(empty($pathItem)) {
				$pathItem = static::DOMAIN_MISSED_KEY;
			}
			if(!is_array($refStructure) && !empty($refStructure)) {
				$refStructure = [
					static::DOMAIN_TITLE_KEY => $refStructure,
					$pathItem => []
				];
			}
			if(!array_key_exists($pathItem, $refStructure)) {
				$refStructure[$pathItem] = [];
			}
			$refStructure = &$refStructure[$pathItem];
		}
		return $refStructure;
	}

	static public function parseFlatData($plainData) {
		$structure = array();
		static::mergeFlatDataToTree($structure, $plainData);
		return $structure;
	}

	public function getDomainSubtree($path = '') {
		$refStructure = &$this->domainStructData;
		$path = trim($path);
		if(empty($path)) return $refStructure;
		$arPath = explode('.', $path);
		foreach($arPath as $pathItem) {
			$pathItem = trim($pathItem);
			if(empty($pathItem)) {
				$pathItem = static::DOMAIN_MISSED_KEY;
			}
			if(!array_key_exists($pathItem, $refStructure)) {
				return null;
			}
			$refStructure = &$refStructure[$pathItem];
		}
		return $refStructure;
	}

	public function isPathExist($path = '') {
		$refStructure = &$this->domainStructData;
		$path = trim($path);
		if(empty($path)) return true;
		$arPath = explode('.', $path);
		foreach($arPath as $pathItem) {
			$pathItem = trim($pathItem);
			if(empty($pathItem)) {
				$pathItem = static::DOMAIN_MISSED_KEY;
			}
			if(!array_key_exists($pathItem, $refStructure)) {
				return false;
			}
			$refStructure = &$refStructure[$pathItem];
		}
		return true;
	}
} 