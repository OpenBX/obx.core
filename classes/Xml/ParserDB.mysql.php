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

class ParserDB {
	const DEFAULT_TEMP_TBL_NAME = 'obx_tmp_xml_parser_tree';
	protected $_tempTableName = self::DEFAULT_TEMP_TBL_NAME;
	protected $_bTempDatabaseCreated = false;
	protected $_sessionID = '';
	protected $_bUseSessionIDIntTempTable = false;

	function startSession($sess_id) {
		global $DB;
		if(!$DB->TableExists($this->_tempTableName)) {
			$res = $this->createTempTables(true);
			if($res) {
				$res = $this->indexTempTables(true);
			}
		}
		else {
			$res = true;
		}
		if($res) {
			$this->_sessionID = substr($sess_id, 0, 32);
			$rs = $this->getList(array(), array("PARENT_ID" => -1), array("ID", "NAME"));
			if(!$rs->Fetch()) {
				$this->add(array(
					"PARENT_ID" => -1,
					"LEFT_MARGIN" => 0,
					"NAME" => "SESS_ID",
					"VALUE" => ConvertDateTime(ConvertTimeStamp(false, "FULL"), "YYYY-MM-DD HH:MI:SS"),
				));
			}
		}
		return $res;
	}

	function GetSessionRoot() {
		global $DB;
		$rs = $DB->Query("SELECT ID MID from ".$this->_tempTableName." WHERE SESS_ID = '".$DB->ForSQL($this->_sessionID)."' AND PARENT_ID = 0");
		$ar = $rs->Fetch();
		return $ar["MID"];
	}

	function endSession() {
		global $DB;
		//Delete "expired" sessions
		$expired = ConvertDateTime(ConvertTimeStamp(time()-3600, "FULL"), "YYYY-MM-DD HH:MI:SS");
		$rs = $DB->Query("select ID, SESS_ID, VALUE from ".$this->_tempTableName." where PARENT_ID = -1 AND NAME = 'SESS_ID' ORDER BY ID");
		while( $ar = $rs->Fetch() ) {
			if($ar["SESS_ID"] == $this->_sessionID || $ar["VALUE"] < $expired) {
				$DB->Query("DELETE from ".$this->_tempTableName." WHERE SESS_ID = '".$DB->ForSQL($ar["SESS_ID"])."'");
			}
		}
		return true;
	}

	static public function checkTableName($tableName) {
		if( preg_match('~[a-zA-Z0-9]{1}[a-zA-Z0-9\_]{0,30}~', $tableName)) {
			return true;
		}
		return false;
	}

