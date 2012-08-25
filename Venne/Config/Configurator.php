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

	/** @var string|array */
	protected $sandbox;

	/** @var \Nette\DI\Container */
	protected $container;

	/** @var \Nette\Loaders\RobotLoader */
	protected $robotLoader;

	/** @var Compiler */
	protected $compiler;


	/**
	 * @param $sandboxDir
	 */
	public function __construct($sandbox)
	{
		$this->sandbox = $sandbox;

		$this->parameters = $this->getSandboxParameters();
		$this->validateConfiguration();
		$this->parameters = $this->getDefaultParameters($this->parameters);
		$this->setTempDirectory($this->parameters["tempDir"]);
	}


	protected function validateConfiguration()
	{
		$mandatoryConfigs = array('settings.php', 'config.neon');

		foreach ($mandatoryConfigs as $config) {
			if (!file_exists($this->parameters['configDir'] . '/' . $config)) {
				copy($this->parameters['configDir'] . '/' . $config . '.orig', $this->parameters['configDir'] . '/' . $config);
			}
		}
	}


	/**
	 * @param $sandboxDir
	 * @throws \InvalidArgumentException
	 */
	protected function getSandboxParameters()
	{
		$mandatoryParameters = array('wwwDir', 'appDir', 'libsDir', 'logDir', 'dataDir', 'tempDir', 'logDir', 'configDir', 'wwwCacheDir', 'resourcesDir');

		if (!is_string($this->sandbox) && !is_array($this->sandbox)) {
			throw new \InvalidArgumentException("SandboxDir must be string or array, " . gettype($this->sandboxDir) . " given.");
		}

		if (is_string($this->sandbox)) {
			$file = $this->sandbox . '/sandbox.php';
			if (!file_exists($file)) {
				throw new \InvalidArgumentException('Sandbox must contain sandbox.php file with path configurations.');
			}
			$parameters = require $file;
		} else {
			$parameters = $this->sandbox;
		}

		foreach ($mandatoryParameters as $item) {
			if (!isset($parameters[$item])) {
				throw new \Nette\Application\ApplicationException("Sandbox parameters does not contain '{$item}' parameter.");
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
		foreach($settings['modules'] as &$module){
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
			->addExtension('extensions', new \Venne\Config\Extensions\ExtensionsExtension());
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
	 * Enable robotLoader.
	 */
	public function enableLoader()
	{
		$this->robotLoader = $this->createRobotLoader();
		$this->robotLoader->ignoreDirs .= ', tests, test, resources';
		$this->robotLoader
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
}
