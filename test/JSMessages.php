<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

namespace OBX\Core\Test;
use OBX\Core\JSMessages;

require_once dirname(__FILE__).'/../classes/TestCase.php';

class JSMessagesTest extends TestCase
{
	public function getCurDir() {
		return dirname(__FILE__);
	}
	public function testBuffer(){
		$JSLang = JSMessages::getInstance('obx.market');
		$JSLang->showHead();
	}

	public function test(){
		$JSMessages = JSMessages::getInstance('obx.market');

		$JSMessages->addMessage('basket.currency.format.string',	'# руб.');
		$JSMessages->addMessage('basket.currency.name',				'рубли');
		$JSMessages->addMessage('basket.currency.override',			'override string');
		$JSMessages->addMessage('basket.currency.override.onemore',	'one more string');
		$JSMessages->addMessage('basket.currency',					'test override');
		// +++ Emulate using via showJSInitDomain
			$initString = $JSMessages->getJSInitDomain('', true);
			$nullString = $JSMessages->getJSInitDomain('', true);
			$this->assertNotEmpty($initString);
			$this->assertEmpty($nullString);
		// ^^^ Emulate using via showJSInitDomain

		// just get, not via showJSInitDomain
		$copyInitString = $JSMessages->getJSInitDomain();
		$this->assertNotEmpty($copyInitString);

		$domainMessages = $JSMessages->getDomainScript();
		$this->assertTrue((strpos($domainMessages, 'basket.currency.format.string')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.name')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.__node_name__')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.override.__node_name__')!==false));
		$this->assertTrue((strpos($domainMessages, 'basket.currency.override.onemore')!==false));
	}
}