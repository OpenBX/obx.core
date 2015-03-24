<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Test\Assistant;
use OBX\Core\Test\TestCase;
use OBX\Core\Assistant\IBlockElement;

class TestAssistant extends TestCase {
	const _DIR_ = __DIR__;

	public function test() {

		$assistant = new IBlockElement();

		$assistant->run();

	}
} 