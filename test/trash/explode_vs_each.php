<?php
namespace OBX\Core;
include realpath(__DIR__.'/../../lib/simplebenchmark.php');

SimpleBenchMark::start('explode');
for($i=0; $i < 10000; $i++) {
	list($key, $value) = explode('.', 'key.value.');
}
SimpleBenchMark::stop('explode');

SimpleBenchMark::start('each');
$arKV = ['key' => 'value'];
for($i=0; $i < 10000; $i++) {
	list($key, $value) = each($arKV);
}
SimpleBenchMark::stop('each');

echo 'explode: '.SimpleBenchMark::getResult('explode').PHP_EOL;
echo '   each: '.SimpleBenchMark::getResult('each').PHP_EOL;