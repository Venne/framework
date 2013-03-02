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
use Nette\DI;
use Nette\Caching\Cache;
use Nette\Config\Compiler;
use Nette\Config\Adapters\NeonAdapter;
use Nette\Diagnostics\Debugger;
use Nette\Application\Routers\SimpleRouter;
use Nette\Application\Routers\Route;
use Nette\InvalidArgumentException;
use Nette\Application\ApplicationException;
use Venne\Module\ModuleManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Configurator extends \Nette\Config\Configurator
{

	/** @var string|array */
	protected $sandbox;

	/** @var \Nette\DI\Container */
	protected $container;

	/** @var \Nette\Loaders\RobotLoader */
	protected $robotLoader;

	/** @var Compiler */
	protected $compiler;

	/** @var \Composer\Autoload\ClassLoader */
	protected $classLoader;


	/**
	 * @param $sandboxDir
	 */
	public function __construct($sandbox, \Composer\Autoload\ClassLoader $classLoader = NULL)
	{
		$this->sandbox = $sandbox;
		$this->classLoader = $classLoader;

		try {
			$this->parameters = $this->getSandboxParameters();
			$this->validateConfiguration();
			$this->parameters = $this->getDefaultParameters($this->parameters);
			$this->setTempDirectory($this->parameters['tempDir']);

			if ($this->classLoader) {
				$this->registerModuleLoaders();
			}
		} catch (ApplicationException $e) {
			die($e->getMessage());
		}
	}


	protected function registerModuleLoaders()
	{
		foreach ($this->parameters['modules'] as $name => $items) {
			if (isset($items['autoload']['psr-0'])) {
				foreach ($items['autoload']['psr-0'] as $key => $val) {
					$this->classLoader->add($key, $items['path'] . '/' . $val);
				}
			}
			if (isset($items['autoload']['files'])) {
				foreach ($items['autoload']['files'] as $file) {
					include_once $items['path'] . '/' . $file;
				}
			}
		}
	}


	protected function validateConfiguration()
	{
		$mandatoryConfigs = array('settings.php', 'config.neon');

		foreach ($mandatoryConfigs as $config) {
			if (!file_exists($this->parameters['configDir'] . '/' . $config)) {
				$origFile = $this->parameters['configDir'] . '/' . $config . '.orig';
				if (file_exists($origFile)) {
					if (is_writable($this->parameters['configDir']) && file_exists($origFile)) {
						copy($origFile, $this->parameters['configDir'] . '/' . $config);
					} else {
						throw new ApplicationException("Config directory is not writable.");
					}
				} else {
					throw new ApplicationException("Configuration file '{$config}' does not exist.");
				}
			}
		}
	}


	/**
	 * @param $sandboxDir
	 * @throws \InvalidArgumentException
	 */
	protected function getSandboxParameters()
	{
		$mandatoryParameters = array('wwwDir', 'appDir', 'libsDir', 'logDir', 'dataDir', 'tempDir', 'logDir', 'configDir', 'wwwCacheDir', 'publicDir', 'resourcesDir');

		if (!is_string($this->sandbox) && !is_array($this->sandbox)) {
			throw new InvalidArgumentException("SandboxDir must be string or array, " . gettype($this->sandboxDir) . " given.");
		}

		if (is_string($this->sandbox)) {
			$file = $this->sandbox . '/sandbox.php';
			if (!file_exists($file)) {
				throw new InvalidArgumentException('Sandbox must contain sandbox.php file with path configurations.');
			}
			$parameters = require $file;
		} else {
			$parameters = $this->sandbox;
		}

		foreach ($mandatoryParameters as $item) {
			if (!isset($parameters[$item])) {
				throw new ApplicationException("Sandbox parameters does not contain '{$item}' parameter.");
			}
		}

		return $parameters;
	}


	protected function getDefaultParameters($parameters = NULL)
	{
		$parameters = (array)$parameters;
		$debugMode = isset($parameters["debugMode"]) ? $parameters["debugMode"] : static::detectDebugMode();
		$ret = array(
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
		$settings = require $parameters['configDir'] . '/settings.php';
		foreach ($settings['modules'] as &$module) {
			$module['path'] = \Nette\DI\Helpers::expand($module['path'], $parameters);
		}
		return $settings + $parameters + $ret;
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
	 * @param string $class
	 * @return DI\Container
	 */
	public function createContainer($class = NULL)
	{
		// add config files
		foreach ($this->getConfigFiles() as $file) {
			$this->addConfig($file, self::NONE);
		}

		// create container
		$container = parent::createContainer();

		// register robotLoader and configurator
		if ($this->robotLoader) {
			$container->addService("robotLoader", $this->robotLoader);
		}
		$container->addService("configurator", $this);

		return $container;
	}


	public function buildContainer(& $dependencies = NULL, $class = NULL)
	{
		if ($class) {
			$_class = $this->parameters['container']['class'];
			$this->parameters['container']['class'] = $class;
		}

		return parent::buildContainer($dependencies);

		if ($class) {
			$this->parameters['container']['class'] = $_class;
		}
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
			->addExtension('extensions', new \Venne\Config\Extensions\ExtensionsExtension())
			->addExtension('proxy', new Venne\Config\Extensions\ProxyExtension());
		return $this->compiler;
	}


	protected function createLoader()
	{
		$loader = new Loader();
		$loader->setParameters($this->parameters);
		return $loader;
	}


	protected function getConfigFiles()
	{
		$ret = array();
		$ret[] = $this->parameters['configDir'] . '/config.neon';
		$ret[] = $this->parameters['configDir'] . "/config_{$this->parameters['environment']}.neon";
		return $ret;
	}


	/**
	 * @param  string        error log directory
	 * @param  string        administrator email
	 * @return void
	 */
	public function enableDebugger($logDirectory = NULL, $email = NULL)
	{
		Nette\Diagnostics\Debugger::$strictMode = TRUE;
		Nette\Diagnostics\Debugger::enable(!$this->isDebugMode(), $logDirectory ? : $this->parameters['logDir'], $email);
	}


	/**
	 * Enable robotLoader.
	 */
	public function enableLoader()
	{
		$this->robotLoader = $this->createRobotLoader();
		$this->robotLoader->ignoreDirs .= ', tests, test, resources';
		$this->robotLoader
			->addDirectory($this->parameters['appDir'])
			->register();
	}


	/**
	 * @return \Nette\Config\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}


	/**
	 * @return bool
	 */
	public function isDebugMode()
	{
		return $this->parameters['debugMode'];
	}
}
