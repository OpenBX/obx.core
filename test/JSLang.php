<?php

use OBX\Core\JSLang;

class OBX_Core_Test_JSLang extends OBX_Core_TestCase
{
	public function test1(){
		$JSLang = JSLang::getInstance('obx.market');
		$JSLang->addMessage('basket.currency.format.string',		'# руб.');
		$JSLang->addMessage('basket.currency.name',				'рубли');
		$JSLang->addMessage('basket.currency.override',			'override string');
		$JSLang->addMessage('basket.currency.override.onemore',	'one more string');
		$JSLang->addMessage('basket.currency',					'test override');
	}
}