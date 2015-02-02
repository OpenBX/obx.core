<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 Devtop                    **
 ***********************************************/

namespace OBX\Core\Assistant;


abstract class State {

	protected $stateData;

	abstract public function linkStateTo(&$EXTERNAL);
	abstract public function getStatus();

	public function setStateKey($key, $data) {

	}

	public function saveStateToFile() {

	}

	public function readStateFromFile() {

	}
} 