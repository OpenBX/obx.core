#!/usr/bin/env php
<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

require dirname(__FILE__) . "classes/Build.php";
$build = new OBX_Build("obx.core");
$build->processCommandOptions();
