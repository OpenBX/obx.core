<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Migration;


abstract class Version
{
	protected $version = null;
	protected $versionFrom = null;

	abstract public function upgrade();
	abstract public function downgrade();

	static public function getList($moduleID) {

	}

	static public function getListModules() {

	}
}