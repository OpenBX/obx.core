<?php
use OBX\Core\Components\Ajax;

ob_start();

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);

/**
 * @global \CMain $APPLICATION
 * @global \CUser $USER
 */
/** @noinspection PhpIncludeInspection */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$ajaxComponent = Ajax::getByCallId($_REQUEST['ajax_call']);

if( $ajaxComponent instanceof Ajax ) {
	$APPLICATION->IncludeComponent(
		$ajaxComponent->name,
		$ajaxComponent->template,
		$ajaxComponent->params,
		false,['HIDE_ICONS' => 'Y']
	);
}
else {
	ShowError('Данные ajax-вызова компонента не найдены');
}