	public function createTempTables($tableName = null, $bWithSessID = false) {
		if(null !== $tableName ) {
			if( self::checkTableName($tableName) ) {
				$this->_tempTableName = $tableName;
			}
			else {
				throw new ParserError(GetMessage('OBX\Core\Xml\Exceptions\ParserError::TMP_TBL_WRONG_NAME'), ParserError::TMP_TBL_WRONG_NAME);
			}
		}

		/** @global \CDatabase $DB */
		global $DB;

		if(defined("MYSQL_TABLE_TYPE") && strlen(MYSQL_TABLE_TYPE) > 0) {
			$DB->Query("SET storage_engine = '".MYSQL_TABLE_TYPE."'", true);
		}

		if($DB->TableExists($this->_tempTableName)) {
			throw new ParserError(GetMessage('OBX\Core\Xml\Exceptions\ParserError::TMP_TBL_EXISTS'), ParserError::TMP_TBL_EXISTS);
		}

		$res = $DB->Query("create table ".$this->_tempTableName."
				(
					ID int(11) not null auto_increment,
					".($bWithSessID? "SESS_ID varchar(32),": "")."
					PARENT_ID int(11),
					LEFT_MARGIN int(11),
					RIGHT_MARGIN int(11),
					DEPTH_LEVEL int(11),
					NAME varchar(255),
					VALUE text,
					ATTRIBUTES text,
					PRIMARY KEY (ID)
				)
			");
		$this->_bUseSessionIDIntTempTable = ($bWithSessID)?true:false;
		return $res;
	}

	public function getTempTableName() {
		return $this->_tempTableName;
	}

	public function dropTempTables() {
		global $DB;
		if($DB->TableExists($this->_tempTableName)) {
			return $DB->Query("drop table ".$this->_tempTableName);
		}
		else return true;
	}

	public function indexTempTables() {
		global $DB;
		$res = true;
		if($this->_bUseSessionIDIntTempTable) {
			if(!$DB->IndexExists($this->_tempTableName, array("SESS_ID", "PARENT_ID")))
				$res = $DB->Query("CREATE INDEX ix_".$this->_tempTableName."_parent on ".$this->_tempTableName."(SESS_ID, PARENT_ID)");
			if($res && !$DB->IndexExists($this->_tempTableName, array("SESS_ID", "LEFT_MARGIN")))
				$res = $DB->Query("CREATE INDEX ix_".$this->_tempTableName."_left on ".$this->_tempTableName."(SESS_ID, LEFT_MARGIN)");
		}
		else {
			if(!$DB->IndexExists($this->_tempTableName, array("PARENT_ID")))
				$res = $DB->Query("CREATE INDEX ix_".$this->_tempTableName."_parent on ".$this->_tempTableName."(PARENT_ID)");
			if($res && !$DB->IndexExists($this->_tempTableName, array("LEFT_MARGIN")))
				$res = $DB->Query("CREATE INDEX ix_".$this->_tempTableName."_left on ".$this->_tempTableName."(LEFT_MARGIN)");
		}
		return $res;
	}

	public function add($arFields) {
		global $DB;
		$strSql1 = "PARENT_ID, LEFT_MARGIN, RIGHT_MARGIN, DEPTH_LEVEL, NAME";
		$strSql2 = intval($arFields["PARENT_ID"]).", ".intval($arFields["LEFT_MARGIN"]).", ".intval($arFields["RIGHT_MARGIN"]).", ".intval($arFields["DEPTH_LEVEL"]).", '".$DB->ForSQL($arFields["NAME"], 255)."'";
		if(array_key_exists("ATTRIBUTES", $arFields)) {
			$strSql1 .= ", ATTRIBUTES";
			$strSql2 .= ", '".$DB->ForSQL($arFields["ATTRIBUTES"])."'";
		}
		if(array_key_exists("VALUE", $arFields)) {
			$strSql1 .= ", VALUE";
			$strSql2 .= ", '".$DB->ForSQL($arFields["VALUE"])."'";
		}
		if($this->_sessionID) {
			$strSql1 .= ", SESS_ID";
			$strSql2 .= ", '".$DB->ForSQL($this->_sessionID)."'";
		}
		$strSql = "INSERT INTO ".$this->_tempTableName." (".$strSql1.") VALUES (".$strSql2.")";
		$DB->Query($strSql);
		return $DB->LastID();
	}

	public function delete($ID) {
		/** @global \CDatabase $DB */
		global $DB;
		return $DB->Query("delete from ".$this->_tempTableName." where ID = ".intval($ID));
	}

	public function getAllChildrenArray($arParent)
	{
		//We will return
		$arResult = array();

		//So we get not parent itself but xml_id
		if( !is_array($arParent) ) {
			$rs = $this->getList(
				array(),
				array("ID" => $arParent),
				array("ID", "LEFT_MARGIN", "RIGHT_MARGIN")
			);
			$arParent = $rs->Fetch();
			if(!$arParent) {
				return $arResult;
			}
		}

		//Array of the references to the arResult array members with xml_id as index.
		$arSalt = array();
		$arIndex = array();
		$rs = $this->getList(
			array("ID" => "asc"),
			array("><LEFT_MARGIN" => array($arParent["LEFT_MARGIN"]+1, $arParent["RIGHT_MARGIN"]-1))
		);
		while($ar = $rs->Fetch()) {
			if(isset($ar["VALUE_CLOB"]))
				$ar["VALUE"] = $ar["VALUE_CLOB"];

			if(isset($arSalt[$ar["PARENT_ID"]][$ar["NAME"]]))
			{
				$salt = ++$arSalt[$ar["PARENT_ID"]][$ar["NAME"]];
				$ar["NAME"] .= $salt;
			}
			else
			{
				$arSalt[$ar["PARENT_ID"]][$ar["NAME"]] = 0;
			}

			if($ar["PARENT_ID"] == $arParent["ID"])
			{
				$arResult[$ar["NAME"]] = $ar["VALUE"];
				$arIndex[$ar["ID"]] = &$arResult[$ar["NAME"]];
			}
			else
			{
				$parent_id = $ar["PARENT_ID"];
				if(!is_array($arIndex[$parent_id]))
					$arIndex[$parent_id] = array();
				$arIndex[$parent_id][$ar["NAME"]] = $ar["VALUE"];
				$arIndex[$ar["ID"]] = &$arIndex[$parent_id][$ar["NAME"]];
			}
		}

		return $arResult;
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param array $arSelect
	 * @return \CDBResult
	 */
	public function getList($arOrder = array(), $arFilter = array(), $arSelect = array()) {
		/** @global \CDatabase $DB */
		global $DB;
		static $arFields = array(
			"ID" => "ID",
			"ATTRIBUTES" => "ATTRIBUTES",
			"LEFT_MARGIN" => "LEFT_MARGIN",
			"RIGHT_MARGIN" => "RIGHT_MARGIN",
			"NAME" => "NAME",
			"VALUE" => "VALUE",
		);
		foreach($arSelect as $i => $field) {
			if(!array_key_exists($field, $arFields)) {
				unset($arSelect[$i]);
			}
		}

		if(count($arSelect) <= 0) {
			$arSelect[] = "*";
		}

		$arSQLWhere = array();
		foreach($arFilter as $field => $value)
		{
			if($field == "ID" || $field == "LEFT_MARGIN")
				$arSQLWhere[$field] = $field." = ".intval($value);
			elseif($field == "PARENT_ID" || $field == "PARENT_ID+0")
				$arSQLWhere[$field] = $field." = ".intval($value);
			elseif($field == ">ID")
				$arSQLWhere[$field] = "ID > ".intval($value);
			elseif($field == "><LEFT_MARGIN")
				$arSQLWhere[$field] = "LEFT_MARGIN between ".intval($value[0])." AND ".intval($value[1]);
			elseif($field == "NAME")
				$arSQLWhere[$field] = $field." = "."'".$DB->ForSQL($value)."'";
		}
		if($this->_sessionID)
			$arSQLWhere[] = "SESS_ID = '".$DB->ForSQL($this->_sessionID)."'";

		foreach($arOrder as $field => $by)
		{
			if(!array_key_exists($field, $arFields)) {
				unset($arSelect[$field]);
			}
			else {
				$arOrder[$field] = $field." ".($by=="desc"? "desc": "asc");
			}
		}

		$strSql = "
			select
				".implode(", ", $arSelect)."
			from
				".$this->_tempTableName."
			".(count($arSQLWhere)? "where (".implode(") and (", $arSQLWhere).")": "")."
			".(count($arOrder)? "order by  ".implode(", ", $arOrder): "")."
		";

		return $DB->Query($strSql);
	}
}