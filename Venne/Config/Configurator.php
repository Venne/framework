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


	const CACHE_NAMESPACE = 'Nette.Configurator';

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
		$this->modules = $this->getDefaultModules($modules);
		$this->setTempDirectory($this->parameters["tempDir"]);
		$this->checkFlags();
	}



	protected function checkFlags()
	{
		// detect updated flag
		if (file_exists($this->parameters['flagsDir'] . "/updated")) {
			$cache = new Cache(new \Nette\Caching\Storages\PhpFileStorage($cacheDir), self::CACHE_NAMESPACE);
			$cache->clean();
			@unlink($this->parameters['flagsDir'] . "/updated");
		}
	}



	protected function getDefaultModules($modules = NULL)
	{
		$ret = array();

		$adapter = new NeonAdapter();
		$ret = $adapter->load($this->parameters["configDir"] . "/modules.neon");

		return is_array($modules) ? array_merge($ret, $modules) : $ret;
	}



	protected function getModuleInstances()
	{
		if (!$this->moduleInstances) {
			foreach ($this->modules as $module) {
				$class = "\\" . ucfirst($module) . "Module\\Module";
				$this->moduleInstances[] = new $class;
			}
		}
		return $this->moduleInstances;
	}



	protected function getDefaultParameters($parameters = NULL)
	{
		$parameters = (array) $parameters;
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
		$ret['flagsDir'] = isset($parameters['flagsDir']) ? $parameters['flagsDir'] : $ret['appDir'] . '/flags';
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
		\Venne\Panels\Stopwatch::start();
		$container = parent::createContainer();
		\Venne\Panels\Stopwatch::stop("generate container");
		\Venne\Panels\Stopwatch::start();


		// register robotLoader and configurator
		$container->addService("robotLoader", $this->robotLoader);
		$container->addService("configurator", $this);


		// parameters
		$baseUrl = rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');
		$container->parameters['baseUrl'] = $baseUrl;
		$container->parameters['basePath'] = preg_replace('#https?://[^/]+#A', '', $baseUrl);


		// setup Application
		$application = $container->application;
		$application->catchExceptions = (bool) !$this->isDebugMode();
		$application->errorPresenter = $container->parameters['website']['errorPresenter'];
		$application->onShutdown[] = function() {
					\Venne\Panels\Stopwatch::stop("shutdown");
				};


		// initialize modules
		foreach ($container->findByTag("module") as $module => $par) {
			$container->{$module}->configure($container);
		}


		// set timer to router
		$container->application->onStartup[] = function() {
					\Venne\Panels\Stopwatch::start();
				};
		$container->application->onRequest[] = function() {
					\Venne\Panels\Stopwatch::stop("routing");
				};


		\Venne\Panels\Stopwatch::stop("container configuration");
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
				->addExtension('doctrine', new Venne\Config\Extensions\DoctrineExtension())
				->addExtension('assets', new Venne\Config\Extensions\AssetExtension());

		foreach ($this->getModuleInstances() as $instance) {
			$instance->compile($this->compiler);
		}

		return $this->compiler;
	}



	protected function getConfigFiles()
	{
		$configs = array(
			$this->parameters['configDir'] . "/config.neon",
			$this->parameters['configDir'] . "/config_" . $this->parameters["environment"] . ".neon",
		);

		foreach ($this->getModuleInstances() as $instance) {
			$path = $instance->getPath() . "/public/config/config.neon";
			if (is_file($path)) {
				$configs[] = $path;
			}
		}

		return $configs;
	}



	/**
	 * Enable robotLoader.
	 */
	public function enableLoader()
	{
		$this->robotLoader = $this->createRobotLoader();
		$this->robotLoader
				->addDirectory($this->parameters["libsDir"])
				->addDirectory($this->parameters["appDir"])
				->register();
	}



	public function buildContainer(& $dependencies = NULL)
	{
		return parent::buildContainer($dependencies);
	}



	public function enableDebugger($logDirectory = NULL, $email = NULL)
	{
		Nette\Diagnostics\Debugger::$strictMode = TRUE;
		Nette\Diagnostics\Debugger::enable(
				!$this->parameters['debugMode'], $logDirectory ? : $this->parameters["debugger"]["logDir"], $email ? : $this->parameters["debugger"]["logEmail"]
		);
	}



	/**
	 * @return \Nette\Config\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

}
