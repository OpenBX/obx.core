<?php

use OBX\Core\JSLang;

class OBX_Core_Test_JSLang extends OBX_Core_TestCase
{
	public function testBuffer(){
		$JSLang = JSLang::getInstance('obx.market');
		$JSLang->showHead();
	}

	public function test(){
		$JSLang = JSLang::getInstance('obx.market');

		$JSLang->addMessage('basket.currency.format.string',	'# руб.');
		$JSLang->addMessage('basket.currency.name',				'рубли');
		$JSLang->addMessage('basket.currency.override',			'override string');
		$JSLang->addMessage('basket.currency.override.onemore',	'one more string');
		$JSLang->addMessage('basket.currency',					'test override');
		// +++ Emulate using via showJSInitDomain
			//$initString = $JSLang->getJSInitDomain('', LANGUAGE_ID, true);
			//$nullString = $JSLang->getJSInitDomain('', LANGUAGE_ID, true);
			//$this->assertNotEmpty($initString);
			//$this->assertEmpty($nullString);
		// ^^^ Emulate using via showJSInitDomain

		// just get, not via showJSInitDomain
		$copyInitString = $JSLang->getJSInitDomain();
		$this->assertNotEmpty($copyInitString);

		$domainMessages = $JSLang->getDomainScript();
		$this->assertTrue((strpos($domainMessages, 'basket.currency.format.string')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.name')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.__node_name__')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.override.__node_name__')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.override.onemore')!==false));
	}
}