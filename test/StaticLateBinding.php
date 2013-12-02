<?php
namespace OBX\Core\Test;

class TestStaticLateBinding extends \PHPUnit_Framework_TestCase
{
	public function test() {
		include __DIR__.'/_incFileOne.php';
		include __DIR__.'/_incFileTwo.php';

		TestIncFileOne::setStaticData('one1', 'one1');
		print_r(TestIncFileTwo::getStaticData());
		echo TestIncFileOne::getFilePath().PHP_EOL;
		echo TestIncFileTwo::getFilePath().PHP_EOL;
		echo TestIncFileTwo::getClass().PHP_EOL;
	}
}