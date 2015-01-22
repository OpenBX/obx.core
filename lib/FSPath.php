<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 25.11.13
 * Time: 19:04
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