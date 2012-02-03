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

use Nette, Nette\Caching\Cache, Nette\DI, Nette\Diagnostics\Debugger, Nette\Application\Routers\SimpleRouter, Nette\Application\Routers\Route;
use Nette\Config\Compiler;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Configurator extends \Nette\Config\Configurator {


	/** @var array */
	protected $modules;



	public function __construct($modules = array())
	{
		parent::__construct();
		$this->modules = $modules;
	}



	protected function getDefaultParameters()
	{
		$parameters = parent::getDefaultParameters();
		$parameters["venneModeInstallation"] = false;
		$parameters["venneModeAdmin"] = false;
		$parameters["venneModeFront"] = false;
		$parameters["venneModulesNamespace"] = "\\Venne\\Modules\\";
		$parameters['flashes'] = array('success' => "success", 'error' => "error", 'info' => "info", 'warning' => "warning",);
		return $parameters;
	}



	/**
	 * Loads configuration from file and process it.
	 *
	 * @return DI\Container
	 */
	public function createContainer()
	{
		/* create container */
		\Venne\Panels\Stopwatch::start();
		$container = parent::createContainer();
		\Venne\Panels\Stopwatch::stop("generate container");
		\Venne\Panels\Stopwatch::start();


		/* Register subscribers */
		$eventManager = $container->eventManager;
		foreach ($container->findByTag("subscriber") as $module => $par) {
			$eventManager->addEventSubscriber($container->{$module});
		}


		/* parameters */
		$baseUrl = rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');
		$container->parameters['baseUrl'] = $baseUrl;
		$container->parameters['basePath'] = preg_replace('#https?://[^/]+#A', '', $baseUrl);


		/* Setup mode */
		$debugger = $container->parameters["debugger"];
		if ($debugger["mode"] == "production") {
			$this->setProductionMode(true);
		} else if ($debugger["mode"] == "development") {
			$this->setProductionMode(false);
		} else {
			$this->setProductionMode($this->detectProductionMode());
		}


		/* Setup Application */
		$application = $container->application;
		$application->catchExceptions = (bool)$this->isProductionMode();
		$application->errorPresenter = $container->parameters['website']['errorPresenter'];
		$application->onShutdown[] = function()
		{
			\Venne\Panels\Stopwatch::stop("shutdown");
		};


		/* Initialize modules */
		foreach ($container->findByTag("module") as $module => $par) {
			$container->{$module}->configure($container);
		}


		/* Detect updated flag */
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


		/* Set timer to router */
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
		$compiler = new Compiler;
		$compiler->addExtension('php', new \Nette\Config\Extensions\PhpExtension())->addExtension('constants', new Nette\Config\Extensions\ConstantsExtension())->addExtension('nette', new Config\NetteExtension())->addExtension('venne', new Config\VenneExtension())->addExtension('doctrine', new Config\DoctrineExtension())->addExtension('module', new Config\ModuleExtension())->addExtension('assets', new Config\AssetExtension());

		foreach ($this->modules as $module => $par) {
			$class = "\\App\\" . ucfirst($module) . "Module\\Module";
			$instance = new $class;
			$instance->compile($this, $compiler);
		}

		return $compiler;
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

}
