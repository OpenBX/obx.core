<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arComponentParameters = array(
	'GROUPS' => array(),
	'PARAMETERS' => array(
		'TABS_COUNT' => array(
			'NAME' => '���������� �������',
			'TYPE' => 'STRING',
			'DEFAULT' => '2',
			'REFRESH' => 'Y',
			'PARENT' => 'BASE'
		),
		'TAB_SWITCHER_ID' => array(
			'NAME' => 'ID ����� �������',
			'TYPE' => 'STRING',
			'DEFAULT' => 'obx-tabs',
			'PARENT' => 'BASE'
		),
		'URL_TEMPLATE' => array(
			'NAME' => '������ ������',
			'TYPE' => 'STRING',
			'DEFAULT' => '#tab=#TAB_ID#',
			'PARENT' => 'BASE'
		),
		'SELECTED_TAB' => array(
			'NAME' => '�������� �������',
			'TYPE' => 'LIST',
			'VALUES' => array(
				'_none_' => '�� �������'
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
	$GROUPS['TAB_'.$iTab] = array('NAME' => '������� �'.$iTab);
	$currentTabID = 'tab_inc_'.$iTab;
	$PARAMS['TAB_'.$iTab.'_ID'] = array(
		'NAME' => 'ID �������',
		'TYPE' => 'STRING',
		'DEFAULT' => $currentTabID,
		'PARENT' => 'TAB_'.$iTab
	);
	$currentTabCaption = '������� �'.$iTab;
	$PARAMS['TAB_'.$iTab.'_CAPTION'] = array(
		'NAME' => '�������� �������',
		'TYPE' => 'STRING',
		'DEFAULT' => $currentTabCaption,
		'PARENT' => 'TAB_'.$iTab
	);
	$PARAMS['TAB_'.$iTab.'_TPL'] = array(
		'NAME' => '������ ���������� bitrix:main.include',
		'TYPE' => 'STRING',
		'DEFAULT' => '.default',
		'PARENT' => 'TAB_'.$iTab
	);
    $PARAMS['TAB_'.$iTab.'_CAT_COUNT'] = array(
        'NAME' => '���������� �����������',
        'TYPE' => 'STRING',
        'DEFAULT' => '0',
        'PARENT' => 'TAB_'.$iTab,
        'REFRESH' => 'Y'
    );
    if ( intval($arCurrentValues['TAB_'.$iTab.'_CAT_COUNT']) > 0) {
        for ($i=0;$i++ < intval($arCurrentValues['TAB_'.$iTab.'_CAT_COUNT']);){
            $PARAMS['TAB_'.$iTab.'_CAT_ID_'.$i] = array(
                'NAME' => 'ID �������',
                'TYPE' => 'STRING',
                'DEFAULT' => $arCurrentValues['TAB_'.$iTab.'_ID']."_".$i,
                'PARENT' => 'TAB_'.$iTab
            );
            $PARAMS['TAB_'.$iTab.'_CAT_'.$i] = array(
                'NAME' => '�������� ���������� �'.$i,
                'TYPE' => 'STRING',
                'DEFAULT' => '��������� �'.$i,
                'PARENT' => 'TAB_'.$iTab,
            );
        }
    }
	$PARAMS['TAB_'.$iTab.'_RCV'] = array(
		'NAME' => '����������� ����������� ���������� �������� �������',
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