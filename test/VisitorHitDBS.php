<?php
/***********************************************
 ** @product OBX:Core Bitrix Module           **
 ** @authors                                  **
 **         Maksim S. Makarov aka pr0n1x      **
 ** @license Affero GPLv3                     **
 ** @mailto rootfavell@gmail.com              **
 ** @copyright 2013 DevTop                    **
 ***********************************************/

use OBX\Core\VisitorHitDBS;

final class OBX_Test_VisitorHitDBS extends OBX_Core_TestCase {

	static private $_VisitorsHitDBS = null;
	static private $_arVisitorsHitsData = array();

	public static function setUpBeforeClass() {
		self::$_VisitorsHitDBS = VisitorHitDBS::getInstance();
		self::$_arVisitorsHitsData = array();
	}

}