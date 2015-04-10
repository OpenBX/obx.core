<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */


require_once __DIR__.'/includes/constants.php';

CModule::AddAutoloadClasses('obx.core', array(
	'OBX_Build' => 'lib/build.php',
	'OBX_Tools' => 'lib/tools.php',
	'OBX\\Core\\Tools' => 'lib/tools.php',
));

