<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core;

class Mime {

	const GRP_UNKNOWN = null;
	const GRP_TEXT = 'text';
	const GRP_IMAGE = 'image';
	const GRP_ARCH = 'archive';
	const GRP_DOC = 'document';
	const GRP_AUDIO = 'audio';
	const GRP_VIDEO = 'video';

	static protected $_arMimeExt = null;
	static protected $_arMimeGroups = null;
	static protected $_arMimeText = array(
		'application/json' => 'json',
		'text/html' => 'html',
		'text/plain' => 'txt',
		'text/xml' => 'xml',
	);

	static protected $_arMimeImages = array(
		'image/png' => 'png',
		'image/jpeg' => 'jpg',
		'image/gif' => 'gif',
		'image/x-icon' => 'ico',
		'image/x-tiff' => 'tiff',
		'image/tiff' => 'tiff',
		'image/svg+xml' => 'svg',
		'application/pcx' => 'pcx',
		'image/x-bmp' => 'bmp',
		'image/x-MS-bmp' => 'bmp',
		'image/x-ms-bmp' => 'bmp',
	);

	static protected $_arMimeCompressedTypes = array(
		'application/x-rar-compressed' => 'rar',
		'application/x-rar' => 'rar',
		'application/x-tar' => 'tar',
		'application/x-bzip2' => 'bz2',
		'application/x-bzip-compressed-tar' => 'tar.bz2',
		'application/x-bzip2-compressed-tar' => 'tar.bz2',
		'application/zip' => 'zip',
		'application/x-7z-compressed' => '7z',
		'application/x-gzip' => 'gz',
		'application/x-gzip-compressed-tar' => 'tar.gz',
		'application/x-xz' => 'xz',
	);



	static protected $_arMimeDocuments = array(
		//doc
		//open docs
		'application/vnd.oasis.opendocument.text' => 'odt',
		'application/vnd.oasis.opendocument.spreadsheet' => 'pds',
		'application/vnd.oasis.opendocument.presentation' => 'odp',
		'application/vnd.oasis.opendocument.graphics' => 'odg',
		'application/vnd.oasis.opendocument.chart' => 'odc',
		'application/vnd.oasis.opendocument.formula' => 'odf',
		'application/vnd.oasis.opendocument.image' => 'odi',
		'application/vnd.oasis.opendocument.text-master' => 'odm',
		'application/vnd.oasis.opendocument.text-template' => 'ott',
		'application/vnd.oasis.opendocument.spreadsheet-template' => 'ots',
		'application/vnd.oasis.opendocument.presentation-template' => 'otp',
		'application/vnd.oasis.opendocument.graphics-template' => 'otg',
		'application/vnd.oasis.opendocument.chart-template' => 'otc',
		'application/vnd.oasis.opendocument.formula-template' => 'otf',
		'application/vnd.oasis.opendocument.image-template' => 'oti',
		'application/vnd.oasis.opendocument.text-web' => 'oth',
		//prop docs
		'application/rtf' => 'rtf',
		'application/pdf' => 'pdf',
		'application/postscript' => 'ps',
		'application/x-dvi' => 'dvi',
		'application/msword' => 'doc',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
		'application/vnd.ms-powerpoint' => 'ppt',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		'application/vnd.ms-excel' => 'xls',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
	);

	static protected $_arMimeAudio = array(
		'audio/midi' => 'midi',
		'audio/x-midi' => 'midi',
		'audio/mod' => 'mod',
		'audio/x-mod' => 'mod',
		'audio/mpeg3' => 'mp3',
		'audio/x-mpeg3' => 'mp3',
		'audio/mpeg-url' => 'mp3',
		'audio/x-mpeg-url' => 'mp3',
		'audio/mpeg2' => 'mp2',
		'audio/x-mpeg2' => 'mp2',
		'audio/mpeg' => 'mpa',
		'audio/x-mpeg' => 'mpa',
		'audio/wav' => 'wav',
		'audio/x-wav' => 'wav',
		'audio/flac' => 'flac',
		'audio/x-ogg' => 'ogg'
	);

	static protected $_arMimeVideo = array(
		'video/mpeg' => 'mpg',
		'video/x-mpeg' => 'mpg',
		'video/sgi-movie' => 'movi',
		'video/x-sgi-movie' => 'movi',
		'video/msvideo' => 'avi',
		'video/x-msvideo' => 'avi',
		'video/fli' => 'fli',
		'video/x-fli' => 'fli',
		'video/quicktime' => 'mov',
		'video/x-quicktime' => 'mov',
		'application/x-shockwave-flash' => 'swf',
		'video/x-ms-wmv' => 'wmv',
		'video/x-ms-asf' => 'asf',
	);


	static public function _init() {
		if( null === static::$_arMimeExt ) {
			static::$_arMimeExt = array_merge(
				static::$_arMimeText,
				static::$_arMimeImages,
				static::$_arMimeCompressedTypes,
				static::$_arMimeDocuments,
				static::$_arMimeAudio,
				static::$_arMimeVideo
			);
		}
		if( null === static::$_arMimeGroups ) {
			static::$_arMimeGroups = array();
			foreach(static::$_arMimeText as $type => $ext) {
				static::$_arMimeGroups[$type] = static::GRP_TEXT;
			}
			foreach(static::$_arMimeImages as $type => $ext) {
				static::$_arMimeGroups[$type] = static::GRP_IMAGE;
			}
			foreach(static::$_arMimeCompressedTypes as $type => $ext) {
				static::$_arMimeGroups[$type] = static::GRP_ARCH;
			}
			foreach(static::$_arMimeDocuments as $type => $ext) {
				static::$_arMimeGroups[$type] = static::GRP_DOC;
			}
			foreach(static::$_arMimeAudio as $type => $ext) {
				static::$_arMimeGroups[$type] = static::GRP_AUDIO;
			}
			foreach(static::$_arMimeVideo as $type => $ext) {
				static::$_arMimeGroups[$type] = static::GRP_VIDEO;
			}
		}
	}

	static public function & _refMimeData() {
		return static::$_arMimeExt;
	}

	static public function getMimeData() {
		return static::$_arMimeExt;
	}

	/**
	 * @param string $type
	 * @param string $fileExt
	 * @param int|null $group
	 * @return bool
	 */
	static public function addType($type, $fileExt, $group = null) {
		if( array_key_exists($type, static::$_arMimeExt) ) {
			return false;
		}
		static::$_arMimeExt[$type] = $fileExt;
		if(null !== $group) {
			switch($group) {
				case static::GRP_TEXT:
				case static::GRP_IMAGE:
				case static::GRP_ARCH:
				case static::GRP_DOC:
				case static::GRP_AUDIO:
				case static::GRP_VIDEO:
				static::$_arMimeGroups[$type] = $group;
			}
		}
		return true;
	}

	/**
	 * @param string $mimeType
	 * @param null|string $defaultExt
	 * @return null|string
	 */
	static public function getFileExt($mimeType, $defaultExt = null) {
		if(array_key_exists($mimeType, static::$_arMimeExt)) {
			return static::$_arMimeExt[$mimeType];
		}
		return $defaultExt;
	}

	static public function getContentGroup($mimeType) {
		if(array_key_exists($mimeType, static::$_arMimeGroups)) {
			return static::$_arMimeGroups[$mimeType];
		}
		return static::GRP_UNKNOWN;
	}
}
Mime::_init();