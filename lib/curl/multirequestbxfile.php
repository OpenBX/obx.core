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
use OBX\Core\Exceptions\Curl\CurlError;
use OBX\Core\Exceptions\Curl\RequestError;
use OBX\Core\Tools;

class MultiRequestBXFile extends MultiRequest {
	const F_IB_IMG_PROP_APPEND = RequestBXFile::F_IB_IMG_PROP_APPEND;
	const F_IB_IMG_PROP_REPLACE = RequestBXFile::F_IB_IMG_PROP_REPLACE;

	public function __destruct() {
		parent::__destruct();
//		if($this->_multiDwnName !== null) {
//			DeleteDirFilesEx($this->_multiDwnFolder);
//		}
	}

	static public function generateID() {
		return md5(__CLASS__.time().'_'.rand(0, 9999));
	}

	public function addUrl($url, $requestID = null) {
		try {
			$Request = new RequestBXFile($url, $requestID);
			$bSuccess = $this->addRequest($Request);
		}
		catch(CurlError $e) {
			$this->addError($e->getMessage(), CurlError::ID.$e->getCode());
			return false;
		}
		catch(RequestError $e) {
			$this->addError($e->getMessage(), RequestError::ID.$e->getCode());
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

		$arFileList = array();
		/** @var Request $Request */
		foreach($this->_arRequestList as $Request) {
			if( $Request->isDownloadSuccess() ) {
				$downloadFileRelPath = $Request->getDownloadFilePath(false);
				$arFile = \CFile::MakeFileArray($downloadFileRelPath);
				if(!empty($arFile)) {
					$arFileList[] = $arFile;
				}
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