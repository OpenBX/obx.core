<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;

class MessagePoolDecorator implements IMessagePool {
	/**
	 * @var null|MessagePool
	 */
	protected $MessagePool = null;

	/**
	 * @return MessagePool
	 */
	public function getMessagePool() {
		if($this->MessagePool == null) {
			$this->MessagePool = new MessagePool;
		}
		return $this->MessagePool;
	}
	public function setMessagePool($MessPool) {
		if($MessPool instanceof MessagePool) {
			$this->MessagePool = $MessPool;
		}
	}

	public function registerLogFile(LogFile $LogFile) {
		return $this->getMessagePool()->registerLogFile($LogFile);
	}
	public function getLogFile() {
		return $this->getMessagePool()->getLogFile();
	}
	public function setLogBehaviour($behaviour) {
		return $this->getMessagePool()->setLogBehaviour($behaviour);
	}
	public function getLogBehaviour($behaviour) {
		return $this->getMessagePool()->getLogBehaviour($behaviour);
	}
	public function setDebugLevel($level) {
		return $this->getMessagePool()->setDebugLevel($level);
	}
	public function getDebugLevel($level) {
		return $this->getMessagePool()->getDebugLevel($level);
	}

	/**
	 * Метод устарел. Праивльный метод addNotice
	 * @param $text
	 * @param int $code
	 * @param int $debugLevel
	 * @deprecated
	 */
	public function addMessage($text, $code = 0, $debugLevel = 0) {
		$this->getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	public function addNotice($text, $code = 0, $debugLevel = 0) {
		$this->getMessagePool()->addNotice($text, $code, $debugLevel);
	}
	public function addError($text, $code = 0) {
		$this->getMessagePool()->addError($text, $code);
	}

	/**
	 * @param \Exception $Exception
	 * @deprecated
	 * @throws \Exception
	 */
	public function throwErrorException(\Exception $Exception){
		$this->getMessagePool()->throwErrorException($Exception);
	}
	/**
	 * @param \Exception $Exception
	 */
	public function addErrorException(\Exception $Exception){
		$this->getMessagePool()->addErrorException($Exception);
	}
	public function addWarningException(\Exception $Exception, $debugLevel = MessagePool::MSG_POOL_MAX_DBG_LVL) {
		$this->getMessagePool()->addWarningException($Exception, $debugLevel);
	}
	public function addWarning($text, $code = 0, $debugLevel = 0) {
		$this->getMessagePool()->addWarning($text, $code, $debugLevel);
	}
	public function getLastError($return = 'TEXT') {
		return $this->getMessagePool()->getLastError($return);
	}
	public function getLastWarning($return = 'TEXT') {
		return $this->getMessagePool()->getLastWarning($return);
	}

	/**
	 * Метод устарел. Праивльный метод getLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	public function getLastMessage($return = 'TEXT') {
		return $this->getMessagePool()->getLastNotice($return);
	}
	public function getLastNotice($return = 'TEXT') {
		return $this->getMessagePool()->getLastNotice($return);
	}
	public function popLastError($return = 'TEXT') {
		return $this->getMessagePool()->popLastError($return);
	}
	public function popLastWarning($return = 'TEXT') {
		return $this->getMessagePool()->popLastWarning($return);
	}

	/**
	 * Метод устарел. Правильный метод popLastNotice
	 * @param string $return
	 * @return mixed
	 * @deprecated
	 */
	public function popLastMessage($return = 'TEXT') {
		return $this->getMessagePool()->popLastNotice($return);
	}
	public function popLastNotice($return = 'TEXT') {
		return $this->getMessagePool()->popLastNotice($return);
	}

	/**
	 * Метод устарел. Праивльный метод getNotices
	 * @return array
	 * @deprecated
	 */
	public function getMessages() {
		return $this->getMessagePool()->getNotices();
	}
	public function getNotices() {
		return $this->getMessagePool()->getNotices();
	}
	public function getErrors() {
		return $this->getMessagePool()->getErrors();
	}
	public function getWarnings() {
		return $this->getMessagePool()->getWarnings();
	}
	public function getMessagePoolData() {
		return $this->getMessagePool()->getMessagePoolData();
	}

	/**
	 * Метод устарел. Правильный метод countNotices
	 * @return int
	 * @deprecated
	 */
	public function countMessages() {
		return $this->getMessagePool()->countNotices();
	}
	public function countNotices() {
		return $this->getMessagePool()->countNotices();
	}
	public function countWarnings() {
		return $this->getMessagePool()->countWarnings();
	}
	public function countErrors() {
		return $this->getMessagePool()->countErrors();
	}
	public function countMessagePoolData() {
		return $this->getMessagePool()->countMessagePoolData();
	}

	/**
	 * Метод устарел. Праивльный метод clearNotices
	 * @deprecated
	 */
	public function clearMessages() {
		$this->getMessagePool()->clearNotices();
	}
	public function clearNotices() {
		$this->getMessagePool()->clearNotices();
	}
	public function clearErrors() {
		$this->getMessagePool()->clearErrors();
	}
	public function clearWarnings() {
		$this->getMessagePool()->clearWarnings();
	}
	public function clearMessagePool() {
		$this->getMessagePool()->clearMessagePool();
	}
}