<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maximum
 * Date: 26.04.13
 * Time: 16:34
 * To change this template use File | Settings | File Templates.
 */

final class OBX_Test_VisitorHitDBS extends OBX_Core_TestCase {

	static private $_VisitorsHitDBS = null;
	static private $_arVisitorsHitsData = array();

	public static function setUpBeforeClass() {
		self::$_VisitorsHitDBS = OBX_VisitorHitDBS::getInstance();
		self::$_arVisitorsHitsData = array();
	}

}