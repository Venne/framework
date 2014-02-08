<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

if (file_exists(__DIR__ . '/../../../autoload.php')) {
	/** @var $loader Composer\Autoload\ClassLoader */
	$loader = require_once __DIR__ . '/../../../autoload.php';
	$sandbox = dirname(dirname(dirname(dirname(__DIR__))));

} elseif (file_exists(__DIR__ . '/../autoload.php')) {
	/** @var $loader Composer\Autoload\ClassLoader */
	$loader = require_once __DIR__ . '/../autoload.php';
	$sandbox = dirname(dirname(__DIR__));

} else {
	die('autoload.php file can not be found.');
}

// create and run application
$configurator = new \Venne\Config\Configurator($sandbox . '/app', $loader);
$configurator->enableDebugger();
$configurator->enableLoader();
$configurator->getContainer()->application->run();
