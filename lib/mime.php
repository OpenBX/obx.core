<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;

class Mime {

	const GRP_UNKNOWN = null;
	const GRP_TEXT = 'text';
	const GRP_IMAGE = 'image';
	const GRP_ARCH = 'archive';
	const GRP_DOC = 'document';
	const GRP_AUDIO = 'audio';
	const GRP_VIDEO = 'video';
	const GRP_OTHER = 'other';

	private static $arInstances = array();

	protected $arMimeExt = null;
	protected $arMimeGroups = null;
	protected $arMimeText = array(
		'application/json' => 'json',
		'text/html' => 'html',
		'text/plain' => 'txt',
		'application/xml' => 'xml',
		'text/xml' => 'xml',
	);

	protected $arMimeImages = array(
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

	protected $arMimeCompressedTypes = array(
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
		'application/x-iso9660-image' => 'iso'
	);



	protected $arMimeDocuments = array(
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

	protected $arMimeAudio = array(
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

	protected $arMimeVideo = array(
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


	protected function __construct() {
		if( null === $this->arMimeExt ) {
			$this->arMimeExt = array_merge(
				$this->arMimeText,
				$this->arMimeImages,
				$this->arMimeCompressedTypes,
				$this->arMimeDocuments,
				$this->arMimeAudio,
				$this->arMimeVideo
			);
		}
		if( null === $this->arMimeGroups ) {
			$this->arMimeGroups = array();
			foreach($this->arMimeText as $type => $ext) {
				$this->arMimeGroups[$type] = static::GRP_TEXT;
			}
			foreach($this->arMimeImages as $type => $ext) {
				$this->arMimeGroups[$type] = static::GRP_IMAGE;
			}
			foreach($this->arMimeCompressedTypes as $type => $ext) {
				$this->arMimeGroups[$type] = static::GRP_ARCH;
			}
			foreach($this->arMimeDocuments as $type => $ext) {
				$this->arMimeGroups[$type] = static::GRP_DOC;
			}
			foreach($this->arMimeAudio as $type => $ext) {
				$this->arMimeGroups[$type] = static::GRP_AUDIO;
			}
			foreach($this->arMimeVideo as $type => $ext) {
				$this->arMimeGroups[$type] = static::GRP_VIDEO;
			}
		}
	}

	/**
	 * @return self
	 */
	final public static function getInstance() {
		$class = get_called_class();
		if( !array_key_exists($class, self::$arInstances)
			|| !(self::$arInstances[$class] instanceof self)
		) {
			self::$arInstances[$class] = new $class;
		}
		return self::$arInstances[$class];
	}

	public function & _refMimeData() {
		return $this->arMimeExt;
	}

	public function getMimeData() {
		return $this->arMimeExt;
	}

	/**
	 * @param string $type
	 * @param string $fileExt
	 * @param int|null $group
	 * @return bool
	 */
	public function addType($type, $fileExt, $group = null) {
		if( array_key_exists($type, $this->arMimeExt) ) {
			return false;
		}
		$this->arMimeExt[$type] = $fileExt;
		if(null !== $group) {
			switch($group) {
				case static::GRP_TEXT:
				case static::GRP_IMAGE:
				case static::GRP_ARCH:
				case static::GRP_DOC:
				case static::GRP_AUDIO:
				case static::GRP_VIDEO:
				$this->arMimeGroups[$type] = $group;
			}
		}
		return true;
	}

	/**
	 * @param string $mimeType
	 * @param null|string $defaultExt
	 * @return null|string
	 */
	public function getFileExt($mimeType, $defaultExt = null) {
		if(array_key_exists($mimeType, $this->arMimeExt)) {
			return $this->arMimeExt[$mimeType];
		}
		return $defaultExt;
	}

	public function getContentGroup($mimeType) {
		if(array_key_exists($mimeType, $this->arMimeGroups)) {
			return $this->arMimeGroups[$mimeType];
		}
		return static::GRP_UNKNOWN;
	}
}
