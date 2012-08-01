<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

$frameworkDir = dirname(__DIR__);

$parameters = array(
	'wwwDir' => __DIR__,
	"rootDir" => __DIR__,
	"appDir" => __DIR__ . "/app",
	"configDir" => __DIR__ . "/config",
	"logDir" => __DIR__ . "/log",
	"tempDir" => __DIR__ . "/temp",
	'dataDir' => __DIR__,
	'wwwCacheDir' => __DIR__,
	'resourcesDir' => __DIR__,
	"environment" => "testing",
	"debugMode" => true,
);

$sources = array(
	$frameworkDir . '/vendor',
	$frameworkDir . '/../../../vendor',
);

foreach($sources as $dir){
	if(file_exists($dir . '/autoload.php')){
		$parameters["libsDir"] = $dir;
		break;
	}
}

if(!isset($parameters["libsDir"])) {
	die("You must load vendors first\n");
}

require_once $parameters['libsDir'] . '/autoload.php';

$configurator = new \Venne\Testing\Configurator($parameters);
//$configurator->enableDebugger();
//$configurator->enableLoader();