<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Assistant;

interface IAssistant {
	function setCondition($conditionData);
	function setStepTime();
	function setPauseTime();

	/**
	 * @return IState
	 */
	function getState();

	/**
	 * @param IState $state
	 * @return bool
	 */
	function setState(IState $state);

	/**
	 * @return \OBX\Core\MessagePool
	 */
	function getMessagePool();

	/**
	 * @return bool
	 */
	function iterate();
	function run();

}