<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Assistant;

abstract class AnAssistant implements IAssistant
{
	const DEF_TIME_LIMIT = 30;

	protected $timeLimit = self::DEF_TIME_LIMIT;
	protected $timeEnd = 0;

	protected function getEndTime() {
		//This is an optimization. We assume than no step can take more than one year.
		if($this->timeLimit > 0) {
			$endTime = time() + $this->timeLimit;
		}
		else {
			$endTime = time() + 365*24*3600; // One year
		}
		return $endTime;
	}

	public function doAction() {

	}

	public function iterate() {
		$endTime = $this->getEndTime();
		do {
			if(time() > $endTime) {
				return true;
			}
			$this->doAction();
		}
		while(true);
		return false;
	}

	public function run() {

	}
} 