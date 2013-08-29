<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Xml;
use OBX\Core\Xml\Exceptions\ParserError;
IncludeModuleLangFile(__FILE__);

class Parser extends ParserDB {

	protected $_filePath = null;
	protected $_file = null;
	protected $_fileLength = 0;
	protected $_fileCharset = null;
	protected $_bMBStringOrig = false;
	protected $_bUTF = false;

	protected $_filePosition = 0;

	/**
	 * @var int Рзмер порции данных считываемых и xml-файла в байтах
	 */
	protected $_chunkReadSize = 10240;
	protected $_buffer = null;
	protected $_bufferLength = 0;
	protected $_bufferPosition = 0;

	/**
	 * @var string Время между итерациями парсинга в секундах
	 */
	protected $_readTimeLimit = 0;

	protected $_arElementStack = array();

	protected $_bReadyToReadYML = false;

	/**
	 * @param string $filePath
	 * @throws ParserError
	 */
	public function __construct($filePath) {
		if( is_file($filePath) && substr($filePath, strrpos($filePath, '.')) == '.xml' )  {
			$this->_filePath = $filePath;
			$this->_file = fopen($filePath, 'r');
			if( !$this->_file ) {
				throw new ParserError(GetMessage('OBX\Core\Xml\Exceptions\ParserError::YML_FILE_CANT_OPEN'), ParserError::YML_FILE_CANT_OPEN);
			}
		}
		else {
			throw new ParserError(GetMessage('OBX\Core\Xml\Exceptions\ParserError::YML_FILE_NOT_FOUND'), ParserError::YML_FILE_NOT_FOUND);
		}
		if( defined('BX_UTF') ) {
			$this->_bUTF = true;
			if( function_exists('mb_orig_strpos')
				&& function_exists('mb_orig_strlen')
				&& function_exists('mb_orig_substr')
			) {
				$this->_bMBStringOrig = true;
			}
			else {
				$this->_bMBStringOrig = false;
			}
		}
		$this->_bReadyToReadYML = true;
	}

	public function getFilePosition() {
		return $this->_filePosition;
	}

	public function setReadTimeLimit($seconds) {
		$seconds = intval($seconds);
		if( is_int($seconds) ) {
			$this->_readTimeLimit = $seconds;
		}
	}
	public function getReadTimeLimit() {
		return $this->_readTimeLimit;
	}

	public function setReadSize($bytes) {
		$bytes = intval($bytes);
		if( is_int($bytes) ) {
			$this->_chunkReadSize = $bytes;
		}
	}


	/**
	 * @param array $ITERATION - массив с данными для пошагового чтения xml
	 * @return bool feof($this->_file)
	 */
	public function readYML(&$ITERATION) {
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		if(!array_key_exists("charset", $ITERATION)) {
			$ITERATION["charset"] = null;
		}
		$this->_fileCharset = &$ITERATION["charset"];

		if(!array_key_exists("element_stack", $ITERATION)) {
			$ITERATION["element_stack"] = array();
		}
		$this->_arElementStack = &$ITERATION["element_stack"];

		if(!array_key_exists("file_position", $ITERATION)) {
			$NS["file_position"] = 0;
		}
		$this->_filePosition = &$ITERATION["file_position"];

		$this->_buffer = '';
		$this->_bufferPosition = 0;
		$this->_bufferLength = 0;

		//This is an optimization. We assume than no step can take more than one year.
		if($this->_readTimeLimit > 0) {
			$end_time = time() + $this->_readTimeLimit;
		}
		else {
			$end_time = time() + 365*24*3600; // One year
		}

		fseek($this->_file, $this->_filePosition);
		$this->_fileCharset = 'windows-1251';
		while( $xmlChunk = $this->getChunk() ) {
			if( null !== $this->_fileCharset ) {
				if( $this->_fileCharset ) {
					$xmlChunk = $APPLICATION->ConvertCharset($xmlChunk, $this->_fileCharset, LANG_CHARSET);
				}
			}
			if($xmlChunk[0] == "/") {
				$this->endElement($xmlChunk);
				if(time() > $end_time) {
					break;
				}
			}
			elseif($xmlChunk[0] == "!" || $xmlChunk[0] == "?") {
				if(substr($xmlChunk, 0, 4) === "?xml") {
					if( preg_match('#encoding[\s]*=[\s]*"(.*?)"#i', $xmlChunk, $arMatch) ) {
						$this->charset = $arMatch[1];
						if(strtoupper($this->_fileCharset) === strtoupper(LANG_CHARSET)) {
							$this->_fileCharset = null;
						}
					}
				}
			}
			else {
				$this->storeChunk($xmlChunk);
			}
		}

		return feof($this->_file);
	}

