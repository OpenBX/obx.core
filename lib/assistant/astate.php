<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Assistant;

abstract class AState implements IState {

	protected $stateData;

	abstract public function linkStateTo(&$EXTERNAL);
	abstract public function getStatus($getFloat = false);

	public function setStateKey($key, $data) {

	}


}
