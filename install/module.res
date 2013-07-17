#########################################
## @product OBX:Core Bitrix Module     ##
## @authors                            ##
##        Maksim S. Makarov aka pr0n1x ##
## @license Affero GPLv3               ##
## @mailto rootfavell@gmail.com        ##
## @copyright 2013 DevTop              ##
#########################################

[RESOURCES]
	%INSTALL_FOLDER%/php_interface/event.d/ :: obx.core.*.php :: %BX_ROOT%/php_interface/event.d/
	NOT_UNINSTALL ! %INSTALL_FOLDER%/php_interface/ :: run_event.d.php :: %BX_ROOT%/php_interface/
	%INSTALL_FOLDER%/js/ :: obx.core :: %BX_ROOT%/js/
	%INSTALL_FOLDER%/components/obx/ :: layout :: %BX_ROOT%/components/obx/
	%INSTALL_FOLDER%/components/obx/ :: breadcrumb.get :: %BX_ROOT%/components/obx/
	%INSTALL_FOLDER%/components/obx/ :: menu.iblock.list :: %BX_ROOT%/components/obx/
	%INSTALL_FOLDER%/components/obx/ :: social.links :: %BX_ROOT%/components/obx/

[RAW_LANG_CHECK]
{
	[classes]
		path: %MODULE_FOLDER%/classes
		exclude_path: %MODULE_FOLDER%/classes/Build.php
	[admin]
		path: %MODULE_FOLDER%/admin/
	[options]
    		path: %MODULE_FOLDER%/options.php
	[admin.ajax]
		path: %MODULE_FOLDER%/admin/ajax/
	[component.layout]
		path: %BX_ROOT%/components/obx/layout
	[component.breadcrumb.get]
		path: %BX_ROOT%/components/obx/breadcrumb.get
	[component.menu.iblock.list]
		path: %BX_ROOT%/components/obx/menu.iblock.list
	[component.social.links]
		path: %BX_ROOT%/components/obx/social.links
	[install]
		path: %INSTALL_FOLDER%/
		exclude: modules
		exclude_path: %INSTALL_FOLDER%/test/*
}