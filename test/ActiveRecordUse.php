<?php
use OBX\Market\BasketItemDBS;

$BasketItemDBS = BasketItemDBS::getInstance();
$BasketItemDBS->getList(array('ID' => 'ASC'), array('STATUS_ID'));