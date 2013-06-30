#!/usr/bin/env php
<?php
//`cd ${__DIR__}`;
//$filesList = `find ./ \
//				\! -wholename "./install/modules/*" \
//				-a \( \
//					-iname "*.php" -o -iname "*.js" -o -iname "*.html" \
//				\)\
//				-a \! -iname "jquery*.js" \
//				-a \! -iname "*.min.js" \
//				-a \! -wholename "./install/get_back_installed_files.php" \
//				-a \! -wholename "./install/install_deps.php" \
//				-a \! -wholename "./install/install_files.php" \
//				-a \! -wholename "./install/uninstall_files.php" \
//				-a \! -wholename "./*lang/ru/*" \
//				-a \! -iname "*.pack.js" \
//				-a \! -iname "print.html"`;
//$arFiles = explode("\n", $filesList);
//print_r($arFiles);
//exit;

$arFiles = array(
	 './classes/DBSimple.php'
	,'./classes/Build.php'
	,'./classes/MessagePool.php'
	,'./classes/Tools.php'
	,'./classes/WizardImportIBlock.php'
);

$printContent = <<<HTML
<!doctype html>
<html>
	<head>
		<meta charset="utf-8"/>
		<style type="text/css">
			body {
				font-weight: bolder;
				font-family: monospace, sans-serif;
			}
			h1 {
				font-size: 12px;
			}
			pre {
				font-size: 8px;
			}
		</style>
	</head>
	<body>
HTML;

foreach($arFiles as $filePath) {
//	echo
//		"\n=======================".$filePath."========================\n"
//		.file_get_contents(__DIR__.'/'.$filePath)
//		."\n===============================================================\n"
//	;
	$fileCodeContent = file_get_contents(__DIR__.'/'.$filePath);

	// удаляем многострочные комментарии
	$fileCodeContent = preg_replace('~\/\*.*?\*\/~is', '', $fileCodeContent);

	// удаялем однострочные комментарии
	$regOneLineComments = '~^[\s\t]*?(?:\/\/){1,}.*~im';
	//$bMatched			= preg_match($regOneLineComments, $fileCodeContent, $arMatches);
	$fileCodeContent  = preg_replace($regOneLineComments, '', $fileCodeContent);

	// Заменяем табы на два проблела в начале строк
	$fileCodeContent = preg_replace('~[\ ]{4}?~im', '	', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{20}?~im', '                    ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{19}?~im', '                   ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{18}?~im', '                  ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{17}?~im', '                 ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{16}?~im', '                ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{15}?~im', '               ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{14}?~im', '              ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{13}?~im', '             ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{12}?~im', '            ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{11}?~im', '           ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{10}?~im', '          ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{9}?~im', '         ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{8}?~im', '        ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{7}?~im', '       ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{6}?~im', '      ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{5}?~im', '     ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{4}?~im', '    ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{3}?~im', '   ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{2}?~im', '  ', $fileCodeContent);
	$fileCodeContent = preg_replace('~^[\t]{1}?~im', ' ', $fileCodeContent);

	// Убираем конструкции типа 'asdf'			=>			 или $var			= 		'val';
	$fileCodeContent = preg_replace('~[\s\t]{1,}?=>[\s\t]{1,}?~im', ' => ', $fileCodeContent);
	$fileCodeContent = preg_replace('~[\s\t]{1,}?=[\s\t]{1,}?~im', ' => ', $fileCodeContent);

	// удаляем пустые строки
	$fileCodeContent = preg_replace('~^[\s\t]*?\n~im', "", $fileCodeContent);

	$fileCodeContent = htmlspecialchars($fileCodeContent);
	$fileCodeContent = str_replace(' ', '&nbsp;', $fileCodeContent);

	$printContent .= '<h1>Файл: '.$filePath.'.</h1>'."\n";
	$printContent .= '<pre><code>'.$fileCodeContent.'</code></pre>';
	$printContent .= "\n\n";
}

$printContent .= <<<HTML
	</body>
</html>
HTML;

file_put_contents(__DIR__.'/print.html', $printContent);