<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core;


class MessagePoolIterator extends MessagePool implements \Iterator {
	protected $currentIError = -1;
	protected $currentIMessage = -1;
	protected $currentIWarning = -1;

	const ITERATE_ERRORS = 1;
	const ITERATE_WARNINGS = 2;
	const ITERATE_MESSAGES = 3;
	protected $iterate = self::ITERATE_ERRORS;

	/**
	 * @param $whatIterate
	 * @return self $this
	 */
	public function iterate($whatIterate) {
		switch($whatIterate) {
			case self::ITERATE_ERRORS:
			case self::ITERATE_WARNINGS:
			case self::ITERATE_MESSAGES:
				$this->iterate = $whatIterate;
				break;
			default:
				$this->iterate = self::ITERATE_ERRORS;
				break;
		}
		return $this;
	}

	/**
	 * @return self $this
	 */
	public function iterateErrors() {
		$this->iterate = self::ITERATE_ERRORS;
		return $this;
	}
	/**
	 * @return self $this
	 */
	public function iterateWarning() {
		$this->iterate = self::ITERATE_WARNINGS;
		return $this;
	}
	/**
	 * @return self $this
	 */
	public function iterateMessages() {
		$this->iterate = self::ITERATE_MESSAGES;
		return $this;
	}

	public function current() {
		switch($this->iterate) {
			case self::ITERATE_ERRORS:
				return $this->_arErrors[$this->currentIError];
				break;
			case self::ITERATE_WARNINGS:
				return $this->_arWarnings[$this->currentIWarning];
				break;
			case self::ITERATE_MESSAGES:
				return $this->_arNotices[$this->currentIMessage];
				break;
		}
	}

	public function & getPool() {
		static $arPool = array(
			'CURRENT' => null,
			'COUNT' => null,
			'POOL' => null,
		);
		switch($this->iterate) {
			case self::ITERATE_ERRORS:
				$arPool['CURRENT'] = &$this->currentIError;
				$arPool['COUNT'] = &$this->_countErrors;
				$arPool['POOL'] = &$this->_arErrors;
				break;
			case self::ITERATE_WARNINGS:
				$arPool['CURRENT'] = &$this->currentIWarning;
				$arPool['COUNT'] = &$this->_countWarnings;
				$arPool['POOL'] = &$this->_arWarnings;
				break;
			case self::ITERATE_MESSAGES:
				$arPool['CURRENT'] = &$this->currentIMessage;
				$arPool['COUNT'] = &$this->_countNotices;
				$arPool['POOL'] = &$this->_arNotices;
				break;
		}
		return $arPool;
	}

	public function next() {
		$arPool = $this->getPool();
		$arPool['CURRENT']++;
		if( $arPool['CURRENT'] >= $arPool['COUNT'] ) {
			return null;
		}
		return $arPool['POOL'][$arPool['CURRENT']];
	}

	public function key() {
		$arPool = $this->getPool();
		if( $arPool['CURRENT'] >= $arPool['COUNT'] ) {
			return null;
		}
		return $arPool['CURRENT'];
	}

	public function valid() {
		$arPool = $this->getPool();
		if($arPool['COUNT'] < 1) {
			if($arPool['CURRENT'] == -1) {
				return true;
			}
			return false;
		}
		elseif( $arPool['CURRENT'] >= $arPool['COUNT'] ) {
			return false;
		}
		return true;
	}

	public function rewind() {
		$arPool = $this->getPool();
		if($arPool['COUNT'] < 1) {
			$arPool['CURRENT'] = -1;
		}
		$arPool['CURRENT'] = 0;
	}

	public function getErrorListString() {
		if($this->_countErrors < 1) {
			return '';
		}
		$strErrors = '';
		foreach($this->_arErrors as $arError) {
			$strErrors .= $arError['TEXT']."<br />\n";
		}
		return $strErrors;
	}
} 