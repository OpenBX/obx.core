<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;

interface IMessagePoolStatic
{
	/** @deprecated */
	static function addNotice($text, $code = 0);
	static function addWarning($text, $code = 0);
	static function addError($text, $code = 0);
	static function addErrorException(\Exception $Exception);
	static function addWarningException(\Exception $Exception);

	static function getLastNotice($return = 'TEXT');
	static function getLastWarning($return = 'TEXT');
	static function getLastError($return = 'TEXT');

	static function popLastMessage($return = 'TEXT');
	static function popLastWarning($return = 'TEXT');
	static function popLastError($return = 'TEXT');

	static function getNotices();
	static function getWarnings();
	static function getErrors();
	static function getMessagePoolData();

	static function countNotices();
	static function countWarnings();
	static function countErrors();
	static function countMessagePoolData();

	static function clearNotices();
	static function clearWarnings();
	static function clearErrors();
	static function clearMessagePool();
}
