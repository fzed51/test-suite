<?php

use Core\Autoloader;

date_default_timezone_set('Europe/Paris');

define('DS', DIRECTORY_SEPARATOR);
define('WS', '/');
define('ROOT', __DIR__ . DS);
define('ROOT_TMP', ROOT . 'tmp' . DS);
define('ROOT_VUE', ROOT . 'app' . DS . 'vue' . DS);
$directory = basename(ROOT);
$tabUrl = explode($directory, filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL));
if (count($tabUrl) > 1) {
	define('WEBROOT', $tabUrl[0] . $directory . WS);
} else {
	define('WEBROOT', WS);
}

require ROOT . 'core' . DS . 'autoloader.class.php';
$autoloader = new Autoloader();
$autoloader->activeCache(true)
		->addExtension('.php')
		->addExtension('.class.php')
		->addFolder(ROOT, true)
		->addFolder(ROOT . 'lib', true)
		->register();

function array_union(&$array1, $array2) {
	foreach ($array2 as $value) {
		array_push($array1, $value);
	}
}

function scanFolder($folder) {
	$lstFileTest = [];
	$lstFSO = scandir($folder);
	$bad = ['.', '..'];
	foreach ($lstFSO as $fso) {
		if (!in_array($fso, $bad)) {
			$fsoFull = $folder . DS . $fso;
			if (is_dir($fsoFull)) {
				scanFolder($fsoFull);
			} elseif (is_file($fsoFull)) {
				if (substr($fso, 0, 6) == 'test__' && substr($fso, -4) == '.php' && is_file($folder . DS . substr($fso, 6))) {
					$urlFso = urlencode(
							str_replace(ROOT, '{ROOT}', $folder . DS . substr($fso, 6))
					);
					$csrf = new Core\Securite\Csrf();
					echo "<p><a href=\"?f=$urlFso&{$csrf->getAttrb()}\">$fso</a></p>\n";
					require $fsoFull;
				}
			}
		}
	}
}

function runTest(array $folders) {
	?><!DOCTYPE html>
	<html lang="fr">
		<head>
			<meta charset="utf-8" />
			<title> Test Suite </title>
			<style>
				body{
					font-family: sans-serif;
					font-size: 100%
				}
				*>.hiddable{
					display: none;
				}
				*:hover>.hiddable{
					display: block;
				}
				*>.ihiddable{
					display: block;
				}
				*:hover>.ihiddable{
					display: none;
				}
				.section{
					border-radius: 10px;
					border: 1px solid #000;
					background-color: #ddd;
					max-width: 1024px;
					margin: 10px auto;
					padding: 5px;
				}
				.fichiers{}
				.fichiers .fichiers_liste{}
				.tests{}
				.tests .tests_liste{}
				.tests .class_name{
					font-weight: bold;
				}
				.tests .methode_name{}
				.tests .test_methode{}
				.tests .test_methode p{
					line-height: 20px;
				}
				.ok{
					background-color: rgba(0,255,0,0.1);
				}
				.ko{
					background-color: rgba(255,0,0,0.1);
				}
				.tests .test_methode_comment{}
				.tests .test_methode_resume{
					float:right;
				}
				.tests .test_class{}
				.tests .test_class_resume{}
			</style>
		</head>
		<body>
			<h1>Test suite</h1>
			<div class="section fichiers">
				<h2>Fichiers chargés</h2>
				<div class="hiddable fichiers_liste">
					<?php
					// charge tous les fichiers de test
					foreach ($folders as $folder) {
						$folder = __DIR__ . DS . $folder;
						if (is_dir($folder)) {
							scanFolder($folder);
						}
					}
					?>
				</div>
			</div>
			<div class="section tests">
				<h2>Résultats des test</h2>
				<div class="tests_liste">
					<?php
					// recherche des class de test
					$liste_class_chargee = get_declared_classes();
					foreach ($liste_class_chargee as $class) {
						if (is_subclass_of($class, 'Core\Test')) {
							$test = new $class();
							$test->run();
						}
					}
					?>
				</div>
			</div>
		</body>
	</html>
	<?php
}

