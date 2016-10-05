<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto pr0n1x@yandex.ru
 * @copyright 2016 OpenBX
 */

namespace OBX\Core\DomainStruct;


class DomainStructArray extends DomainStructData implements \ArrayAccess {
	// Array Interface
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->domainStructData[] = $value;
		} else {
			$this->domainStructData[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return (isset($this->domainStructData[$offset])
				?true
				:($this->isPathExist($offset)
					?true
					:false
				)
		);
	}

	public function offsetUnset($offset) {
		if( isset($this->domainStructData[$offset]) ) {
			unset($this->domainStructData[$offset]);
		}
		else {
			$refStructure = &$this->domainStructData;
			$path = trim($offset);
			if(empty($path)) return;
			$arPath = explode('.', $path);
			$pathLength = count($arPath);
			$iPathItem = 0;
			foreach($arPath as $pathItem) {
				$iPathItem++;
				$pathItem = trim($pathItem);
				if(empty($pathItem)) {
					$pathItem = static::DOMAIN_MISSED_KEY;
				}
				if(!array_key_exists($pathItem, $refStructure)) {
					return;
				}
				if($pathLength === $iPathItem) {
					unset($refStructure[$pathItem]);
					return;
				}
				$refStructure = &$refStructure[$pathItem];
			}
		}
	}

	public function & offsetGet($offset) {
		if( isset($this->domainStructData[$offset] ) )
			return $this->domainStructData[$offset];
		else
			return static::accessDomainSubtreeReference($this->domainStructData, $offset);
	}
} 