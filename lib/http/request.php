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
	static public function fixFileName(&$fileName) {
		$fileName = str_replace(array(
			'\\', '/', ':', '*', '?', '<', '>', '|', '"', "\n", "\r"
		), '', $fileName);
	}

	/**
	 * @param $header
	 * @return array
	 */
	static public function parseHeader(&$header) {
		$arHeader = array(
			'STATUS' => null,
			'COOKIES' => null,
			'CHARSET' => null
		);
		if( is_string($header) ) {
			$arHeaderLinesRaw = explode("\n", $header);
		}
		elseif(is_array($header)) {
			$arHeaderLinesRaw = $header;
		}
		else {
			return array();
		}

		//list($k, $firstValue) = each($arHeaderLinesRaw);
		if(strpos($arHeaderLinesRaw[0], 'HTTP') !== false) {
			$arHttpStatusRaw = explode(' ', trim(array_shift($arHeaderLinesRaw), " \r\n"));
			$arHeader['STATUS']['HTTP'] = array_shift($arHttpStatusRaw);
			$arHeader['STATUS']['CODE'] = array_shift($arHttpStatusRaw);
			$arHeader['STATUS']['MESSAGE'] = implode(' ', $arHttpStatusRaw);
		}
		$arCookiesList = array();
		foreach($arHeaderLinesRaw as &$hedaerLine) {
			$mainHeaderValue = null;
			$headerLine = trim($hedaerLine, " \r");
			$valKeyDelimPos = strpos($headerLine, ':');
			$headerKey = trim(substr($headerLine, 0, $valKeyDelimPos));
			$headerValue = trim(substr($headerLine, $valKeyDelimPos+1));
			if($headerKey == '') {
				continue;
			}
			//Если есть символ ";" значит скорее всего значение разделено на подзначения
			$arValueOptions = array();
			$bOptionsExists = false;
			if($headerKey == 'Set-Cookie') {
				if(strpos($headerValue, ';') !== false ) {
					$bOptionsExists = true;
					$arValueOptRaw = explode(';', $headerValue);
					$arCookie = array(
						'name' => '',
						'value' => '',
						'expires' => '',
						'path' => '/',
						'domain' => '',
						'secure' => '',
						'httponly' => ''
					);
					list($arCookie['name'], $arCookie['value']) = explode('=', array_shift($arValueOptRaw));
					foreach($arValueOptRaw as &$optionValueRaw) {
						list($optionKey, $optionValue) = explode('=', $optionValueRaw);
						$optionKey = trim($optionKey);
						$optionValue = trim($optionValue);
						if(array_key_exists($optionKey, $arCookie)) {
							$arCookie[$optionKey] = $optionValue;
						}
						$arCookiesList[$arCookie['name']] = $arCookie;
					}
					continue;
				}
			}
			else {
				if(strpos($headerValue, ';') !== false ) {
					$bOptionsExists = true;
					$arValueOptRaw = explode(';', $headerValue);
					$bFirstValueOption = true;
					foreach($arValueOptRaw as &$optionValueRaw) {
						list($optionKey, $optionValue) = explode('=', $optionValueRaw);
						$optionKey = trim($optionKey);
						$optionValue = trim($optionValue);
						if(true === $bFirstValueOption && $optionValue == '') {
							$mainHeaderValue = $optionKey;
						}
						else {
							$arValueOptions[$optionKey] = $optionValue;
						}
						$bFirstValueOption = false;
					}
				}
				if($headerKey == 'Content-Type') {
					if(
						true === $bOptionsExists
						&& array_key_exists('charset', $arValueOptions)
						&& strlen($arValueOptions['charset'])>0
					) {
						$arHeader['CHARSET'] = $arValueOptions['charset'];
					}
					else {
						$mainHeaderValue = $headerValue;
					}
				}
			}

			if($bOptionsExists) {
				$arHeader[$headerKey] = array(
					'VALUE' => $headerValue,
					'OPTIONS' => $arValueOptions
				);
			}
			else {
				$arHeader[$headerKey] = array(
					'VALUE' => $headerValue,
				);
			}
			if($mainHeaderValue !== null) {
				$arHeader[$headerKey]['VALUE_MAIN'] = $mainHeaderValue;
			}
			if( !empty($arCookiesList) ) {
				$arHeader['COOKIES'] = $arCookiesList;
			}
		}
		return $arHeader;
	}
} 