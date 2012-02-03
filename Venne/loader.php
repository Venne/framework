<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne;

use Nette\Diagnostics\Debugger, Nette\Application\Routers\SimpleRouter, Nette\Application\Routers\Route, Nette\Config\Adapters\NeonAdapter;

define('VENNE', TRUE);
define('VENNE_DIR', __DIR__);
define('VENNE_VERSION_ID', '2.0000');
define('VENNE_VERSION_STATE', 'alpha');

/**
 * Loader for Venne:CMS
 *
 * @author	 Josef Kříž
 *
 * @property string $rootDir
 * @property string $tempDir
 * @property string $libsDir
 * @property string $venneDir
 * @property string $appDir
 * @property string $wwwDir
 * @property string $configDir
 * @property string $wwwCacheDir
 * @property string $resourcesDir
 * @property string $flagsDir
 */
class Loader {

	/** @var array */
	protected $parameters = array();


	/** @var Configurator */
	protected $configurator;



	/**
	 * Constructor.
	 *
	 * @param $rootDir
	 */
	public function __construct($rootDir)
	{
		$this->parameters['rootDir'] = realpath($rootDir);
		$this->defaultParameters();
	}



	/**
	 * Set default parameters.
	 */
	protected function defaultParameters()
	{
		$this->parameters['tempDir'] = $this->parameters['rootDir'] . '/temp';
		$this->parameters['libsDir'] = $this->parameters['rootDir'] . '/libs';
		$this->parameters['logDir'] = $this->parameters['rootDir'] . '/log';
		$this->parameters['netteDir'] = $this->parameters['libsDir'] . '/Nette';
		$this->parameters['venneDir'] = $this->parameters['libsDir'] . '/Venne';
		$this->parameters['appDir'] = $this->parameters['rootDir'] . '/app';
		$this->parameters['wwwDir'] = $this->parameters['rootDir'] . '/www';
		$this->parameters['configDir'] = $this->parameters['appDir'] . '/config';
		$this->parameters['wwwCacheDir'] = $this->parameters['wwwDir'] . '/cache';
		$this->parameters['resourcesDir'] = $this->parameters['wwwDir'] . '/resources';
		$this->parameters['flagsDir'] = $this->parameters['rootDir'] . '/flags';
	}



	/**
	 * Set property
	 *
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value)
	{
		if (!isset($this->parameters[$name])) {
			die('Property ' . $name . ' not exist');
		}

		$this->parameters[$name] = $value;
	}



	/**
	 * Get property
	 *
	 * @param $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (!isset($this->parameters[$name])) {
			die('Property ' . $name . ' not exist');
		}

		return $this->parameters[$name];
	}



	/**
	 * Get container.
	 *
	 * @return DI\Container
	 */
	public function getContainer()
	{
		/* Load Nette */
		require_once $this->parameters['netteDir'] . "/loader.php";
		require_once $this->parameters['venneDir'] . "/Configurator.php";


		/* Detect and prepare configuration files */
		if (!is_readable($this->parameters['configDir'] . "/global.neon") && !is_readable($this->parameters['configDir'] . "/global.orig.neon")) {
			die("Your config files are not readable");
		}
		$configName = $this->parameters["configDir"] . "/global.neon";
		if (!file_exists($this->parameters["configDir"] . "/global.neon")) {
			if (is_writable($this->parameters["configDir"])) {
				umask(0000);
				copy($this->parameters["configDir"] . "/global.orig.neon", $this->parameters["configDir"] . "/global.neon");
			} else {
				$configName = $this->parameters["configDir"] . "/global.orig.neon";
			}
		}


		/* Load configuration files */
		$adapter = new NeonAdapter();
		$config = $adapter->load($configName);
		$modeConfigName = $this->parameters["configDir"] . "/config." . $config["parameters"]["mode"] . ".neon";
		if (!file_exists($this->parameters["configDir"] . "/config." . $config["parameters"]["mode"] . ".neon")) {
			if (is_writable($this->parameters["configDir"])) {
				umask(0000);
				file_put_contents($modeConfigName, "");
			} else {
				$modeConfigName = "";
			}
		}


		/* Check of temporary directory */
		if (!is_writable($this->parameters['tempDir'])) {
			die("Your temporary directory is not writable");
		}


		/* Configurator */
		$this->configurator = $configurator = new Configurator($config["parameters"]["modules"]);
		$configurator->setTempDirectory($this->parameters['tempDir']);
		$robotLoader = $configurator->createRobotLoader();
		$robotLoader
			->addDirectory($this->parameters["libsDir"])
			->addDirectory($this->parameters["appDir"])
			->register();
		$configurator->addParameters($this->parameters);
		$configurator->addParameters($config);
		$configurator->addConfig($configName, Configurator::NONE);
		if ($modeConfigName) {
			if (!is_readable($modeConfigName)) {
				die("Your config files are not readable");
			}
			$configurator->addConfig($modeConfigName, Configurator::NONE);
		}


		/* Create DI and run application */
		$container = $configurator->createContainer();
		$container->addService("robotLoader", $robotLoader);
		return $container;
	}



	/**
	 * Run Venne:CMS
	 */
	public function run()
	{
		$container = $this->getContainer();
		$application = $container ->application;
		$configurator = $this->configurator;


		/* Debugger */
		$debugger = $container->parameters["debugger"];
		Debugger::$strictMode = true;
		Debugger::enable($debugger['developerIp'] && $configurator->isProductionMode() ? (array)$debugger['developerIp'] : $configurator->isProductionMode(), $debugger['logDir'], $debugger['logEmail']);
		Debugger::$logger->mailer = array("\\Venne\\Diagnostics\\Logger", "venneMailer");
		\Nette\Diagnostics\Logger::$emailSnooze = $container->parameters["debugger"]["emailSnooze"];
		Debugger::$logDirectory = $container->parameters["logDir"];
		\Venne\Diagnostics\Logger::$linkPrefix = "http://" . $container->httpRequest->url->host . $container->httpRequest->url->basePath . "admin/system/log/show?name=";

		$application->run();
	}

}


