<?php
/**
 * @product OBX:Core Bitrix Module
 * @author Maksim S. Makarov aka pr0n1x
 * @license Affero GPLv3
 * @mailto rootfavell@gmail.com
 * @copyright 2015 Devtop
 */

namespace OBX\Core\Http\Client;


/**
 * Class MultiRequest
 * @package OBX\Core\Http
 * Класс служащий для скачивания нескольких файлов в один хит
 * Необходимо построить по архитектуре, описанной Александром Сербулом
 * в статье http://habrahabr.ru/company/bitrix/blog/198540/
 * только не принимать запросы, а отправлять их :)
 * В принципе реализовать тот же cURL только без cURL :)
 * Вся сила в неблокирующих сокетах и мультиплексировании
 */
class MultiRequest {

} 