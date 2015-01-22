<?php

namespace OBX\Core\Test;

abstract class _Request extends TestCase {
	static protected $_urlTestFiles = 'http://bx-modules.loc/bitrix/modules/obx.core/test/data/dwn_files/';
	static protected $_urlJSON = 'http://bx-modules.loc/bitrix/modules/obx.core/test/data/dwn_files/test.response.php?XDEBUG_SESSION_START=PHPSTORM';
	static protected $_url404 = 'http://bx-modules.loc/bitrix/modules/obx.core/test/data/dwn_files/test.response.php?get_404=Y';
	static protected $_urlBigFile = 'http://bx-modules.loc/bitrix/modules/obx.core/test/data/dwn_files/Pirates.Of.Silicon.Valley.rus.Lostfilm.TV.avi';
	static protected $_urlBigFile300 = 'http://bx-modules.loc/bitrix/modules/obx.core/test/data/dwn_files/big_file_300M.zero';

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