<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Market;
/**
 * TODO: ИСпользователь trait когда придет время PHP-5.4
 */
trait TMessagePool
{
	protected $_arMessages = array();
	protected $_countMessages = 0;
	protected $_arErrors = array();
	protected $_countErrors = 0;
	protected $_arWarnings = array();
	protected $_countWarnings = 0;
	protected $_arCommonMessagePool = array();
	protected $_countCommonMessages = 0;

	public function addMessage($text, $code = 0) {
		$arMessage = array(
			"TEXT" => $text,
			"CODE" => $code
		);
		$this->_arMessages[] = $arMessage;
		$this->_arCommonMessagePool[] = $arMessage;
		$this->_countMessages++;
		$this->_countCommonMessages++;
	}

	public function getLastMessage() {
		return $this->_arMessages[$this->_countMessages];
	}

	public function addError($text, $code) {
		$arMessage = array(
			"TEXT" => $text,
			"CODE" => $code
		);
		$this->_arErrors[] = $arMessage;
		$this->_arCommonMessagePool[] = $arMessage;
		$this->_countErrors++;
		$this->_countCommonMessages++;
	}

	public function getLastError() {
		return $this->_arErrors[$this->_countErrors];
	}

	public function addWarning($text, $code) {
		$arMessage = array(
			"TEXT" => $text,
			"CODE" => $code
		);
		$this->_arWarnings[] = $arMessage;
		$this->_arCommonMessagePool[] = $arMessage;
		$this->_countWarnings++;
		$this->_countCommonMessages++;
	}

	public function getLastWarning() {
		return $this->_arWarnings[$this->_countWarnings];
	}

	public function getMessages() {
		return $this->_arMessages;
	}

	public function getErrors() {
		return $this->_arErrors;
	}

	public function getWarnings() {
		return $this->_arWarnings;
	}
}
?>