<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2013 Devtop
 */

namespace OBX\Core;


/**
 * Class FlatTreeObject
 * @package OBX\Core
 * Класс для работы с плоскими деревьями.
 * Например CIBlockSection::GetList(array('LEFT_MARGIN' => 'ASC'), ...)
 * или arResult компонента bitrix:menu
 * Что бы работать с плоским деревом категорий, необходимо обязательно добавить
 * в arSelect поле DEPTH_LEVEL
 */
class FlatTreeObject
{
	const DEF_DEPTH_LEVEL_KEY = 'DEPTH_LEVEL';
	const DEF_CHILDREN_KEY = 'CHILDREN';
	const DEF_PARENT_KEY = 'PARENT';

	protected $flatTree = null;
	protected $relationTable = array();

	protected $depthKey = self::DEF_DEPTH_LEVEL_KEY;
	protected $childrenKey = self::DEF_CHILDREN_KEY;
	protected $parentKey = self::DEF_PARENT_KEY;

	public function __construct(&$arFlatTree,
								$useRef2FlatTree = false,
								$bModifySrcArray = false,
								$DEPTH_KEY = self::DEF_DEPTH_LEVEL_KEY,
								$CHILDREN_KEY = self::DEF_CHILDREN_KEY,
								$PARENT_KEY = self::DEF_PARENT_KEY
	)
	{
		$this->depthKey = $DEPTH_KEY;
		$this->childrenKey = $CHILDREN_KEY;
		$this->parentKey = $PARENT_KEY;
		if(true === $useRef2FlatTree) {
			$this->flatTree = &$arFlatTree;
		}
		else {
			$this->flatTree = $arFlatTree;
		}
		$iItems = 0;
		$curDepth = 1;
		$prevKey = 0;
		$parentKey = null;
		$arLastKeyInDepth = array();
		$arParents = array();
		$arChildren = array();
		foreach($this->flatTree as $key => &$item) {
			$iItems++;
			if($item[$this->depthKey] > $curDepth) {
				$parentKey = $prevKey;
				$curDepth = $item[$this->depthKey];
			}
			elseif($item[$this->depthKey] < $curDepth) {
				$curDepth = $item[$this->depthKey];
				$parentKey = ($curDepth > 1)?$arLastKeyInDepth[$curDepth-1]:null;
			}
			if(null !== $parentKey) {
				$arParents[$parentKey][$this->childrenKey][] = $key;
			}
			$arChildren[$key][$this->parentKey] = $parentKey;
			$arChildren[$key][$this->depthKey] = $curDepth;
			$prevKey = $key;
			$arLastKeyInDepth[$item[$this->depthKey]] = $prevKey;
		}
		//d($arParents, '$arParents');
		//d($arChildren, '$arChildren');

		$this->relationTable = array();
		$this->relationTable[0] = $arParents[0];
		foreach($arChildren as $childKey => $arChild) {
			$this->relationTable[$childKey] = $arChild;
			$this->relationTable[$childKey][$this->childrenKey] = array();
			$this->relationTable[$childKey][$this->childrenKey] = $arParents[$childKey][$this->childrenKey];

			if($bModifySrcArray) {
				$this->flatTree[$childKey][$this->parentKey] = $arChild[$this->parentKey];
				$this->flatTree[$childKey][$this->childrenKey] = array();
				$this->flatTree[$childKey][$this->childrenKey] = $arParents[$childKey][$this->childrenKey];
			}
		}
	}

	public function getTree()
	{
		$arTree = array();
		$ref2ParentInTree = array();
		foreach($this->flatTree as $key => &$item) {
			$relTblItem = &$this->relationTable[$key];
			$parentIndex = $relTblItem[$this->parentKey];
			if(null === $parentIndex) {
				$arTree[$key] = array(
					'INDEX' => $key,
					$this->depthKey => $relTblItem[$this->depthKey],
					$this->parentKey => $relTblItem[$this->parentKey],
					$this->depthKey => $relTblItem[$this->depthKey],
					'DATA' => &$item,
					'CHILDREN' => null,
				);
				$ref2ParentInTree[$key] = &$arTree[$key];
			}
			else {
				$parentNode = &$ref2ParentInTree[$parentIndex];
				if(null === $parentNode['CHILDREN']) {
					$parentNode['CHILDREN'] = array();
				}
				$childNode = array(
					'INDEX' => $key,
					$this->depthKey => $relTblItem[$this->depthKey],
					$this->parentKey => $relTblItem[$this->parentKey],
					$this->depthKey => $relTblItem[$this->depthKey],
					'DATA' => &$this->flatTree[$relTblItem[$this->parentKey]],
					'CHILDREN' => null,
				);
				$parentNode['CHILDREN'][] = &$childNode;
				$nodeInRelTable = $this->relationTable[$key][$this->parentKey];
				if(null !== $nodeInRelTable) {
					$ref2ParentInTree[$key] = &$childNode;
				}
				unset($childNode);

			}
		}

		return $arTree;
	}

	function getIBSectionParentID(&$SectionID)
	{
		if(!$SectionID) {
			return false;
		}
		if(@isset($this->relationTable[$SectionID])) {
			return $this->relationTable[$SectionID]['PARENT_SECTION_ID'];
		}
		return false;
	}
} 