	protected function getChunk() {
		if( $this->_bUTF ) {
			if( $this->_bMBStringOrig ) {
				return $this->_getChunk_mb_orig();
			}
			return $this->_getChunk_mb();
		}
		return $this->_getChunk();
	}

	protected function _getChunk_mb_orig() {
		if( $this->_bufferPosition >= $this->_bufferLength ) {
			if( !feof($this->_file) ) {
				$this->_buffer = fread($this->_file, $this->_chunkReadSize);
				$this->_bufferPosition = 0;
				$this->_bufferLength = mb_orig_strlen($this->_buffer);
			}
			else {
				return false;
			}
		}

		//Skip line delimiters (ltrim)
		$position = mb_orig_strpos($this->_buffer, "<", $this->_bufferPosition);
		while($position === $this->_bufferPosition) {
			$this->_bufferPosition++;
			$this->_filePosition++;
			//Buffer ended with white space so we can refill it
			if($this->_bufferPosition >= $this->_bufferLength) {
				if(!feof($this->_file))
				{
					$this->_buffer = fread($this->_file, $this->_chunkReadSize);
					$this->_bufferPosition = 0;
					$this->_bufferLength = mb_orig_strlen($this->_buffer);
				}
				else return false;
			}
			$position = mb_orig_strpos($this->_buffer, "<", $this->_bufferPosition);
		}

		//Let's find next line delimiter
		while($position===false)
		{
			$next_search = $this->_bufferLength;
			//Delimiter not in buffer so try to add more data to it
			if(!feof($this->_file))
			{
				$this->_buffer .= fread($this->_file, $this->_chunkReadSize);
				$this->_bufferLength = mb_orig_strlen($this->_buffer);
			}
			else break;

			//Let's find xml tag start
			$position = mb_orig_strpos($this->_buffer, "<", $next_search);
		}
		if($position===false)
			$position = $this->_bufferLength+1;

		$len = $position-$this->_bufferPosition;
		$this->_filePosition += $len;
		$result = mb_orig_substr($this->_buffer, $this->_bufferPosition, $len);
		$this->_bufferPosition = $position;

		return $result;
	}

	protected function _getChunk_mb() {
		if( $this->_bufferPosition >= $this->_bufferLength ) {
			if( !feof($this->_file) ) {
				$this->_buffer = fread($this->_file, $this->_chunkReadSize);
				$this->_bufferPosition = 0;
				$this->_bufferLength = mb_strlen($this->_buffer);
			}
			else {
				return false;
			}
		}

		//Skip line delimiters (ltrim)
		$position = mb_strpos($this->_buffer, "<", $this->_bufferPosition);
		while($position === $this->_bufferPosition) {
			$this->_bufferPosition++;
			$this->_filePosition++;
			//Buffer ended with white space so we can refill it
			if($this->_bufferPosition >= $this->_bufferLength) {
				if(!feof($this->_file))
				{
					$this->_buffer = fread($this->_file, $this->_chunkReadSize);
					$this->_bufferPosition = 0;
					$this->_bufferLength = mb_strlen($this->_buffer);
				}
				else return false;
			}
			$position = mb_strpos($this->_buffer, "<", $this->_bufferPosition);
		}

		//Let's find next line delimiter
		while($position===false)
		{
			$next_search = $this->_bufferLength;
			//Delimiter not in buffer so try to add more data to it
			if(!feof($this->_file))
			{
				$this->_buffer .= fread($this->_file, $this->_chunkReadSize);
				$this->_bufferLength = mb_strlen($this->_buffer);
			}
			else break;

			//Let's find xml tag start
			$position = mb_strpos($this->_buffer, "<", $next_search);
		}
		if($position===false)
			$position = $this->_bufferLength+1;

		$len = $position-$this->_bufferPosition;
		$this->_filePosition += $len;
		$result = mb_substr($this->_buffer, $this->_bufferPosition, $len);
		$this->_bufferPosition = $position;

		return $result;
	}

