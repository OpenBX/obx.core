<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Curl;
use OBX\Core\Exceptions\Curl\RequestError;
use OBX\Core\Tools;

class MultiRequestBXFile extends MultiRequest {
	const F_IB_IMG_PROP_APPEND = RequestBXFile::F_IB_IMG_PROP_APPEND;
	const F_IB_IMG_PROP_REPLACE = RequestBXFile::F_IB_IMG_PROP_REPLACE;

	protected $_multiDwnName = null;
	protected $_multiDwnFolder = null;

	public function __destruct() {
		parent::__destruct();
		if($this->_multiDwnName !== null) {
			DeleteDirFilesEx(OBX_DOC_ROOT.$this->_multiDwnFolder);
		}
	}

	static public function generateMultiDownloadName() {
		return md5(__CLASS__.time().'_'.rand(0, 9999));
	}

	public function addUrl($url) {
		try {
			$Request = new RequestBXFile($url);
			$bSuccess = $this->addRequest($Request);
		}
		catch(RequestError $e) {
			$this->addError($e->getMessage(), $e->getCode());
			return false;
		}
		return $bSuccess;
	}

	public function saveToIBProp($iblockID, $elementID, $propCode, $action = self::F_IB_IMG_PROP_APPEND) {
		if(true !== $this->_bRequestsComplete && true !== $this->_bDownloadsComplete) {
			return false;
		}
		$arProp = array();
		$arErr = array();
		if(is_numeric($propCode)) {
			$propID = intval($propCode);
			$propCode = Tools::getPropCodeById($iblockID, $propID, $arProp, $arErr);
			if($propCode === false) {
				return false;
			}
		}
		else {
			$propID = Tools::getPropIdByCode($iblockID, $propCode, $arProp, $arErr);
			if($propID === false) {
				return false;
			}
		}
		if(!empty($arErr)) {
			return false;
		}
		if($arProp['PROPERTY_TYPE'] != 'F') {
			return false;
		}
		if($arProp['MULTIPLE'] != 'Y') {
			return false;
		}

		$this->_multiDwnName = self::generateMultiDownloadName();
		$this->_multiDwnFolder = Request::DOWNLOAD_FOLDER.'/'.$this->_multiDwnName;
		if( !CheckDirPath(OBX_DOC_ROOT.$this->_multiDwnFolder) ) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		$this->saveToDir($this->_multiDwnFolder, Request::SAVE_TO_DIR_COUNT);
		$arFileList = array();
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			if( $Request->isDownloadSuccess() ) {
				$relFilePath = $Request->getSavedFilePath(true);
				if(!empty($relFilePath)) {
					$arFileList[] = \CFile::MakeFileArray($relFilePath);
				}
			}
			else {

			}
		}

		if(!empty($arFileList)) {
			switch($action) {
				case self::F_IB_IMG_PROP_REPLACE:
					\CIBlockElement::SetPropertyValuesEx($elementID, $iblockID, array($arProp['ID'] => $arFileList));
					break;
				case self::F_IB_IMG_PROP_APPEND:
					\CIBlockElement::SetPropertyValues($elementID, $iblockID, $arFileList, $arProp['ID']);
					break;
			}
		}
		return true;
	}
} 