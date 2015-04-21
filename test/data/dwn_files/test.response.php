<?php
if(array_key_exists('get_404', $_GET)) {
	header('HTTP/1.0 404 Not Found');
	header('Status: 404 Not Found');
}
header('Content-Type: application/json; charset=UTF-8');
//header('Content-Type: application/json');
if(array_key_exists('download', $_GET) && $_GET['download'] == 'Y') {
	$fileNamePostfix = '';
	if(array_key_exists('req', $_GET) ) {
		$fileNamePostfix = '_'.intval($_GET['req']);
	}
	header('Content-Disposition: attachment; filename='.urlencode(htmlspecialchars($_GET['test'].$fileNamePostfix)).'.json');
}
header('X-Some-Header1: some-value1; and-some-other-opt1=other=value1');
header('X-Some-Header2: some-value2; and-some-other-opt2=other=value2');
header('X-Some-Header3: some-value3; and-some-other-opt3=other=value3');
header('X-Some-Header3: some-value4; and-some-other-opt4=other=value4');
setcookie('cookie1', 'cookie_value1', time()+3600, '/', 'smokeoffice12.loc', false, true);
setcookie('cookie2', 'cookie_value2', time()+1800, '/', 'smokeoffice12.loc', false, true);
setcookie('cookie3', 'cookie_value3', time()+7200, '/', 'smokeoffice12.loc', false, true);
setcookie('cookie4', 'cookie_value4', time()+14400, '/', 'smokeoffice12.loc', false, true);

if(array_key_exists('sleep', $_REQUEST)) {
	$sleep = intval($_REQUEST['sleep']);
	if($sleep>10) $sleep = 10;
	sleep($sleep);
}

?>
{
	"response": "Немного русского текста и <div style=\"padding: 3px; border: 1px solid black; border-radius: 4px;\">html-код</div>",
	"get": <?=json_encode($_GET)?>,
	"post": <?=json_encode($_POST)?>,
	"cookie": <?=json_encode($_COOKIE)?>

}
