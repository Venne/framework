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

	/** @var \Nette\DI\Container */
	protected $container;

	/** @var \Nette\Loaders\RobotLoader */
	protected $robotLoader;

	/** @var Compiler */
	protected $compiler;



	public function __construct($parameters = NULL, $modules = NULL, $productionMode = NULL)
	{
		$this->parameters = $this->getDefaultParameters($parameters);
		$this->modules = $this->getDefaultModules($modules);
		$this->setProductionMode($productionMode);
		$this->setTempDirectory($this->parameters["tempDir"]);
	}



	protected function getDefaultModules($modules = NULL)
	{
		$ret = array();

		$adapter = new NeonAdapter();
		if (file_exists($this->parameters["configDir"] . "/modules.neon")) {
			$ret = $adapter->load($this->parameters["configDir"] . "/modules.neon");
		} else {
			$ret = $adapter->load($this->parameters["configDir"] . "/modules.orig.neon");
		}

		return is_array($modules) ? array_merge($ret, $modules) : $ret;
	}



	protected function getDefaultParameters($parameters = NULL)
	{
		$parameters = (array)$parameters;

		$ret = array(
			'wwwDir' => isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : NULL,
			'productionMode' => static::detectProductionMode(),
			'environment' => static::detectProductionMode() ? self::PRODUCTION : self::DEVELOPMENT,
			'consoleMode' => PHP_SAPI === 'cli',
			'container' => array(
				'class' => 'SystemContainer',
				'parent' => 'Nette\DI\Container',
			)
		);
		$ret = $parameters + $ret;
		$ret["venneModeInstallation"] = false;
		$ret["venneModeAdmin"] = false;
		$ret["venneModeFront"] = false;
		$ret['rootDir'] = isset($parameters['rootDir']) ? $parameters['rootDir'] : dirname($ret['wwwDir']);
		$ret['tempDir'] = $ret['rootDir'] . '/temp';
		$ret['tempDir'] = $ret['rootDir'] . '/temp';
		$ret['libsDir'] = $ret['rootDir'] . '/libs';
		$ret['logDir'] = $ret['rootDir'] . '/log';
		$ret['netteDir'] = $ret['libsDir'] . '/Nette';
		$ret['venneDir'] = $ret['libsDir'] . '/Venne';
		$ret['appDir'] = $ret['rootDir'] . '/app';
		$ret['configDir'] = $ret['appDir'] . '/config';
		$ret['wwwCacheDir'] = $ret['wwwDir'] . '/cache';
		$ret['resourcesDir'] = $ret['wwwDir'] . '/resources';
		$ret['flagsDir'] = $ret['rootDir'] . '/flags';
		if ($parameters) {
			$ret += $parameters;
		}
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


		// start debugger
		$this->runDebugger($container);


		// register robotLoader and configurator
		$container->addService("robotLoader", $this->robotLoader);
		$container->addService("configurator", $this);


		// parameters
		$baseUrl = rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');
		$container->parameters['baseUrl'] = $baseUrl;
		$container->parameters['basePath'] = preg_replace('#https?://[^/]+#A', '', $baseUrl);


		// setup Application
		$application = $container->application;
		$application->catchExceptions = (bool)$this->isProductionMode();
		$application->errorPresenter = $container->parameters['website']['errorPresenter'];
		$application->onShutdown[] = function()
		{
			\Venne\Panels\Stopwatch::stop("shutdown");
		};


		// initialize modules
		foreach ($container->findByTag("module") as $module => $par) {
			$container->{$module}->configure($container);
		}


		// detect updated flag
		if (file_exists($this->parameters['flagsDir'] . "/updated")) {
			$dirContent = \Nette\Utils\Finder::find('*')->from($this->parameters['tempDir'] . "/cache")->childFirst();
			foreach ($dirContent as $file) {
				if ($file->isDir()) @rmdir($file->getPathname()); else
					@unlink($file->getPathname());
			}
			@unlink($directory);
			@unlink($this->parameters['flagsDir'] . "/updated");
			$container->eventManager->dispatchEvent(\Venne\Module\Events\Events::onUpdateFlag);
		}


		// set timer to router
		$container->application->onStartup[] = function()
		{
			\Venne\Panels\Stopwatch::start();
		};
		$container->application->onRequest[] = function()
		{
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

		foreach ($this->modules as $module) {
			$class = "\\App\\" . ucfirst($module) . "Module\\Module";
			$instance = new $class;
			$instance->compile($this->compiler);
		}

		return $this->compiler;
	}



	protected function getConfigFiles()
	{
		$configs = array();

		$configList = array(
			"modules" => array(
				"orig" => $this->parameters['configDir'] . "/modules.orig.neon",
				"config" => $this->parameters['configDir'] . "/modules.neon"
			),
			"config" => array(
				"orig" => $this->parameters['configDir'] . "/global.orig.neon",
				"config" => $this->parameters['configDir'] . "/global.neon"
			),
		);

		foreach ($configList as $name => $item) {
			/* Detect and prepare configuration files */
			if (!is_readable($item["config"]) && !is_readable($item["orig"])) {
				die("Your config files are not readable");
			}
			if (!file_exists($item["config"])) {
				if (is_writable($this->parameters["configDir"])) {
					umask(0000);
					copy($item["orig"], $item["config"]);
					if ($name == "config") {
						$configs[] = $item["config"];
					}
				} else {
					if ($name == "config") {
						$configs[] = $item["orig"];
					}
				}
			} else {
				if ($name == "config") {
					$configs[] = $item["config"];
				}
			}
		}

		return $configs;
	}



	/**
	 * Sets path to temporary directory.
	 *
	 * @return Configurator  provides a fluent interface
	 */
	public function setTempDirectory($path)
	{
		parent::setTempDirectory($path);
		if (!is_dir($sessionDir = $path . "/sessions")) {
			umask(0000);
			mkdir($sessionDir, 0777);
		}
		return $this;
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
		$this->parameters["logDir"] = $logDirectory;
		$this->parameters["debugger"] = array();
		$this->parameters["debugger"]["emailSnooze"] = $email;
	}



	protected function runDebugger($container)
	{
		if (isset($this->parameters["debugger"]) && $this->parameters["debugger"]) {
			$debugger = $container->parameters["debugger"];

			$this->setProductionMode($debugger["mode"] == "production");

			Debugger::$strictMode = true;
			Debugger::enable($debugger['developerIp'] && $this->isProductionMode() ? (array)$debugger['developerIp'] : $this->isProductionMode(), $debugger['logDir'], $debugger['logEmail']);
			Debugger::$logger->mailer = array("\\Venne\\Diagnostics\\Logger", "venneMailer");
			\Nette\Diagnostics\Logger::$emailSnooze = $this->parameters["debugger"]["emailSnooze"] ? : $container->parameters["debugger"]["emailSnooze"];
			Debugger::$logDirectory = $container->parameters["logDir"];
			\Venne\Diagnostics\Logger::$linkPrefix = "http://" . $container->httpRequest->url->host . $container->httpRequest->url->basePath . "admin/system/log/show?name=";
		}
	}



	/**
	 * @return \Nette\Config\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}


}
