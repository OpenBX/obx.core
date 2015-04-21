<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core;


/**
 * Class FSPath
 * @package OBX\Core
 * Класс, который нужен что бы не проверять путь каждый раз в тех ф-их, которые работают с путями на FS
 */
class FSPath {
	public function __construct($filePath) {

	}

	public function checkPath() {

	}

	public function __toString() {
		return '';
	}
} 