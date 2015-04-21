<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
$arParams['TABS_COUNT'] = intval($arParams['TABS_COUNT']);
$arResult['TABS'] = array();
for($iTab=1; $iTab <= $arParams['TABS_COUNT']; $iTab++) {
	$arParams['TAB_'.$iTab.'_ID'] = trim($arParams['TAB_'.$iTab.'_ID']);
	if(empty($arParams['TAB_'.$iTab.'_ID']) || $arParams['TAB_'.$iTab.'_ID'] == '_none_') {
		ShowError('Для вкладки №'.$iTab.' не задан ID');
	}
	$arParams['SELECTED_TAB'] = trim($arParams['SELECTED_TAB']);
	if( array_key_exists($arParams['TAB_'.$iTab.'_ID'], $arResult['TABS']) ) {
		ShowError('В параметрах компонента задано более одной вкладки с ID "'.$arParams['TAB_'.$iTab.'_ID'].'"');
	}
    $arChilds = array();
    if (intval($arParams['TAB_'.$iTab.'_CAT_COUNT']) > 0) {
        for ($i=0;$i++ < intval($arParams['TAB_'.$iTab.'_CAT_COUNT']);) {
            $arChilds[$i]['NAME'] = $arParams['TAB_'.$iTab.'_CAT_'.$i];

            $arChilds[$i]['MAIN_INCLUDE_PARAMS'] = array(
                'AREA_FILE_SHOW' => 'sect',
                'AREA_FILE_SUFFIX' => $arParams['TAB_'.$iTab.'_CAT_ID_'.$i],
                'AREA_FILE_RECURSIVE' => 'N',
                'NAME' => $arParams['TAB_'.$iTab.'_CAT_'.$i],
                'EDIT_TEMPLATE' => ''
            );
            $arChilds[$i]['ID'] = $arParams['TAB_'.$iTab.'_CAT_ID_'.$i];
            $arChilds[$i]['URL'] = str_replace('#TAB_ID#', $arParams['TAB_'.$iTab.'_CAT_ID_'.$i], $arParams['URL_TEMPLATE']);
        }
    }
	$arResult['TABS'][$arParams['TAB_'.$iTab.'_ID']] = array(
		'ID' => $arParams['TAB_'.$iTab.'_ID'],
		'NUMBER' => $iTab,
		'CAPTION' => $arParams['TAB_'.$iTab.'_CAPTION'],
		'URL' => str_replace('#TAB_ID#', $arParams['TAB_'.$iTab.'_ID'], $arParams['URL_TEMPLATE']),
		'SELECTED' => ($arParams['TAB_'.$iTab.'_ID'] == $arParams['SELECTED_TAB'])?'Y':'N',
		'MAIN_INCLUDE_TEMPLATE' => $arParams['TAB_'.$iTab.'_TPL'],
		'MAIN_INCLUDE_PARAMS' => array(
			'AREA_FILE_SHOW' => 'sect',
			'AREA_FILE_SUFFIX' => $arParams['TAB_'.$iTab.'_ID'],
			'AREA_FILE_RECURSIVE' => $arParams['TAB_'.$iTab.'_RCV'],
                    'NAME' => $arParams['TAB_' . $iTab . '_CAPTION'],
			'EDIT_TEMPLATE' => ''
            ),
            'CHILDS' => $arChilds
	);
}
/** @var \CBitrixComponent $this */
$this->includeComponentTemplate();
?>