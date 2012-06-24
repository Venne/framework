<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

$rootDir = dirname(__DIR__);

// load Venne:CMS
require_once $rootDir . '/Venne/loader.php';

$parameters = array(
	"rootDir" => __DIR__,
	"venneDir" => __DIR__ . '/../Venne',
	"appDir" => __DIR__ . "/app",
	"configDir" => __DIR__ . "/config",
	"logDir" => __DIR__ . "/log",
	"tempDir" => __DIR__ . "/temp",
	"environment" => "testing",
	"debugMode" => true,
);

$sources = array(
	$rootDir . '/vendor',
	$rootDir . '/../../../vendor',
);

foreach($sources as $dir){
	if(file_exists($dir . "/nette/nette/Nette/loader.php")){
		$parameters["libsDir"] = $dir;
		break;
	}
}

if(!isset($parameters["libsDir"])) {
	die("You must load vendors first\n");
}

$configurator = new \Venne\Testing\Configurator($parameters);
//$configurator->enableDebugger();
$configurator->enableLoader();