<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Migration;

// Цепочка версий
class Chain implements \Iterator
{
	private $firstElement = null;
	private $position = 0;
	private $chain = array();
	// '1.1.2 : 1.2.0'

	function rewind() {
		var_dump(__METHOD__);
		$this->position = 0;
	}

	function current() {
		var_dump(__METHOD__);
		return $this->chain[$this->position];
	}

	function key() {
		var_dump(__METHOD__);
		return $this->position;
	}

	function next() {
		var_dump(__METHOD__);
		++$this->position;
	}

	function valid() {
		var_dump(__METHOD__);
		return isset($this->chain[$this->position]);
	}

	public function add(Version $version) {

	}

	public function removeVersion() {

	}
} 