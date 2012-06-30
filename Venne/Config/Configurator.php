<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Venne;
use Nette;
use Nette\Caching\Cache;
use Nette\DI;
use Nette\Diagnostics\Debugger;
use Nette\Application\Routers\SimpleRouter;
use Nette\Application\Routers\Route;
use Nette\Config\Compiler;
use Nette\Config\Adapters\NeonAdapter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Configurator extends \Nette\Config\Configurator
{


	/** @var array */
	protected $modules = array();

	/** @var Venne\Module\IModule[] */
	protected $moduleInstances = array();

	/** @var \Nette\DI\Container */
	protected $container;

	/** @var \Nette\Loaders\RobotLoader */
	protected $robotLoader;

	/** @var Compiler */
	protected $compiler;


	public function __construct($parameters = NULL, $modules = NULL)
	{
		$this->parameters = $this->getDefaultParameters($parameters);
		$this->parameters['modules'] = $this->getDefaultModules($modules);
		$this->setTempDirectory($this->parameters["tempDir"]);
	}


	protected function getDefaultModules($modules = NULL)
	{
		$adapter = new NeonAdapter();
		return $adapter->load($this->parameters["configDir"] . "/modules.neon");
	}


	protected function getModuleInstances()
	{
		if (!$this->moduleInstances) {
			foreach ($this->parameters['modules'] as $module => $item) {
				if ($item['status'] == \Venne\Module\ModuleManager::MODULE_STATUS_INSTALLED) {
					$class = "\\" . ucfirst($module) . "Module\\Module";
					$this->moduleInstances[] = new $class;
				}
			}
		}
		return $this->moduleInstances;
	}


	protected function getDefaultParameters($parameters = NULL)
	{
		$parameters = (array)$parameters;
		$debugMode = isset($parameters["debugMode"]) ? $parameters["debugMode"] : static::detectDebugMode();
		$ret = array(
			'wwwDir' => isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : NULL,
			'debugMode' => $debugMode,
			'productionMode' => !$debugMode,
			'environment' => isset($parameters["environment"]) ? $parameters["environment"] :
				($debugMode ? self::DEVELOPMENT : self::PRODUCTION),
			'consoleMode' => PHP_SAPI === 'cli',
			'container' => array(
				'class' => 'SystemContainer',
				'parent' => 'Nette\DI\Container',
			)
		);
		$ret = $parameters + $ret;
		$ret['appDir'] = isset($parameters['appDir']) ? $parameters['appDir'] : dirname($ret['wwwDir']) . '/app';
		$ret['libsDir'] = isset($parameters['libsDir']) ? $parameters['libsDir'] : dirname($ret['wwwDir']) . '/vendor';
		$ret['logDir'] = isset($parameters['logDir']) ? $parameters['logDir'] : $ret['appDir'] . '/log';
		$ret['dataDir'] = isset($parameters['dataDir']) ? $parameters['dataDir'] : $ret['appDir'] . '/data';
		$ret['tempDir'] = isset($parameters['tempDir']) ? $parameters['tempDir'] : $ret['appDir'] . '/temp';
		$ret['logDir'] = isset($parameters['logDir']) ? $parameters['logDir'] : $ret['appDir'] . '/log';
		$ret['configDir'] = isset($parameters['configDir']) ? $parameters['configDir'] : $ret['appDir'] . '/config';
		$ret['wwwCacheDir'] = isset($parameters['wwwCacheDir']) ? $parameters['wwwCacheDir'] : $ret['wwwDir'] . '/cache';
		$ret['resourcesDir'] = isset($parameters['resourcesDir']) ? $parameters['resourcesDir'] : $ret['wwwDir'] . '/resources';
		return $ret;
	}


	/**
	 * @param string $name
	 */
	public function setEnvironment($name)
	{
		$this->parameters["environment"] = $name;
	}


	/**
	 * @return \Nette\DI\Container
	 */
	public function getContainer()
	{
		if (!$this->container) {
			$this->container = $this->createContainer();
		}

		return $this->container;
	}


	/**
	 * Loads configuration from file and process it.
	 *
	 * @return DI\Container
	 */
	public function createContainer()
	{
		// add config files
		foreach ($this->getConfigFiles() as $file) {
			$this->addConfig($file, self::NONE);
		}

		// create container
		$container = parent::createContainer();

		// register robotLoader and configurator
		$container->addService("robotLoader", $this->robotLoader);
		$container->addService("configurator", $this);

		// setup Application
		$application = $container->application;
		$application->catchExceptions = (bool)!$this->isDebugMode();

		return $container;
	}


	/**
	 * @return Compiler
	 */
	protected function createCompiler()
	{
		$this->compiler = parent::createCompiler();
		$this->compiler
			->addExtension('venne', new Venne\Config\Extensions\VenneExtension())
			->addExtension('console', new Venne\Config\Extensions\ConsoleExtension())
		;

		foreach ($this->getModuleInstances() as $instance) {
			$instance->compile($this->compiler);
		}

		return $this->compiler;
	}


	protected function getConfigFiles()
	{
		$configs = array();

		foreach ($this->getModuleInstances() as $instance) {
			$paths = array(
				$instance->getPath() . "/Resources/config/config.neon",
				$this->parameters['configDir'] . "/" . $instance->getName() . "/config.neon"
			);
			foreach ($paths as $path) {
				if (is_file($path)) {
					$configs[] = $path;
				}
			}
		}
		$configs[] = $this->parameters['configDir'] . "/config.neon";
		$configs[] = $this->parameters['configDir'] . "/config_" . $this->parameters["environment"] . ".neon";
		return $configs;
	}


	/**
	 * Enable robotLoader.
	 */
	public function enableLoader()
	{
		$this->robotLoader = $this->createRobotLoader();
		$this->robotLoader->ignoreDirs .= ', tests, test, resources';
		$this->robotLoader
			->addDirectory($this->parameters["libsDir"])
			->addDirectory($this->parameters["appDir"])
			->register();
	}


	public function enableDebugger($logDirectory = NULL, $email = NULL)
	{
		Nette\Diagnostics\Debugger::$strictMode = TRUE;
		Nette\Diagnostics\Debugger::enable(
			!$this->parameters['debugMode'], $logDirectory ? : $this->parameters["logDir"], $email ? : $this->parameters["debugger"]["logEmail"]
		);
	}


	/**
	 * @return \Nette\Config\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}


	/**
	 * @return Nette\Loaders\RobotLoader
	 */
	public function createRobotLoader()
	{
		if (!($cacheDir = $this->getCacheDirectory())) {
			throw new Nette\InvalidStateException("Set path to temporary directory using setTempDirectory().");
		}
		$loader = new Venne\Loaders\RobotLoader;
		$loader->setCacheStorage(new \Nette\Caching\Storages\FileStorage($cacheDir));
		$loader->autoRebuild = !$this->parameters['productionMode'];
		return $loader;
	}

}
