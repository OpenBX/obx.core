<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arComponentParameters = array(
	'GROUPS' => array(),
	'PARAMETERS' => array(
		'TABS_COUNT' => array(
			'NAME' => 'Количество вкладок',
			'TYPE' => 'STRING',
			'DEFAULT' => '2',
			'REFRESH' => 'Y',
			'PARENT' => 'BASE'
		),
		'TAB_SWITCHER_ID' => array(
			'NAME' => 'ID Блока вкладок',
			'TYPE' => 'STRING',
			'DEFAULT' => 'obx-tabs',
			'PARENT' => 'BASE'
		),
		'URL_TEMPLATE' => array(
			'NAME' => 'Шаблон ссылок',
			'TYPE' => 'STRING',
			'DEFAULT' => '#tab=#TAB_ID#',
			'PARENT' => 'BASE'
		),
		'SELECTED_TAB' => array(
			'NAME' => 'Активная вкладка',
			'TYPE' => 'LIST',
			'VALUES' => array(
				'_none_' => 'не выбрана'
			),
			'PARENT' => 'BASE',
			'REFRESH' => 'Y'
		)
	)
);

$PARAMS = &$arComponentParameters['PARAMETERS'];
$GROUPS = &$arComponentParameters['GROUPS'];
$arSelectActive = &$arComponentParameters['PARAMETERS']['SELECTED_TAB']['VALUES'];
if( !array_key_exists('TABS_COUNT', $arCurrentValues) || intval($arCurrentValues['TABS_COUNT']) < 1 ) {
	$countTabs = 2;
	$arCurrentValues['TABS_COUNT'] = '2';
}
else {
	$countTabs = intval($arCurrentValues['TABS_COUNT']);
}

$ParameterTools = \OBX\Core\Components\Parameters::getInstance();
for($iTab=1; $iTab <= $countTabs; $iTab++ ) {
	$GROUPS['TAB_'.$iTab] = array('NAME' => 'Вкладка №'.$iTab);
	$currentTabID = 'tab_inc_'.$iTab;
	$PARAMS['TAB_'.$iTab.'_ID'] = array(
		'NAME' => 'ID вкладки',
		'TYPE' => 'STRING',
		'DEFAULT' => $currentTabID,
		'PARENT' => 'TAB_'.$iTab
	);
	$currentTabCaption = 'Вкладка №'.$iTab;
	$PARAMS['TAB_'.$iTab.'_CAPTION'] = array(
		'NAME' => 'Название вкладки',
		'TYPE' => 'STRING',
		'DEFAULT' => $currentTabCaption,
		'PARENT' => 'TAB_'.$iTab
	);
	$PARAMS['TAB_'.$iTab.'_TPL'] = array(
		'NAME' => 'Шаблон компонента bitrix:main.include',
		'TYPE' => 'STRING',
		'DEFAULT' => '.default',
		'PARENT' => 'TAB_'.$iTab
	);
	$PARAMS['TAB_'.$iTab.'_RCV'] = array(
		'NAME' => 'Рекурсивное подключение включаемых областей раздела',
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => $currentTabID,
		'PARENT' => 'TAB_'.$iTab
	);

	if( array_key_exists('TAB_'.$iTab.'_ID', $arCurrentValues) ) {
		$currentTabID = $arCurrentValues['TAB_'.$iTab.'_ID'];
	}
	if( array_key_exists('TAB_'.$iTab.'_CAPTION', $arCurrentValues) ) {
		$currentTabCaption = $arCurrentValues['TAB_'.$iTab.'_CAPTION'];
	}
	$arSelectActive[$currentTabID] = $currentTabCaption.' ['.$currentTabID.']';
}

?>