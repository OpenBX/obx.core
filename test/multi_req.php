<?php
namespace OBX\Core\Test;


class TestMultiRequests extends \PHPUnit_Framework_TestCase
{
	protected $_url = 'http://smokeoffice12.loc/bitrix/tools/obx.core/test.response.php?XDEBUG_SESSION_START=PHPSTORM';
	//protected $_url = 'http://smokeoffice12.loc/bitrix/modules.build/dvt.marketpizza/build/release-1.0.4.tar.gz?';
	protected $_arCH = array();
	protected $_arResponseList = array();
	protected $_arRespHeaderList = array();
	protected $_iDwn = 0;
	protected $_arFD = array();


	public function addUrl($uriParam) {
		$this->_iDwn++;
		$this->_arCH[$this->_iDwn] = null; $ch = &$this->_arCH[$this->_iDwn];
		$this->_arFD[$this->_iDwn] = null; $fd = &$this->_arFD[$this->_iDwn];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_url.$uriParam);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2000);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		//curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Range: bytes=0-1'));
		//$fd = fopen(__DIR__.'/../../../tmp/obx.core/file.No.'.$this->_iDwn, 'w');
		//curl_setopt($ch, CURLOPT_FILE, $fd);
	}

	public function test() {
		$this->addUrl('&req=1');
		$this->addUrl('&req=2');
		$this->addUrl('&req=3');
		$this->addUrl('&req=4');
		$this->addUrl('&req=5');
		$this->addUrl('&req=6');
		$this->addUrl('&req=7');
		$this->addUrl('&req=8');

		$mh = curl_multi_init();
		foreach($this->_arCH as &$ch) {
			curl_multi_add_handle($mh, $ch);
		}

		//execute the handles
		$running = null;
		do {
			usleep(100);
			$resE = curl_multi_exec($mh, $running);
			$debug=1;
		} while($running > 0);

		//get content
		foreach($this->_arCH as &$ch) {
			$this->_arResponseList[] = curl_multi_getcontent($ch);
			$this->_arRespHeaderList[] = curl_getinfo($ch);
			$debug=1;
		}

		//close the handles
		foreach($this->_arCH as &$ch) {
			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);
	}
}