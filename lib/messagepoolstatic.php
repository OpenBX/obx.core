<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;

/**
 * @package OBX\Core
 */
class MessagePoolStatic implements IMessagePoolStatic {
	static protected $MessagePool = array();

	/**
	 * @return MessagePool
	 */
	final static public function getMessagePool() {
		$className = get_called_class();
		if( !isset(self::$MessagePool[$className]) ) {
			self::$MessagePool[$className] = new MessagePool;
		}
		return self::$MessagePool[$className];
	}
	final static public function setMessagePool($MessPool) {
		$className = get_called_class();
		if($MessPool instanceof MessagePool) {
			self::$MessagePool[$className] = $MessPool;
		}
	}
	static public function registerLogFile(LogFile $LogFile) {
		return self::getMessagePool()->registerLogFile($LogFile);
	}
	static public function setLogBehaviour($behaviour) {
		return self::getMessagePool()->setLogBehaviour($behaviour);
	}
	static public function getLogBehaviour($behaviour) {
		return self::getMessagePool()->getLogBehaviour($behaviour);
	}
	static public function setDebugLevel($level) {
		return self::getMessagePool()->setDebugLevel($level);
	}
	static public function getDebugLevel($level) {
		return self::getMessagePool()->getDebugLevel($level);
	}
	static public function getLogFile() {
		return self::getMessagePool()->getLogFile();
	}

	/**
	 * Метод устарел. правильный метод addNotice
	 * @param $text
	 * @param int $code
	 * @param int $debugLevel
	 * @deprecated
	 */
	static public function addMessage($text, $code = 0, $debugLevel = 0) {
		self::getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	static public function addNotice($text, $code = 0, $debugLevel = 0) {
		self::getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	static public function addError($text, $code = 0) {
		self::getMessagePool()->addError($text, $code);
	}

	/**
	 * @param \Exception $Exception
	 * @deprecated
	 * @throws \Exception
	 */
	static public function throwErrorException(\Exception $Exception){
		self::getMessagePool()->throwErrorException($Exception);
	}
	/**
	 * @param \Exception $Exception
	 */
	static public function addErrorException(\Exception $Exception){
		self::getMessagePool()->addErrorException($Exception);
	}
	static public function addWarningException(\Exception $Exception, $debugLevel = MessagePool::MSG_POOL_MAX_DBG_LVL) {
		self::getMessagePool()->addWarningException($Exception, $debugLevel);
	}
	static public function addWarning($text, $code = 0, $debugLevel = 0) {
		self::getMessagePool()->addWarning($text, $code, $debugLevel);
	}
	static public function getLastError($return = 'TEXT') {
		return self::getMessagePool()->getLastError($return);
	}
	static public function getLastWarning($return = 'TEXT') {
		return self::getMessagePool()->getLastWarning($return);
	}

	/**
	 * Метод устарел. Правильный метод getLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	static public function getLastMessage($return = 'TEXT') {
		return self::getMessagePool()->getLastNotice($return);
	}
	static public function getLastNotice($return = 'TEXT') {
		return self::getMessagePool()->getLastNotice($return);
	}
	static public function popLastError($return = 'TEXT') {
		return self::getMessagePool()->popLastError($return);
	}
	static public function popLastWarning($return = 'TEXT') {
		return self::getMessagePool()->popLastWarning($return);
	}
	static public function popLastMessage($return = 'TEXT') {
		return self::getMessagePool()->popLastNotice($return);
	}

	/**
	 * Метод устарел. Правильный метод getNotices
	 * @return array
	 * @deprecated
	 */
	static public function getMessages() {
		return self::getMessagePool()->getNotices();
	}
	static public function getNotices() {
		return self::getMessagePool()->getNotices();
	}
	static public function getErrors() {
		return self::getMessagePool()->getErrors();
	}
	static public function getWarnings() {
		return self::getMessagePool()->getWarnings();
	}
	static public function getMessagePoolData() {
		return self::getMessagePool()->getMessagePoolData();
	}

	/**
	 * Метод устарел. Правильный метод countNotices
	 * @return int
	 * @deprecated
	 */
	static public function countMessages() {
		return self::getMessagePool()->countNotices();
	}
	static public function countNotices() {
		return self::getMessagePool()->countNotices();
	}
	static public function countWarnings() {
		return self::getMessagePool()->countWarnings();
	}
	static public function countErrors() {
		return self::getMessagePool()->countErrors();
	}
	static public function countMessagePoolData() {
		return self::getMessagePool()->countMessagePoolData();
	}
	/**
	 * Метод устарел. Правильный метод clearNotices
	 * @deprecated
	 */
	static public function clearMessages() {
		self::getMessagePool()->clearNotices();
	}
	static public function clearNotices() {
		self::getMessagePool()->clearNotices();
	}
	static public function clearErrors() {
		self::getMessagePool()->clearErrors();
	}
	static public function clearWarnings() {
		self::getMessagePool()->clearWarnings();
	}
	static public function clearMessagePool() {
		self::getMessagePool()->clearMessagePool();
	}
}
