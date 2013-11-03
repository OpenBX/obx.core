<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

// Этот файл нужен что бы IDE видела некоторые ф-ии :)
/**
 * @param string $haystack
 * @return int
 */
function mb_orig_strlen($haystack) {}

/**
 * @param string $haystack
 * @param string $needle
 * @param int $offset
 * @param string $encoding
 * @return int | false
 */
function mb_orig_strpos($haystack , $needle, $offset = 0, $encoding ) {}

/**
 * @param string $string
 * @param int $start
 * @param int $length
 * @return string
 */
function mb_orig_substr ($string, $start, $length) {}