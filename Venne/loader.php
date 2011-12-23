<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne;

use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\SimpleRouter,
	Nette\Application\Routers\Route,
	Nette\Config\Adapters\NeonAdapter;

/**
 * Load Nette framework
 */
require_once $parameters['libsDir'] . "/Nette/loader.php";
require_once $parameters['venneDir'] . "/Configurator.php";
/**
 * Load and configure Venne:CMS
 */
define('VENNE', TRUE);
define('VENNE_DIR', __DIR__);
define('VENNE_VERSION_ID', '2.0000');
define('VENNE_VERSION_STATE', 'alpha');

if(!is_readable($parameters['configDir'] . "/global.neon") OR !is_readable($parameters['configDir'] . "/global.orig.neon")){
	die("Your config files are not readable");
}

$configName = $parameters["configDir"] . "/global.neon";
if(!file_exists($parameters["configDir"] . "/global.neon")){
	if(is_writable($parameters["configDir"])){
		umask(0000);
		copy($parameters["configDir"] . "/global.orig.neon", $parameters["configDir"] . "/global.neon");
	}else{
		$configName = $parameters["configDir"] . "/global.orig.neon";
	}
}

$adapter = new NeonAdapter();
$config = $adapter->load($configName);

$modeConfigName = $parameters["configDir"] . "/config." . $config["parameters"]["mode"] . ".neon";
if(!file_exists($parameters["configDir"] . "/config." . $config["parameters"]["mode"] . ".neon")){
	if(is_writable($parameters["configDir"])){
		umask(0000);
		file_put_contents($modeConfigName, "");
	}else{
		$modeConfigName = "";
	}
}

if(!is_writable($parameters['tempDir'])){
	die("Your temporary directory is not writable");
}

$configurator = new Configurator($config["parameters"]["modules"]);
$configurator->setTempDirectory($parameters['tempDir']);
$robotLoader = $configurator->createRobotLoader();
$robotLoader->addDirectory($parameters["libsDir"])
		->addDirectory($parameters["appDir"])
		->addDirectory($parameters["wwwDir"] . "/themes")
		->register();

$configurator->addParameters($parameters);
$configurator->addParameters($config);
$configurator->addConfig($configName, Configurator::NONE);
if($modeConfigName){
	if(!is_readable($modeConfigName)){
		die("Your config files are not readable");
	}
	$configurator->addConfig($modeConfigName, Configurator::NONE);
}

$container = $configurator->createContainer();
$container->addService("robotLoader", $robotLoader);
$application = $container->application;
$application->run();
