<?php
/**
 * Created by PhpStorm.
 * User: maximum
 * Date: 28.11.13
 * Time: 14:25
 */

namespace OBX\Core\Http;


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