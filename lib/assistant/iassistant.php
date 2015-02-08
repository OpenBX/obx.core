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


interface IAssistant {
	function setCondition($conditionData);
	function setStepTime();
	function setPauseTime();

	function getState();
	function getMessagePoos();
	function run();

}