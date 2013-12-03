<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 29.11.13
 * Time: 18:04
 */

namespace OBX\Core\Exceptions;


abstract class AError extends \ErrorException {

	static protected $_arLangMessages = array();
	const _FILE_ = null;
	const LANG_PREFIX = null;

	/**
	 * @param string $message [optional] The Exception message to throw.
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
		parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
	}

	static protected function loadMessages(&$class) {
		if(array_key_exists($class, self::$_arLangMessages)) {
			return ;
		}
		if(static::_FILE_ === null) {
			throw new \ErrorException('You must redeclare '.$class.'::_FILE_ constant exactly: const _FILE_ = __FILE__;');
		}
		if(static::LANG_PREFIX === null) {
			throw new \ErrorException('You must redeclare '.$class.'::LANG_PREFIX constant exactly: const LANG_PREFIX = "YOUR_EXCEPTION_PREFIX";');
		}
		self::$_arLangMessages[$class] = IncludeModuleLangFile(static::_FILE_, false, true);
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
		$message = '';
		$arLangMessages = self::$_arLangMessages[$class];
		$langPrefix = static::LANG_PREFIX;
		if(array_key_exists($langPrefix.$errorCode, $arLangMessages)) {
			$message = self::$_arLangMessages[$class][$langPrefix.$errorCode];
		}
//		else {
//			throw new \ErrorException('Lang message for error code '.get_called_class().'::'.$errorCode.' not found');
//		}
		if(null !== $arReplace && is_array($arReplace)) {
			$arReplaceSearch = array_keys($arReplace);
			$arReplaceTarget = array_values($arReplace);
			$message = str_replace($arReplaceSearch, $arReplaceTarget, $message);
		}
		return $message;
	}
} 