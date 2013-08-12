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

use Composer\Autoload\ClassLoader;
use Nette\Config\Compiler;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use Nette\Loaders\RobotLoader;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Configurator extends \Nette\Config\Configurator
{

	/** @var string|array */
	protected $sandbox;

	/** @var Container */
	protected $container;

	/** @var RobotLoader */
	protected $robotLoader;

	/** @var Compiler */
	protected $compiler;

	/** @var ClassLoader */
	protected $classLoader;


	/**
	 * @param $sandbox
	 * @param ClassLoader $classLoader
	 */
	public function __construct($sandbox, ClassLoader $classLoader = NULL)
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
		} catch (InvalidArgumentException $e) {
			die($e->getMessage());
		}
	}


	protected function registerModuleLoaders()
	{
		foreach ($this->parameters['modules'] as $items) {
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


	/**
	 * @throws InvalidArgumentException
	 */
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
						throw new InvalidArgumentException("Config directory is not writable.");
					}
				} else {
					throw new InvalidArgumentException("Configuration file '{$config}' does not exist.");
				}
			}
		}
	}


	/**
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function getSandboxParameters()
	{
		$mandatoryParameters = array('wwwDir', 'appDir', 'libsDir', 'logDir', 'dataDir', 'tempDir', 'logDir', 'configDir', 'wwwCacheDir', 'publicDir', 'resourcesDir', 'modulesDir');

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
				throw new InvalidArgumentException("Sandbox parameters does not contain '{$item}' parameter.");
			}
		}

		return $parameters;
	}


	/**
	 * @param null $parameters
	 * @return array
	 */
	protected function getDefaultParameters($parameters = NULL)
	{
		$parameters = (array)$parameters;
		$debugMode = isset($parameters['debugMode']) ? $parameters['debugMode'] : static::detectDebugMode();
		$ret = array(
			'debugMode' => $debugMode,
			'environment' => ($e = static::detectEnvironment()) ? $e : ($debugMode ? 'development' : 'production'),
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
		$parameters = $settings + $parameters + $ret;
		$parameters['productionMode'] = !$parameters['debugMode'];
		return $parameters;
	}


	/**
	 * @param string $name
	 * @return Configurator
	 */
	public function setEnvironment($name)
	{
		$this->parameters['environment'] = $name;
		return $this;
	}


	/**
	 * @return Container
	 */
	public function getContainer()
	{
		if (!$this->container) {
			$this->container = $this->createContainer();
		}

		return $this->container;
	}


	/**
	 * @return null|string
	 */
	public static function detectEnvironment()
	{
		return isset($_SERVER['SERVER_NAME'])
			? $_SERVER['SERVER_NAME']
			: (function_exists('gethostname') ? gethostname() : NULL);
	}


	/**
	 * Loads configuration from file and process it.
	 *
	 * @param string $class
	 * @return Container
	 */
	public function createContainer($class = NULL)
	{
		// add config files
		foreach ($this->getConfigFiles() as $file) {
			if (!file_exists($file)) {
				umask(0000);
				@touch($file);
			}

			$this->addConfig($file, self::NONE);
		}

		// create container
		$container = parent::createContainer();

		// register robotLoader and configurator
		if ($this->robotLoader) {
			$container->addService('robotLoader', $this->robotLoader);
		}
		$container->addService('configurator', $this);

		return $container;
	}


	/**
	 * @param null $dependencies
	 * @param null $class
	 * @return Container
	 */
	public function buildContainer(& $dependencies = NULL, $class = NULL)
	{
		if ($class) {
			$_class = $this->parameters['container']['class'];
			$this->parameters['container']['class'] = $class;
		}

		$container = parent::buildContainer($dependencies);

		if ($class) {
			$this->parameters['container']['class'] = $_class;
		}

		return $container;
	}


	/**
	 * @return Compiler
	 */
	protected function createCompiler()
	{
		$this->compiler = parent::createCompiler();
		$this->compiler
			->addExtension('venne', new \Venne\Config\Extensions\VenneExtension())
			->addExtension('console', new \Venne\Config\Extensions\ConsoleExtension())
			->addExtension('extensions', new \Venne\Config\Extensions\ExtensionsExtension())
			->addExtension('proxy', new \Venne\Config\Extensions\ProxyExtension());
		return $this->compiler;
	}


	/**
	 * @return array
	 */
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
	 */
	public function enableDebugger($logDirectory = NULL, $email = NULL)
	{
		$debugMode = $this->isDebugMode();

		if (
			isset($this->parameters['debugModeLogin']['name']) &&
			isset($this->parameters['debugModeLogin']['password'])
		) {
			if (isset($_GET['debugMode'])) {
				if ($_GET['debugMode']) {
					if (
						!isset($_SERVER['PHP_AUTH_USER']) ||
						$_SERVER['PHP_AUTH_USER'] !== $this->parameters['debugModeLogin']['name'] ||
						$_SERVER['PHP_AUTH_PW'] !== $this->parameters['debugModeLogin']['password']
					) {
						header('WWW-Authenticate: Basic realm="Debug mode"');
						header('HTTP/1.0 401 Unauthorized');
						exit;
					}
				}
			}

			if (
				isset($this->parameters['debugModeLogin']['name']) &&
				isset($this->parameters['debugModeLogin']['password']) &&
				isset($_SERVER['PHP_AUTH_USER']) &&
				$_SERVER['PHP_AUTH_USER'] === $this->parameters['debugModeLogin']['name'] &&
				$_SERVER['PHP_AUTH_PW'] === $this->parameters['debugModeLogin']['password']
			) {
				$debugMode = TRUE;
			}
		}

		Debugger::$strictMode = TRUE;
		Debugger::enable(!$debugMode, $logDirectory ? : $this->parameters['logDir'], $email);
	}


	/**
	 * Enable robotLoader.
	 * @return Configurator
	 */
	public function enableLoader()
	{
		$this->robotLoader = $this->createRobotLoader();
		$this->robotLoader->ignoreDirs .= ', tests, test, resources';
		$this->robotLoader
			->addDirectory($this->parameters['appDir'])
			->register();
		return $this;
	}


	/**
	 * @return Compiler
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


	/**
	 * Sets path to temporary directory.
	 * @return Configurator
	 */
	public function setTempDirectory($path)
	{
		$this->parameters['tempDir'] = $path;
		if (($cacheDir = $this->getCacheDirectory()) && !is_dir($cacheDir)) {
			@mkdir($cacheDir);
		}
		return $this;
	}
}
