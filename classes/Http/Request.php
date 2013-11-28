<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Http;

/**
 * Class Request
 * @package OBX\Core\Http
 * Базовый класс отвечающий за парсинг и формирование заголовков и данных
 * необходимо разработать таким образом, что бы его можно было использовать
 * как в качестве базового класса для клиента так и для сервера
 */
class Request {
	static public function getFileNameFromUrl($url, &$fileExt = null, &$baseName = null) {
		$arUrl = parse_url($url);
		$fileName = trim(urldecode(basename($arUrl['path'])));
		static::fixFileName($fileName);
		$fileExt = '';
		$dotPos = strrpos($fileName, '.');
		if( $dotPos !== false) {
			$fileExt = strtolower(substr($fileName, $dotPos+1));
			$baseName = substr($fileName, 0, $dotPos);
			switch($fileExt) {
				case 'gz':
				case 'bz2':
				case 'bz':
				case 'xz':
				case 'lzma':
				case '7z':
					$possibleArchDotPos = strrpos(strtolower($fileName), '.tar.'.$fileExt);
					if( $possibleArchDotPos === (strlen($fileName)-strlen('.tar.'.$fileExt)) ) {
						$fileExt = 'tar.'.$fileExt;
						$baseName = substr($fileName, 0, $possibleArchDotPos);
					}
					break;
			}
		}
		return $fileName;
	}
	static protected function fixFileName(&$fileName) {
		$fileName = str_replace(array(
			'\\', '/', ':', '*', '?', '<', '>', '|', '"', "\n", "\r"
		), '', $fileName);
	}
} 