<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
$arComponentDescription = array(
	'NAME' => GetMessage('OBX_CMP_UTL_TABS_NAME'),
	'DESCRIPTION' => GetMessage('OBX_CMP_UTL_TABS_DESCRIPTION'),
	'CACHE_PATH' => 'Y',
	'PATH' => array(
		'ID' => 'utility',
		'CHILD' => array(
			'ID' => 'include_area',
			'NAME' => GetMessage('MAIN_INCLUDE_GROUP_NAME'),
		),
	),
);
?>