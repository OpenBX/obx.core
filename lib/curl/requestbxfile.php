<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core\Curl;
use OBX\Core\Exceptions\Curl\RequestError;
use OBX\Core\Tools;

class RequestBXFile extends Request {

	const F_IB_IMG_PREVIEW = 1;
	const F_IB_IMG_DETAIL = 2;
	const F_IB_IMG_BOTH = 3;
	const F_IB_IMG_PROP_REPLACE = 4;
	const F_IB_IMG_PROP_APPEND = 5;

	/**
	 * Возвращает -1 в случае ошибки кода битркс и кдиает исключение в случае ошибки obx.core
	 * -1 - очень маловероятная ситуация
	 * @param string $relUploadDirPath
	 * @param int $oldFileID
	 * @param string $description
	 * @param string $MODULE_ID
	 * @throws RequestError
	 * @return int
	 */
	public function saveToBXFile($relUploadDirPath, $oldFileID = 0, $description = '', $MODULE_ID = null) {
		$relUploadDirPath = str_replace(array('\\', '//'), '/', $relUploadDirPath);
		$relUploadDirPath = trim($relUploadDirPath, '/');
		if( strpos($relUploadDirPath, 'upload/') === 0 ) {
			$relUploadDirPath = substr($relUploadDirPath, 7);
		}
		$relPath = '/upload/'.$relUploadDirPath;
		$path = OBX_DOC_ROOT.$relPath;
		if(!CheckDirPath($path)) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		$fileID = -1;
		$oldFileID = intval($oldFileID);
		if($this->_bDownloadSuccess || $this->_bRequestSuccess) {
			if( !CheckDirPath(OBX_DOC_ROOT.static::DOWNLOAD_FOLDER.'/'.$this->_ID) ) {
				throw new RequestError('', RequestError::E_PERM_DENIED);
			}
			$downloadFileRelPath = $this->getDownloadFilePath(false);
			if($this->_bDownloadSuccess) {
				$arFile = \CFile::MakeFileArray($downloadFileRelPath);
			}
			elseif($this->_bRequestSuccess) {
				$this->saveToFile($downloadFileRelPath);
				$this->_saveFileName = null;
				$this->_saveRelPath = null;
				$this->_savePath = null;
				$arFile = \CFile::MakeFileArray($downloadFileRelPath);
			}
			$arFile['name'] = $this->_originalName.'.'.$this->_originalExt;
			if($oldFileID>0) {
				$arFile['old_file'] = $oldFileID;
			}
			if(is_string($description) && !empty($description)) {
				$arFile['description'] = $description;
			}
			if( null !== $MODULE_ID && IsModuleInstalled($MODULE_ID) ) {
				$arFile['MODULE_ID'] = $MODULE_ID;
			}
			$fileID = \CFile::SaveFile($arFile, $relUploadDirPath);
		}
		return $fileID;
	}



	/**
	 * @param int $elementID
	 * @param int $target
	 * @param string $description
	 * @return bool
	 * @throws RequestError
	 */
	public function saveToIBElement($elementID, $target = self::F_IB_IMG_DETAIL, $description = '') {
		if(true !== $this->_bRequestSuccess && true !== $this->_bDownloadSuccess) {
			return false;
		}
		if($this->_ID === null) {
			$this->_ID = static::generateID();
		}
		if( !CheckDirPath(OBX_DOC_ROOT.static::DOWNLOAD_FOLDER.'/'.$this->_ID) ) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		$downloadFileRelPath = $this->getDownloadFilePath(false);
		if($this->_bDownloadSuccess) {
			$arFile = \CFile::MakeFileArray($downloadFileRelPath);
		}
		elseif($this->_bRequestSuccess) {
			$this->saveToFile($downloadFileRelPath);
			$this->_saveFileName = null;
			$this->_saveRelPath = null;
			$this->_savePath = null;
			$arFile = \CFile::MakeFileArray($downloadFileRelPath);
		}
		$arFile['name'] = $this->_originalName.'.'.$this->_originalExt;
		if(is_string($description) && !empty($description)) {
			$arFile['description'] = $description;
		}
		$el = new \CIBlockElement();
		$arFields = array();
		if($target === self::F_IB_IMG_PREVIEW) {
			$arFields['PREVIEW_PICTURE'] = $arFile;
		}
		elseif($target === self::F_IB_IMG_DETAIL) {
			$arFields['DETAIL_PICTURE'] = $arFile;
		}
		elseif($target === self::F_IB_IMG_BOTH) {
			$arFields['PREVIEW_PICTURE'] = $arFile;
			$arFields['DETAIL_PICTURE'] = $arFile;
		}
		return $el->Update($elementID, $arFields);
	}

	/**
	 * @param int $iblockID
	 * @param int $elementID
	 * @param int|string $propCode
	 * @param int $action
	 * @param string $description
	 * @throws RequestError
	 * @return bool
	 */
	public function saveToIBProp($iblockID, $elementID, $propCode, $action = self::F_IB_IMG_PROP_APPEND, $description = '') {
		if(true !== $this->_bRequestSuccess && true !== $this->_bDownloadSuccess) {
			return false;
		}

		$arProp = array();
		$arErr = array();

		if(is_numeric($propCode)) {
			$propID = intval($propCode);
			$propCode = Tools::getPropCodeById($iblockID, $propID, $arProp, $arErr);
			if($propCode === false) {
				throw new RequestError('', RequestError::E_BX_FILE_PROP_NOT_FOUND);
			}
		}
		else {
			$propID = Tools::getPropIdByCode($iblockID, $propCode, $arProp, $arErr);
			if($propID === false) {
				throw new RequestError('', RequestError::E_BX_FILE_PROP_NOT_FOUND);
			}
		}
		if($arProp['PROPERTY_TYPE'] != 'F') {
			throw new RequestError('', RequestError::E_BX_FILE_PROP_WRONG_TYPE);
		}

		if($this->_ID === null) {
			$this->_ID = static::generateID();
		}
		if( !CheckDirPath(OBX_DOC_ROOT.static::DOWNLOAD_FOLDER.'/'.$this->_ID) ) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		$downloadFileRelPath = $this->getDownloadFilePath(false);
		if($this->_bDownloadSuccess) {
			$arFile = \CFile::MakeFileArray($downloadFileRelPath);
		}
		elseif($this->_bRequestSuccess) {
			$this->saveToFile($downloadFileRelPath);
			$arFile = \CFile::MakeFileArray($downloadFileRelPath);
		}
		$arFile['name'] = $this->_originalName.'.'.$this->_originalExt;
		if(is_string($description) && !empty($description)) {
			$arFile['description'] = $description;
		}

		switch($action) {
			case self::F_IB_IMG_PROP_REPLACE:
				\CIBlockElement::SetPropertyValuesEx($elementID, $iblockID, array($arProp['ID'] => $arFile));
				break;
			case self::F_IB_IMG_PROP_APPEND:
				\CIBlockElement::SetPropertyValues($elementID, $iblockID, $arFile, $arProp['ID']);
				break;
		}
		return true;
	}
}