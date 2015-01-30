<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 30.01.15
 * Time: 21:48
 */

namespace OBX\Core\PhpGenerator;


interface IClass {
	function addMethod();
	function addVariable();
	function addConstant();

	function getPhpCode();
} 