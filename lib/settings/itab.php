<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

namespace OBX\Core\Settings;

interface ITab {
	function getTabTitle();
	function getTabDescription();
	function getTabIcon();
	function getTabHtmlContainer();
	function showTabContent();
	function showTabScripts();
	function saveTabData();
	function showMessages($colspan = -1);
	function showWarnings($colspan = -1);
	function showErrors($colspan = -1);
}