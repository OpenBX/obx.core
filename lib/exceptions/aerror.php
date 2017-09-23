<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core\Exceptions;
use Bitrix\Main\Localization\Loc;

abstract class AError extends \ErrorException implements IBase {

	static protected $_arLangMessages = array();
	const FILE = null;
	const ID = null;

	/**
	 * @param string|array $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param int $severity [optional] The severity level of the exception.
	 * @param string $filename [optional] The filename where the exception is thrown.
	 * @param int $lineno [optional] The line number where the exception is thrown.
	 * @param \Exception $previous [optional] The previous exception used for the exception chaining.
	 */
	public function __construct($message = '', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, $previous = null) {
		if(empty($message)) {
			$message = static::getLangMessage($code);
		}
		elseif(is_array($message)) {
			$arReplace = $message;
			$message = static::getLangMessage($code, $arReplace);
		}
		parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
	}

	static protected function loadMessages(&$class) {
		if(array_key_exists($class, self::$_arLangMessages)) {
			return ;
		}
		if(static::FILE === null) {
			throw new \ErrorException('You must redeclare '.$class.'::FILE constant exactly: const FILE = __FILE__;');
		}
		if(static::ID === null) {
			throw new \ErrorException('You must redeclare '.$class.'::ID constant exactly: const ID = "YOUR_LANG_MESSAGES_PREFIX";');
		}
		self::$_arLangMessages[$class] = Loc::loadLanguageFile(static::FILE, false, true);
	}

	/**
	 * @param $errorCode
	 * @param array|null $arReplace
	 * @return string
	 * @throws \ErrorException
	 */
	static public function getLangMessage($errorCode, $arReplace = null) {
		$class = get_called_class();
		self::loadMessages($class);
		$arLangMessages = &self::$_arLangMessages[$class];
		$msgID = static::ID;
		if(array_key_exists($msgID.$errorCode, $arLangMessages)) {
			$message = $arLangMessages[$msgID.$errorCode];
		}
		else {
			$message = $msgID.intval($errorCode);
		}
		if(null !== $arReplace && is_array($arReplace)) {
			$arReplaceSearch = array_keys($arReplace);
			$arReplaceTarget = array_values($arReplace);
			$message = str_replace($arReplaceSearch, $arReplaceTarget, $message);
		}
		return $message;
	}

	public function getFullCode() {
		return static::ID.$this->getCode();
	}
} 