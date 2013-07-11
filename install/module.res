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

