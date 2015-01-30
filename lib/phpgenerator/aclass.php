<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 30.01.15
 * Time: 21:48
 */

namespace OBX\Core\PhpGenerator;


abstract class AClass implements IClass {
	/**
	 * Возвращает строку с php-кодом массива переданного на вход
	 * @param Array $array - входной массив, для вывода в виде php-кода
	 * @param String $whiteOffset - отступ от начала каждй строки(для красоты)
	 * @return string
	 */
	static public function convertArray2PhpCode($array, $whiteOffset = '') {
		$strResult = "array(\n";
		foreach($array as $paramName => &$paramValue) {
			if(!is_array($paramValue)) {
				$qt = '\'';
				if(null === $paramValue) {
					$qt = ''; $paramValue = 'null';
				}
				elseif(is_bool($paramValue)) {
					$qt = '';
					$paramValue = ($paramValue?'true':false);
				}
				elseif(is_numeric($paramValue)) {
					$qt = '';
				}
				elseif(is_string($paramValue) || is_object($paramValue)) {
					$paramValue = ''.$paramValue;
					if('::' === substr($paramValue, 0, 2)) {
						$qt = '';
						$paramValue = trim(substr($paramValue, 2));
					}
					else {
						$paramValue = str_replace('\\', '\\\\', $paramValue);
						$paramValue = str_replace('\'', '\\\'', $paramValue);
					}
				}
				$strResult .= $whiteOffset."\t'".$paramName."' => ".$qt.$paramValue.$qt.",\n";
			}
			else {
				$strResult .= $whiteOffset
					."\t'".$paramName
					."' => ".self::convertArray2PhpCode($paramValue, $whiteOffset."\t")
					.",\n";
			}
		}
		$strResult .= $whiteOffset.")";
		return $strResult;
	}
} 