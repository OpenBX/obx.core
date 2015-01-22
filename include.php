<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2015 DevTop                    **
 ***********************************************/

use Bitrix\Main\Loader;
Loader::registerAutoLoadClasses('obx.core', array(
	'OBX_Build' => 'lib/build.php',
	'OBX_Tools' => 'lib/tools.php',
	'OBX\\Core\\Tools' => 'lib/tools.php',
));

