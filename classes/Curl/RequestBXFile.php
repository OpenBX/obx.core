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
use OBX\Core\Curl\Exceptions\RequestError;
use OBX\Core\Tools;

class RequestBXFile extends Request {

	const F_IB_IMG_PREVIEW = 1;
	const F_IB_IMG_DETAIL = 2;
	const F_IB_IMG_PROP_REPLACE = 3;
	const F_IB_IMG_PROP_APPEND = 4;

	/**
	 * Возвращает -1 в случае ошибки кода битркс и кдиает исключение в случае ошибки obx.core
	 * -1 - очень маловероятная ситуация
	 * @param string $relUploadDirPath
	 * @param int $oldFileID
	 * @param string $description
	 * @param string $MODULE_ID
	 * @throws Exceptions\RequestError
	 * @return int
	 */
	public function saveToBXFile($relUploadDirPath, $oldFileID = 0, $description = '', $MODULE_ID = null) {
		$relUploadDirPath = str_replace(array('\\', '//'), '/', $relUploadDirPath);
		$relUploadDirPath = trim($relUploadDirPath, '/');
		if( strpos($relUploadDirPath, 'upload/') === 0 ) {
			$relUploadDirPath = substr($relUploadDirPath, 7);
		}
		$relPath = '/upload/'.$relUploadDirPath;
		$path = $_SERVER['DOCUMENT_ROOT'].$relPath;
		if(!CheckDirPath($path)) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		$fileID = -1;
		$oldFileID = intval($oldFileID);
		if($this->_bDownloadComplete || $this->_bRequestComplete) {
			if($this->_dwnName === null) {
				$this->_dwnName = static::generateDownloadName();
			}
			if( !CheckDirPath($_SERVER['DOCUMENT_ROOT'].static::DOWNLOAD_FOLDER.'/'.$this->_dwnName) ) {
				throw new RequestError('', RequestError::E_PERM_DENIED);
			}
			$saveDirFolder = static::DOWNLOAD_FOLDER.'/'.$this->_dwnName;
			$saveFileRelPath = $saveDirFolder.'/'.$this->_originalName.'.'.$this->_originalExt;
			$this->saveToFile($saveFileRelPath);
			$arFile = \CFile::MakeFileArray($saveFileRelPath);
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
		static $arAcceptTypes = array(
			'image/png' => 'png',
			'image/jpeg' => 'jpg',
			'image/gif' => 'gif'
		);
		if(true !== $this->_bRequestComplete && true !== $this->_bDownloadComplete) {
			return false;
		}
		$contentType = $this->getContentType();
		if( !array_key_exists($contentType, $arAcceptTypes) ) {
			return false;
		}

		if($this->_dwnName === null) {
			$this->_dwnName = static::generateDownloadName();
		}
		if( !CheckDirPath($_SERVER['DOCUMENT_ROOT'].static::DOWNLOAD_FOLDER.'/'.$this->_dwnName) ) {
			throw new RequestError('', RequestError::E_PERM_DENIED);
		}
		$saveDirFolder = static::DOWNLOAD_FOLDER.'/'.$this->_dwnName;
		$saveFileRelPath = $saveDirFolder.'/'.$this->_originalName.'.'.$this->_originalExt;
		$this->saveToFile($saveFileRelPath);
		$arFile = \CFile::MakeFileArray($saveFileRelPath);
		if(is_string($description) && !empty($description)) {
			$arFile['description'] = $description;
		}
		$el = new \CIBlockElement();
		$arFields = array();
		if($target === self::F_IB_IMG_PREVIEW) {
			$arFile['DETAIL_PICTURE'] = $arFile;
		}
		else {
			$arFile['PREVIEW_PICTURE'] = $arFile;
		}
		return $el->Update($elementID, $arFields);

	}

	/**
	 * @param int $iblockID
	 * @param int $elementID
	 * @param int|string $propCode
	 * @param int $action
	 * @param string $description
	 * @return bool
	 */
	public function saveToIBProp($iblockID, $elementID, $propCode, $action = self::F_IB_IMG_PROP_APPEND, $description = '') {
		if(true !== $this->_bRequestComplete && true !== $this->_bDownloadComplete) {
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

		$fileID = $this->saveToBXFile('iblock', 0, $description, 'iblock');
		if($fileID < 1) {
			return false;
		}

		$arPropValues = array();
		if($action == self::F_IB_IMG_PROP_APPEND && $arProp['MULTIPLE'] == 'Y') {
			$rsPropValues = \CIBlockElement::GetProperty($iblockID, $elementID, $by = 'sort', $order = 'asc', array(
				'ID' => $arProp['ID']
			));
			$arPropValues = array();
			if($arValue = $rsPropValues->Fetch()) {
				$arPropValues[$arValue['ID']] = $arValue['VALUE'];
				if(!is_array($arPropValues[$arValue['ID']])) {
					$arPropValues[$arValue['ID']] = array($arPropValues[$arValue['ID']]);
				}
				$arPropValues[$arValue['ID']][] = $fileID;
			}
			else {
				$arPropValues[$arProp['ID']] = $fileID;
			}
		}
		else {
			$arPropValues[$arProp['ID']] = $fileID;
		}
		\CIBlockElement::SetPropertyValuesEx($elementID, $iblockID, $arPropValues);
		return true;
	}
}