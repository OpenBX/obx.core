<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;

interface IMessagePool
{
	function addNotice($text, $code = 0);
	function addWarning($text, $code = 0);
	function addError($text, $code = 0);
	function addErrorException(\Exception $Exception);
	function addWarningException(\Exception $Exception);

	function getLastNotice($return = 'TEXT');
	function getLastWarning($return = 'TEXT');
	function getLastError($return = 'TEXT');

	function popLastNotice($return = 'TEXT');
	function popLastWarning($return = 'TEXT');
	function popLastError($return = 'TEXT');

	function getNotices();
	function getWarnings();
	function getErrors();
	function getMessagePoolData();

	function countNotices();
	function countWarnings();
	function countErrors();
	function countMessagePoolData();

	function clearNotices();
	function clearWarnings();
	function clearErrors();
	function clearMessagePool();
}