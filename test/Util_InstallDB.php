<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

class OBX_Test_Util_Core_InstallDB extends OBX_Core_TestCase {
	public function testInstallDB() {
		require_once $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/obx.core/install/index.php';
		$module_obx_core = new obx_core;
		$this->assertInstanceOf(obx_core, $module_obx_core);
		$module_obx_core->InstallDB();
		$module_obx_core->InstallData();
	}
}