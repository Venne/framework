<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

// load Venne:CMS
require_once __DIR__ . '/../Venne/loader.php';

$rootDir = __DIR__ . "/..";

$parameters = array(
	"rootDir" => $rootDir,
	"appDir" => $rootDir . "/tests/app",
	"libsDir" => $rootDir,
	"configDir" => $rootDir . "/tests/config",
	"logDir" => $rootDir . "/tests/log",
	"tempDir" => $rootDir . "/tests/temp",
);

$configurator = new \Venne\Tests\Configurator($parameters);
//$configurator->enableDebugger();
$configurator->enableLoader();