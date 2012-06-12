<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config\Extensions;

use Venne;
use Nette\DI\ContainerBuilder;
use Nette\Config\CompilerExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class VenneExtension extends CompilerExtension
{


	public $defaults = array(
		'stopwatch' => array(
			'debugger' => TRUE,
		),
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig();


		$container->getDefinition('nette.presenterFactory')
			->setClass('Venne\Application\PresenterFactory', array(
			isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
		));

		$container->getDefinition('user')
			->setClass('Venne\Security\User');


		// session
		$container->getDefinition('session')
			->addSetup("setSavePath", '%tempDir%/sessions');


		$container->addDefinition("authenticator")
			->setClass("Venne\Security\Authenticator", array("%administration.login.name%", "%administration.login.password%"));


		// template
		$container->addDefinition($this->prefix("templateConfigurator"))
			->setClass("Venne\Templating\TemplateConfigurator");


		// helpers
		$container->addDefinition($this->prefix("helpers"))
			->setClass("Venne\Templating\Helpers");


		// modules
		foreach ($container->parameters["modules"] as $module => $item) {
			$container->addDefinition($module . "Module")
				->addTag("module")
				->setClass(ucfirst($module) . "Module\\Module");
		}


		// widgets
		$container->addDefinition($this->prefix('widgetManager'))
			->setClass('Venne\Widget\WidgetManager');


		// console
		$container->addDefinition($this->prefix('console'))
			->setClass('Symfony\Component\Console\Application', array('Command Line Interface'))
			->addSetup('setCatchExceptions', false);


		// CLI
		$cliRoute = $container->addDefinition($this->prefix("CliRoute"))
			->setClass("Venne\Application\Routers\CliRouter")
			->setAutowired(false);

		$container->getDefinition('router')
			->addSetup('offsetSet', array(NULL, $cliRoute));
	}


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		$this->prepareComponents();

		$this->registerMacroFactories();
		$this->registerHelperFactories();
		$this->registerRoutes();
		$this->registerCommands();
		$this->registerWidgets();
	}


	public function afterCompile(\Nette\Utils\PhpGenerator\ClassType $class)
	{
		parent::afterCompile($class);

		$initialize = $class->methods['initialize'];

		foreach ($this->getSortedServices($this->getContainerBuilder(), "subscriber") as $item) {
			$initialize->addBody('$this->getService("eventManager")->addEventSubscriber($this->getService(?));', array($item));
		}
	}


	protected function registerRoutes()
	{
		$container = $this->getContainerBuilder();
		$router = $container->getDefinition('router');

		foreach ($this->getSortedServices($container, "route") as $route) {
			$router->addSetup('$service[] = $this->getService(?)', array($route));
		}
	}


	protected function registerCommands()
	{
		$container = $this->getContainerBuilder();
		$console = $container->getDefinition($this->prefix('console'));

		foreach ($this->getSortedServices($container, "command") as $item) {
			$console->addSetup("add", "@{$item}");
		}
	}


	protected function registerMacroFactories()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('templateConfigurator'));

		foreach ($container->findByTag('macro') as $factory => $meta) {
			$definition = $container->getDefinition($factory);
			$config->addSetup('addFactory', array(substr($factory, 0, -7)));
		}
	}


	protected function registerHelperFactories()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('helpers'));

		foreach ($container->findByTag('helper') as $factory => $meta) {
			$config->addSetup('addHelper', array(substr($factory, strrpos($factory, ".") + 1, -6), $factory));
		}
	}


	protected function registerWidgets()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('widgetManager'));

		foreach ($container->findByTag('widget') as $factory => $meta) {
			$definition = $container->getDefinition($factory);

			if (!is_string($meta)) {
				throw new \Nette\InvalidArgumentException("Tag widget require name. Provide it in configuration. (tags: [widget: name])");
			}

			$config->addSetup('addWidget', array($meta, "@{$factory}"));
		}
	}


	protected function prepareComponents()
	{
		$container = $this->getContainerBuilder();

		foreach ($container->findByTag("component") as $name => $item) {
			$definition = $container->getDefinition($name);
			$definition
				->setShared(false)
				->setAutowired(false);
		}
	}


	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param $tag
	 * @return array
	 */
	protected function getSortedServices(ContainerBuilder $container, $tag)
	{
		$items = array();
		$ret = array();
		foreach ($container->findByTag($tag) as $route => $meta) {
			$priority = isset($meta['priority']) ? $meta['priority'] : (int)$meta;
			$items[$priority][] = $route;
		}

		krsort($items);

		foreach ($items as $items2) {
			foreach ($items2 as $item) {
				$ret[] = $item;
			}
		}
		return $ret;
	}


}

