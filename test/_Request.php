<?php

namespace OBX\Core\Test;

abstract class _Request extends TestCase {
	static protected $_urlTestFiles = 'http://smokeoffice12.loc/bitrix/modules/obx.core/test/data/dwn_files/';
	static protected $_urlJSON = 'http://smokeoffice12.loc/bitrix/modules/obx.core/test/data/dwn_files/test.response.php?XDEBUG_SESSION_START=PHPSTORM';
	static protected $_url404 = 'http://smokeoffice12.loc/bitrix/modules/obx.core/test/data/dwn_files/test.response.php?get_404=Y';

	const _DIR_ = __DIR__;

	public function getFilesList() {
		return array(
			array('favicon.ico'),
			array('favicon.png'),
			array('favicon.jpg'),
			array('favicon.gif'),
			array('favicon.7z'),
			array('favicon.ico.rar'),
			array('favicon.ico.tar.bz2'),
			array('favicon.ico.zip'),
			array('favicon.tar.gz'),
			array('favicon.tar.xz'),
			array('test.html'),
			array('test.txt'),
			array('test.odp'),
			array('test.ods'),
			array('test.odt'),
			array('test.doc'),
			array('test.docx'),
			array('test.ppt'),
			array('test.pptx'),
			array('test.xls'),
			array('test.xlsx'),

		);
	}
}