	protected function _getChunk() {
		if( $this->_bufferPosition >= $this->_bufferLength ) {
			if( !feof($this->_file) ) {
				$this->_buffer = fread($this->_file, $this->_chunkReadSize);
				$this->_bufferPosition = 0;
				$this->_bufferLength = strlen($this->_buffer);
			}
			else {
				return false;
			}
		}

		//Skip line delimiters (ltrim)
		$position = strpos($this->_buffer, "<", $this->_bufferPosition);
		while($position === $this->_bufferPosition) {
			$this->_bufferPosition++;
			$this->_filePosition++;
			//Buffer ended with white space so we can refill it
			if($this->_bufferPosition >= $this->_bufferLength) {
				if(!feof($this->_file))
				{
					$this->_buffer = fread($this->_file, $this->_chunkReadSize);
					$this->_bufferPosition = 0;
					$this->_bufferLength = strlen($this->_buffer);
				}
				else return false;
			}
			$position = strpos($this->_buffer, "<", $this->_bufferPosition);
		}

		//Let's find next line delimiter
		while($position===false)
		{
			$next_search = $this->_bufferLength;
			//Delimiter not in buffer so try to add more data to it
			if(!feof($this->_file))
			{
				$this->_buffer .= fread($this->_file, $this->_chunkReadSize);
				$this->_bufferLength = strlen($this->_buffer);
			}
			else break;

			//Let's find xml tag start
			$position = strpos($this->_buffer, "<", $next_search);
		}
		if($position===false)
			$position = $this->_bufferLength+1;

		$len = $position-$this->_bufferPosition;
		$this->_filePosition += $len;
		$result = substr($this->_buffer, $this->_bufferPosition, $len);
		$this->_bufferPosition = $position;

		return $result;
	}


	protected function storeChunk($xmlChunk) {
		static $arRegSearch = array(
			'~&(quot|#34);~i',
			'~&(lt|#60);~i',
			'~&(gt|#62);~i',
			'~&(amp|#38);~i',
		);
		static $arReplace = array('"', "<", ">", "&");

		$p = strpos($xmlChunk, ">");
		if($p !== false) {
			if(substr($xmlChunk, $p - 1, 1)=="/") {
				$bHaveChildren = false;
				$elementName = substr($xmlChunk, 0, $p-1);
				$DBelementValue = false;
			}
			else {
				$bHaveChildren = true;
				$elementName = substr($xmlChunk, 0, $p);
				$elementValue = substr($xmlChunk, $p+1);
				if(preg_match('/^\s*$/', $elementValue)) {
					$DBelementValue = false;
				}
				elseif(strpos($elementValue, "&")===false) {
					$DBelementValue = $elementValue;
				}
					
				else {
					$DBelementValue = preg_replace($arRegSearch, $arReplace, $elementValue);
				}
			}
			if(($ps = strpos($elementName, " "))!==false)
			{
				//Let's handle attributes
				$elementAttrs = substr($elementName, $ps+1);
				$elementName = substr($elementName, 0, $ps);
				preg_match_all("/(\\S+)\\s*=\\s*[\"](.*?)[\"]/s", $elementAttrs, $attrs_tmp);
				$attrs = array();
				if(strpos($elementAttrs, "&")===false) {
					foreach($attrs_tmp[1] as $i=>$attrs_tmp_1)
						$attrs[$attrs_tmp_1] = $attrs_tmp[2][$i];
				}
				else {
					foreach($attrs_tmp[1] as $i=>$attrs_tmp_1) {
						$attrs[$attrs_tmp_1] = preg_replace($arRegSearch, $arReplace, $attrs_tmp[2][$i]);
					}
				}
				$DBelementAttrs = serialize($attrs);
			}
			else {
				$DBelementAttrs = false;
			}

			if($c = count($this->_arElementStack)) {
				$parent = $this->_arElementStack[$c-1];
			}
			else {
				$parent = array("ID"=>"NULL", "L"=>0, "R"=>1);
			}

			$left = $parent["R"];
			$right = $left+1;

			$arFields = array(
				"PARENT_ID" => $parent["ID"],
				"LEFT_MARGIN" => $left,
				"RIGHT_MARGIN" => $right,
				"DEPTH_LEVEL" => $c,
				"NAME" => $elementName,
			);
			if($DBelementValue !== false) {
				$arFields["VALUE"] = $DBelementValue;
			}
			if($DBelementAttrs !== false) {
				$arFields["ATTRIBUTES"] = $DBelementAttrs;
			}

			$ID = $this->add($arFields);

			if($bHaveChildren) {
				$this->_arElementStack[] = array("ID"=>$ID, "L"=>$left, "R"=>$right, "RO"=>$right);
			}
			else {
				$this->_arElementStack[$c-1]["R"] = $right+1;
			}
		}

	}

	protected function endElement() {
		/** @global \CDatabase $DB */
		global $DB;
		$child = array_pop($this->_arElementStack);
		$this->_arElementStack[count($this->_arElementStack)-1]["R"] = $child["R"]+1;
		if($child["R"] != $child["RO"])
			$DB->Query("UPDATE ".$this->_tempTableName." SET RIGHT_MARGIN = ".intval($child["R"])." WHERE ID = ".intval($child["ID"]));
	}

}

