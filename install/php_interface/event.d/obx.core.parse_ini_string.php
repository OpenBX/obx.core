<?php
/*************************************
 ** @product OBX:Core Bitrix Module **
 ** @authors                        **
 **         Maksim S. Makarov       **
 **         Morozov P. Artem        **
 ** @license Affero GPLv3           **
 ** @mailto rootfavell@gmail.com    **
 ** @mailto tashiro@yandex.ru       **
 *************************************/

if( !function_exists('parse_ini_string') ) {
	//*/
	function parse_ini_string($str, $ProcessSections=false){
		$lines  = explode("\n", $str);
		$return = Array();
		$inSect = false;
		foreach($lines as $line){
			$line = trim($line);
			if(!$line || $line[0] == "#" || $line[0] == ";") {
				continue;
			}
			if( ($posDotcomma = strpos($line, ";")) ) {
				$line = substr($line, 0, $posDotcomma);
			}
			if( ($posSharp = strpos($line, "#")) ) {
				$line = substr($line, 0, $posSharp);
			}
			$line = trim($line);
			if(!$line) {
				continue;
			}
			if($line[0] == "[" && $endIdx = strpos($line, "]")){
				$inSect = substr($line, 1, $endIdx-1);
				continue;
			}
			if(!strpos($line, '=')) // (We don't use "=== false" because value 0 is not valid as well)
				continue;

			$tmp = explode("=", $line, 2);
			$value = ltrim($tmp[1]);
			$key = trim($tmp[0]);
			# Remove quote
			if( preg_match( "/^\".*\"$/", $value ) || preg_match( "/^'.*'$/", $value ) ) {
				$value = mb_substr( $value, 1, mb_strlen( $value ) - 2 );
			}
			if($ProcessSections && $inSect) {
				$return[$inSect][$key] = $value;
			}
			else {
				$return[$key] = $value;
			}
		}
		return $return;
	}
}
?>