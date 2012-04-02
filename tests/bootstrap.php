<?php

// uncomment this line if you must temporarily take down your site for maintenance
// require '.maintenance.php';

// load Venne:CMS
require_once __DIR__ . '/../../../libs/Nette/loader.php';
require_once __DIR__ . '/../../../libs/Venne/loader.php';

\Nette\Diagnostics\Debugger::enable(\Nette\Diagnostics\Debugger::DEVELOPMENT);

$rootDir = dirname(__DIR__ . "/../../../../");

$parameters = array(
	"rootDir" => $rootDir,
	"appDir" => $rootDir . "/app",
	"configDir" => $rootDir . "/app/config",
	"logDir" => __DIR__ . "/log",
	"tempDir" => __DIR__ . "/temp",
);

$configurator = new \Venne\Tests\Configurator($parameters);
$configurator->enableDebugger();
$configurator->enableLoader();