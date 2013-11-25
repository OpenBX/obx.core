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

class RequestBXFile extends Request {

	const F_IB_IMG_PREVIEW = 1;
	const F_IB_IMG_DETAIL = 2;
	const F_IB_IMG_PROP_REPLACE = 3;
	const F_IB_IMG_PROP_APPEND = 4;

	/**
	 * @param string $relUploadDirPath
	 * @param int $oldFileID
	 * @throws Exceptions\RequestError
	 * @return int
	 */
	public function saveToBXFile($relUploadDirPath, $oldFileID = null) {
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
		if($this->_bDownloadComplete) {
			$arFile = \CFile::MakeFileArray($this->_dwnFolder.'/'.$this->_dwnName.'.'.static::DOWNLOAD_FILE_EXT);
			$fileID = \CFile::SaveFile($arFile, $relUploadDirPath);
		}
		elseif($this->_bRequestComplete) {

		}
		return $fileID;
	}

	public function saveToIBElement($IBLOCK, $target = self::F_IB_IMG_DETAIL) {

	}

	public function saveToIBProp($IBLOCK, $propID) {

	}
}