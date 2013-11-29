<?php
define('OBX_MAGIC_WORD', '__I_KNOW_WHAT_I_DO__');
define('I_KNOW_WHAT_I_DO', '__I_KNOW_WHAT_I_DO__');
if(!defined('OBX_DOC_ROOT') && !empty($_SERVER['DOCUMENT_ROOT'])) {
	define('OBX_DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
	define('OBX\DOC_ROOT', $_SERVER['DOCUMENT_ROOT']);
}