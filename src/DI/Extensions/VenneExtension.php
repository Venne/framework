<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\DI\Extensions;

use Venne\DI\CompilerExtension;
use Venne\Utils\File;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class VenneExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = array(
		'session' => array(),
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		// Application
		$container->getDefinition('nette.presenterFactory')
			->setClass('Venne\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $this->containerBuilder->expand($container->parameters['appDir']) : NULL
			));

		$container->addDefinition($this->prefix('controlVerifier'))
			->setClass('Venne\Security\ControlVerifiers\ControlVerifier');

		$container->addDefinition($this->prefix('controlVerifierReader'))
			->setClass('Venne\Security\ControlVerifierReaders\AnnotationReader');

		$container->getDefinition('user')
			->setClass('Venne\Security\User');

		// http
		$container->getDefinition('httpResponse')
			->addSetup('setHeader', array('X-Powered-By', 'Nette Framework && Venne:Framework'));

		// session
		$session = $container->getDefinition('session');
		foreach ($config['session'] as $key => $val) {
			if ($val) {
				$session->addSetup('set' . ucfirst($key), $val);
			}
		}

		// template
		$container->getDefinition('nette.latte')
			->addSetup('$service->compiler->addMacro(\'cache\', new Venne\Latte\Macros\GlobalCacheMacro(?->compiler))', array('@self'));

		$container->addDefinition($this->prefix('templateConfigurator'))
			->setClass("Venne\Templating\TemplateConfigurator", array('@container', '@nette.latte'));

		// helpers
		$container->addDefinition($this->prefix('helpers'))
			->setClass("Venne\Templating\Helpers");

		// packages
		$container->addDefinition($this->prefix('templateManager'))
			->setClass('Venne\Packages\TemplateManager', array($container->expand('%packages%')));

		// widgets
		$container->addDefinition($this->prefix('widgetManager'))
			->setClass('Venne\Widget\WidgetManager');
	}


	public function beforeCompile()
	{
		$this->prepareComponents();

		$this->registerMacroFactories();
		$this->registerHelperFactories();
		$this->registerRoutes();
		$this->registerWidgets();
		$this->registerPresenters();
	}


	public function afterCompile(\Nette\PhpGenerator\ClassType $class)
	{
		parent::afterCompile($class);

		$initialize = $class->methods['initialize'];

		foreach ($this->getSortedServices('subscriber') as $item) {
			$initialize->addBody('$this->getService("eventManager")->addEventSubscriber($this->getService(?));', array($item));
		}

		$initialize->addBody('$this->parameters[\'baseUrl\'] = rtrim($this->getService("httpRequest")->getUrl()->getBaseUrl(), "/");');
		$initialize->addBody('$this->parameters[\'basePath\'] = preg_replace("#https?://[^/]+#A", "", $this->parameters["baseUrl"]);');
	}


	protected function registerRoutes()
	{
		$container = $this->getContainerBuilder();
		$router = $container->getDefinition('router');

		foreach ($this->getSortedServices('route') as $route) {
			$definition = $container->getDefinition($route);
			$definition->setAutowired(FALSE);

			$router->addSetup('$service[] = $this->getService(?)', array($route));
		}
	}


	protected function registerMacroFactories()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('templateConfigurator'));

		foreach ($container->findByTag('macro') as $factory => $meta) {
			$config->addSetup('addFactory', array($factory));
		}
	}


	protected function registerHelperFactories()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('helpers'));

		foreach ($container->findByTag('helper') as $factory => $meta) {
			$config->addSetup('addHelper', array($meta, "@{$factory}"));
		}
	}


	protected function registerWidgets()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('widgetManager'));

		foreach ($container->findByTag('widget') as $factory => $meta) {
			if (!is_string($meta)) {
				throw new \Nette\InvalidArgumentException("Tag widget require name. Provide it in configuration. (tags: [widget: name])");
			}
			$config->addSetup('addWidget', array($meta, $factory));
		}
	}


	protected function registerPresenters()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition('nette.presenterFactory');

		foreach ($container->findByTag('presenter') as $factory => $meta) {
			$service = $container->getDefinition(substr($factory, -7) === 'Factory' ? substr($factory, 0, -7) : $factory);
			$service->setAutowired(FALSE);
			$config->addSetup('addPresenter', array($service->class, $factory));
		}
	}


	protected function prepareComponents()
	{
		$container = $this->getContainerBuilder();

		foreach ($container->findByTag('component') as $name => $item) {
			$definition = $container->getDefinition($name);
			$definition->setAutowired(FALSE);
		}
	}
}

