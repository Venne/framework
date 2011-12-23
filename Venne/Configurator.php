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

use Nette,
	Nette\Caching\Cache,
	Nette\DI,
	Nette\Diagnostics\Debugger,
	Nette\Application\Routers\SimpleRouter,
	Nette\Application\Routers\Route;
use Nette\Config\Compiler;
use Venne\Diagnostics\Logger;

/**
 * @author     Josef Kříž
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
		$parameters['flashes'] = array(
			'success' => "success",
			'error' => "error",
			'info' => "info",
			'warning' => "warning",
		);
		return $parameters;
	}



	/**
	 * Loads configuration from file and process it.
	 * @return DI\Container
	 */
	public function createContainer()
	{
		\Venne\Panels\Stopwatch::start();
		$container = parent::createContainer();
		\Venne\Panels\Stopwatch::stop("generate container");
		\Venne\Panels\Stopwatch::start();


		/* Detect mode */
		$url = explode("/", substr($container->httpRequest->url->path, strlen($container->httpRequest->url->basePath)), 2);
		if ($url[0] == "admin") {
			$container->parameters["venneModeAdmin"] = true;
		} else if ($url[0] == "installation") {
			$container->parameters["venneModeInstallation"] = true;
		} else {
			$container->parameters["venneModeFront"] = true;
		}


		/* Setup Debugger */
		$debugger = $container->parameters["debugger"];
		if ($debugger["mode"] == "production") {
			$this->setProductionMode(true);
		} else if ($debugger["mode"] == "development") {
			$this->setProductionMode(false);
		} else {
			$this->setProductionMode($this->detectProductionMode());
		}
		Debugger::enable(
				$debugger['developerIp'] && $this->isProductionMode() ? (array) $debugger['developerIp'] : $this->isProductionMode(), $debugger['logDir'], $debugger['logEmail']
		);
		Debugger::$strictMode = true;
		Debugger::$logger->mailer = array("\\Venne\\Diagnostics\\Logger", "venneMailer");
		\Nette\Diagnostics\Logger::$emailSnooze = $container->parameters["debugger"]["emailSnooze"];
		Debugger::$logDirectory = $container->parameters["logDir"];
		Logger::$linkPrefix = "http://" . $container->httpRequest->url->host . $container->httpRequest->url->basePath . "admin/system/log/show?name=";


		/* Setup Application */
		$application = $container->application;
		$application->catchExceptions = (bool) $this->isProductionMode();
		$application->errorPresenter = $container->parameters['website']['errorPresenter'];
		$application->onShutdown[] = function() {
					\Venne\Panels\Stopwatch::stop("shutdown");
				};


		/* Initialize modules */
		foreach ($container->findByTag("module") as $module => $par) {
			$container->{$module}->configure($container, $container->cmsManager);
		}


		/* Load theme */
		$theme = $container->parameters['website']['theme'];
		$class = "\\" . ucfirst($theme) . "Theme\\Theme";
		$container->addService($theme . "Theme", new $class($container), array(\Nette\DI\Container::TAGS => array("theme" => true)));


		/* Set timer to router */
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
		$compiler = new Compiler;
		$compiler->addExtension('php', new \Nette\Config\Extensions\PhpExtension())
				->addExtension('constants', new Nette\Config\Extensions\ConstantsExtension())
				->addExtension('nette', new Config\NetteExtension())
				->addExtension('venne', new Config\VenneExtension())
				->addExtension('doctrine', new Config\DoctrineExtension());

		foreach ($this->modules as $module => $par) {
			$class = "\\App\\" . ucfirst($module) . "Module\\Module";
			$compiler->addExtension(ucfirst($module) . "Module", new $class);
		}

		return $compiler;
	}



	/**
	 * Sets path to temporary directory.
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
