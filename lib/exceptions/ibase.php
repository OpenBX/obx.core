<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Exceptions;

interface IBase {
	static public function getLangMessage($errorCode, $arReplace = null);
	public function getFullCode(); // self::ID.$this->getCode()

	/* Protected methods inherited from Exception class */
	public function getMessage();                 // Exception message
	public function getCode();                    // User-defined Exception code
	public function getFile();                    // Source filename
	public function getLine();                    // Source line
	public function getTrace();                   // An array of the backtrace()
	public function getTraceAsString();           // Formated string of trace

	/* Overrideable methods inherited from Exception class */
	public function __toString();                 // formated string for display
	public function __construct($message = null, $code = 0);
} 