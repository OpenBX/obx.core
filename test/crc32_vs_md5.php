#!/usr/bin/env php
<?php
//$name = 'q3p4tcpn27tpvo874pvv wer7 pwr 7r 987s 7s9df7';
$name = serialize([
	'asdf' => ['asdf', 'zxcv'],
	'asdzxcv' => 'asdf'.mt_rand(10, 1000000000)
]);
$beginCRC32 = microtime(true);
$hashCRC32 = dechex(crc32($name));
$endCRC32 = microtime(true);
$beginMD5 = microtime(true);
$hashMD5 = md5($name);
$endMD5 = microtime(true);

echo 'CRC32 time: '.($endCRC32 - $beginCRC32).' | '.$hashCRC32.PHP_EOL;
echo 'MD5 time: '.($endMD5 - $beginMD5).' | '.$hashMD5.PHP_EOL;