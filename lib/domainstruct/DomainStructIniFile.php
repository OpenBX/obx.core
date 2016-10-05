<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto pr0n1x@yandex.ru
 * @copyright 2016 OpenBX
 */

namespace OBX\Core\DomainStruct;

class DomainStructIniFile extends DomainStructArray {
	protected $configFilePath = null;

	public function __construct($filePath) {
		$filePath = rtrim(str_replace(array('\\', '//'), '/', trim($filePath)), '/');
		if(!is_file($filePath)) {
			throw new \ErrorException('Конфигурационный ini-файл не найден');
		}
		$this->configFilePath = $filePath;
		parent::__construct(parse_ini_file($this->configFilePath, false));
	}

	public function getFilePath() {
		return $this->configFilePath;
	}
}
