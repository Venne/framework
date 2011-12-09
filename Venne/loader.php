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
	Nette\Application\Routers\Route;

/**
 * Load Nette framework
 */
require_once $params['libsDir'] . "/Nette/loader.php";
require_once $params['venneDir'] . "/Configurator.php";

/**
 * Load and configure Venne:CMS
 */
define('VENNE', TRUE);
define('VENNE_DIR', __DIR__);
define('VENNE_VERSION_ID', '2.0000');
define('VENNE_VERSION_STATE', 'alpha');

require_once $params['venneDir'] . '/DI/Container.php';
require_once $params['venneDir'] . '/Module/Container.php';

$configFile = $params['appDir'] . '/config.neon';
if(!file_exists($configFile)){
	Debugger::enable(Debugger::DEVELOPMENT);
	$configOrigFile = $params['appDir'] . '/config.orig.neon';
	if(!is_writable($params['appDir'])){
		$configFile = $configOrigFile;
	}else{
		copy($configOrigFile, $configFile);
	}
}
$configurator = new Configurator($params);
$container = $configurator->loadConfig($configFile, $configurator->container->params['mode']);
$application = $container->application;
$application->catchExceptions = (bool) Debugger::$productionMode;
$application->errorPresenter = $container->params['website']['errorPresenter'];
