<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

// load Venne:CMS
require_once dirname(__DIR__) . '/Venne/loader.php';

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

foreach(array(dirname(__DIR__ . '/vendor/nette/nette/Nette') , dirname(__DIR__) . "/../../../vendor/nette/nette/Nette") as $dir){
	if(file_exists($dir . "/loader.php")){
		$parameters["libsDir"] = $dir;
		break;
	}
}

if(!isset($parameters["libsDir"])) {
	die("You must load vendors first");
}

$configurator = new \Venne\Tests\Configurator($parameters);
//$configurator->enableDebugger();
$configurator->enableLoader();