function afficheSourceTestee($fileName) {
	$tab = unserialize(file_get_contents(ROOT . 'utilisation_fichier.srl.txt'));
	$fileContent = file_get_contents($fileName);
	$source = explode("\n", $fileContent);
	$table_content = '';
	$fileread = [];
	if (is_array($tab) && isset($tab[$fileName])) {
		$fileread = $tab[$fileName];
	}
	$numligne = 0;
	foreach ($source as $ligne) {
		$numligne ++;
		$ligne_lue = false;
		$class_ligne = '';
		if (isset($fileread[$numligne])) {
			$ligne_lue = $fileread[$numligne];
			$class_ligne = "cover";
		}
		if (!$ligne_lue) {
			if (preg_match('/[a-zA-Z0-9]+/', $ligne) > 0) {
				$class_ligne = "uncover";
			}
		}
//		$ligne = highlight_string($ligne, true);
		$ligne = htmlentities(rtrim($ligne));
		$ligne = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $ligne);
		$ligne = str_replace("    ", "&nbsp;&nbsp;&nbsp;&nbsp;", $ligne);
		$table_content .= "<tr class=\"$class_ligne\"><td><tt>$numligne</tt></td><td><tt>$ligne</tt></td></tr>";
	}
	?>
	<html>
		<head>
			<title>Source de <?= basename($fileName); ?></title>
			<style>
				html{
					font: 400 normal 12px/14px sans-serif;
				}
				.source{
					border: #666666 solid 2px;
					border-radius: 10px;
					max-width: 80em;
					margin: 0 auto;
					padding: 10px;
					color: #ddd;
					background-color: #444;
				}
				.source *{
					color:#ddd;
				}
				.source .uncover{
					background-color: rgba(255,0,0,0.2);
				}
				.source .cover{
					background-color: rgba(0,255,0,0.2);
				}

				.bouton{
					box-sizing: border-box;
					display: inline-block;
					height: 36px;
					width: 36px;
					line-height: 32px;
					border: 1px solid #ddd;
					border-radius: 3px;
					background-color: #666;
					text-decoration: none;
					text-align: center;
				}

				.bouton & * {
					padding-left: 32px
				}
			</style>
		</head>
		<body>
			<div class="source">
				<h1>
					<a href="?" title="go back" class="bouton home">&lt;&lt;</a>
					<span title="<?= htmlentities($fileName); ?>"><?= basename($fileName); ?></span>
				</h1>
				<table><?= $table_content; ?></table>
			</div>
		</body>
	</html>"
	<?php
}

//
//$tab = [];
//$profile = function () use(&$tab) {
//    $backtraces = debug_backtrace();
//    //array_push($tab, [ 'file' => $backtrace[0]['file'], 'line'=> $backtrace[0]['line'] );
//    foreach ($backtraces as $backtrace) {
//        if (isset($backtrace['file'])) {
//            $file = $backtrace['file'];
//        } else {
//            $file = '?';
//        }
//        if (isset($backtrace['file'])) {
//            $line = $backtrace['line'];
//        } else {
//            $line = '?';
//        }
//        if (!isset($tab[$file])) {
//            $tab[$file] = [];
//        }
//        $tab[$file][(string) $line] = true;
//    }
//};
if (!isset($_GET['f'])) {
	//declare(ticks = 1);
	//register_tick_function($profile);
	xdebug_start_code_coverage();
	try {
		runTest(['core', 'app']);
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	$couverture = xdebug_get_code_coverage();
	xdebug_stop_code_coverage();
	file_put_contents(ROOT . 'utilisation_fichier.srl.txt', serialize($couverture));
} else {
	$fileName = str_replace('{ROOT}', ROOT, urldecode($_GET['f']));
	if (is_file($fileName)) {
		afficheSourceTestee($fileName);
	} else {
		header('location: ' . filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL), true, 404);
	}
}
