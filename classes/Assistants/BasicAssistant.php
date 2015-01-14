<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Mikhail Medvedev aka r3c130n      **
 ** @license Affero GPLv3                     **
 ** @mailto i@r3c130n.ru                      **
 ** @copyright 2015 Salerman                  **
 ***********************************************/

namespace OBX\Core\Assistants_Alpha;


abstract class BasicAssistant {

	protected $title = "";

	protected $limit = 1;
	protected $lastID = 0;
	protected $qty = 0;

	protected $callback = null;
	protected $bCallback = false;

	protected $arFilter = Array();

	protected $leftBorderCnt = 0;

	protected $filterInputs = Array();

	protected $message = 'Обработал строку №#ID#';

	protected $currentPercent = 0;

	const FILTER_INPUT_TYPE_TEXT = 1;
	const FILTER_INPUT_TYPE_SELECT = 2;
	const FILTER_INPUT_TYPE_RADIO = 3;
	const FILTER_INPUT_TYPE_CHECKBOX = 4;

	public function __construct ($title, $callbackExecute = null) {
		global $APPLICATION;

		if ($_REQUEST['work_start'] && check_bitrix_sessid()) {
			$this->lastID = intVal($_REQUEST['lastid']);
		}

		if ($callbackExecute != null && is_callable($callbackExecute)) {
			$this->callback = $callbackExecute;
			$this->bCallback = true;
		}

		$this->title = $title;
		$APPLICATION->SetTitle( $this->title );
	}

	public function setLimit ($limit) {
		$this->limit = $limit;
	}

	public function setMessage ($message) {
		$this->message = $message;
	}

	abstract public function executeStep();

	public function Run () {
		if ($_REQUEST['work_start'] && check_bitrix_sessid()) {

			if ($this->bCallback) {
				call_user_func($this->callback, $this);
			} else {
				$this->executeStep();
			}

			echo $this->getCurrentStatusString();

			die();
		}

		$GLOBALS['assistant_filter'] = $this->getFilterHtml();
	}

	public function addFilterInput ($type, $name, $label, $def_value = "", $values = Array()) {
		if (isset($_REQUEST['set_filter'])) {
			$this->saveFilterValues();
		}

		if (isset($_REQUEST['del_filter'])) {
			$this->delFilterValues();
		}

		switch ($type) {
			case self::FILTER_INPUT_TYPE_TEXT:
				$val = isset($_REQUEST[$name]) ? htmlspecialcharsEx($_REQUEST[$name]) : $def_value;
				$this->filterInputs[] = '<input type="text" name="' . $name . '" placeholder="' . $label . '" value="' . $val . '"/>';
				break;
			case self::FILTER_INPUT_TYPE_SELECT:
				$val = isset($_REQUEST[$name]) ? htmlspecialcharsEx($_REQUEST[$name]) : '';
				$select = '<select name="' . $name . '"><option value="">' . $label . '</option>';
				if (!empty($values) && is_array($values)) {
					foreach ($values as $value => $title) {
						$bSelected = ($value == $val) ? ' selected="selected"' : '';
						$select .= '<option value="' . $value . '"' . $bSelected .'>' . $title .'</option>';
					}
				}
				$select .= '</select>';
				$this->filterInputs[] = $select;
				break;
			case self::FILTER_INPUT_TYPE_CHECKBOX:
				$val = isset($_REQUEST[$name]) ? htmlspecialcharsEx($_REQUEST[$name]) : '';
				$bChecked = ($val == $def_value) ? 'checked="checked"' : '';
				$this->filterInputs[] = '<input type="checkbox" ' . $bChecked . ' name="' . $name . '" value="' . $def_value . '"/> ' . $label;
				break;
			case self::FILTER_INPUT_TYPE_RADIO:
				$val = isset($_REQUEST[$name]) ? htmlspecialcharsEx($_REQUEST[$name]) : '';
				$radio = $label;
				if (!empty($values) && is_array($values)) {
					foreach ($values as $value => $label) {
						$bChecked = ($val == $value) ? 'checked="checked"' : '';
						$radio .= ' <input type="radio" ' . $bChecked . ' name="' . $name . '" value="' . $value . '"/> ' . $label;
					}
				}
				$this->filterInputs[] = $radio;

				break;
		}
	}

	protected function getFilterHtml() {
		$html = "<fieldset><legend>Фильтр</legend>";
		if (!empty($this->filterInputs)) {
			foreach ($this->filterInputs as $input) {
				$html .= $input . ' ';
			}
			$html .= '<input type="submit" name="set_filter" value="Применить фильтр" /> ';
			$html .= '<input type="submit" name="del_filter" value="Сбросить фильтр" /><br>';

			$html .= "</fieldset><br>";
		}
		return $html;
	}

	protected function getStatusMessage () {
		return str_replace(
			Array ('#ID#','#QTY#'),
			Array ($this->lastID, $this->qty),
			$this->message);
	}

	public function getLeftBorderQty () {
		return $this->leftBorderCnt;
	}

	public function setLeftBorderQty ($qty) {
		$this->leftBorderCnt = $qty;
	}

	public function getQty () {
		return $this->qty;
	}

	public function setQty ($qty) {
		$this->qty = $qty;
	}

	public function getLastID () {
		return $this->lastID;
	}

	public function setLastID ($id) {
		$this->lastID = $id;
	}

	protected function getCurrentPercent () {
		$qty = $this->getQty();
		if ($qty == 0) {
			$this->currentPercent = 100;
		} else {
			$this->currentPercent = round( 100 * $this->getLeftBorderQty() / $qty, 2);
		}
		return $this->currentPercent;
	}

	protected function getCurrentStatusString() {
		$this->currentPercent = $this->getCurrentPercent ();

		return 'CurrentStatus = Array('
					. $this->currentPercent . ',"'
					. ( $this->currentPercent < 100 ? '&lastid='  .$this->lastID : '')
					. '", "' . $this->getStatusMessage() . '");';
	}

	protected function saveFilterValues() {
		$arExclude = Array('set_filter', 'tabControl_active_tab', 'autosave_id', 'sessid');
		foreach ($_POST as $key => $val) {
			if (!in_array($key, $arExclude)) {
				$_SESSION['OBX_ASSISTANT'][$key] = $val;
			}
		}
	}

	protected function delFilterValues () {
		$arExclude = Array('set_filter', 'tabControl_active_tab', 'autosave_id', 'sessid');
		foreach ($_POST as $key => $val) {
			if (!in_array($key, $arExclude)) {
				unset($_POST[$key]);
				unset($_REQUEST[$key]);
			}
		}

		unset($_SESSION['OBX_ASSISTANT']);
	}

	public static function getFilterValues() {
		if (!empty($_SESSION['OBX_ASSISTANT'])) {
			foreach ($_SESSION['OBX_ASSISTANT'] as $key => $value) {
				$_REQUEST[$key] = isset ($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
			}
			return $_SESSION['OBX_ASSISTANT'];
		}
		return Array();
	}
}