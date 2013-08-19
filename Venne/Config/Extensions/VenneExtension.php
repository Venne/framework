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

use Venne\Config\CompilerExtension;
use Venne\Utils\File;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class VenneExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = array(
		'moduleManager' => array(
			'resourcesMode' => 'symlink'
		),
		'session' => array()
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);


		// Application
		$container->getDefinition('nette.presenterFactory')
			->setClass('Venne\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));

		$container->addDefinition($this->prefix('controlVerifier'))
			->setClass('Venne\Security\ControlVerifiers\ControlVerifier');

		$container->addDefinition($this->prefix('controlVerifierReader'))
			->setClass('Venne\Security\ControlVerifierReaders\AnnotationReader');

		$container->getDefinition('user')
			->setClass('Venne\Security\User');

		// cache
		$container->addDefinition($this->prefix('cacheManager'))
			->setClass('Venne\Caching\CacheManager', array('@cacheStorage', '%tempDir%/cache', '%tempDir%/sessions'));

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
			->setClass('Venne\Latte\Engine')
			->addSetup('$m = Venne\Latte\Macros\UIMacros::install(?->compiler); $m->injectHelper(?)', array('@self', '@venne.moduleHelpers'))
			->setShared(FALSE);

		$container->addDefinition($this->prefix("templateConfigurator"))
			->setClass("Venne\Templating\TemplateConfigurator", array('@container', '@nette.latteFactory'));

		// helpers
		$container->addDefinition($this->prefix("helpers"))
			->setClass("Venne\Templating\Helpers");

		// modules
		$container->addDefinition($this->prefix('moduleManager'))
			->setClass('Venne\Module\ModuleManager', array('@container', '@venne.cacheManager', '%libsDir%', '%configDir%', '%modulesDir%'));

		$container->addDefinition($this->prefix('templateManager'))
			->setClass('Venne\Module\TemplateManager', array('%modules%'));

		// widgets
		$container->addDefinition($this->prefix('widgetManager'))
			->setClass('Venne\Widget\WidgetManager');

		// CLI
		$cliRoute = $container->addDefinition($this->prefix("CliRoute"))
			->setClass("Venne\Application\Routers\CliRouter")
			->setAutowired(FALSE);

		$container->getDefinition('router')
			->addSetup('offsetSet', array(NULL, $cliRoute));

		// Commands
		$commands = array(
			'cache' => 'Venne\Caching\Commands\Cache',
			'moduleUpdate' => 'Venne\Module\Commands\Update',
			'moduleInstall' => 'Venne\Module\Commands\Install',
			'moduleUninstall' => 'Venne\Module\Commands\Uninstall',
			'moduleUpgrade' => 'Venne\Module\Commands\Upgrade',
			'moduleRegister' => 'Venne\Module\Commands\Register',
			'moduleUnregister' => 'Venne\Module\Commands\Unregister',
			'moduleList' => 'Venne\Module\Commands\List',
			'moduleCreate' => 'Venne\Module\Commands\Create',
			'moduleDelete' => 'Venne\Module\Commands\Delete',
		);
		foreach ($commands as $name => $cmd) {
			$container->addDefinition($this->prefix(lcfirst($name) . 'Command'))
				->setClass("{$cmd}Command")
				->addTag('command');
		}

		// helpers
		$container->addDefinition($this->prefix('moduleHelpers'))
			->setClass('Venne\Module\Helpers', array('%modules%'));

		// symlink to client-side
		$clientSidePath = realpath($container->parameters['libsDir'] . '/nette/nette/client-side');
		$netteModulePath = $container->parameters['resourcesDir'] . '/netteModule';
		if (!file_exists($netteModulePath) && file_exists($clientSidePath)) {
			umask(0000);
			@mkdir(dirname($netteModulePath), 0777, TRUE);
			if (!@symlink(File::getRelativePath(dirname($netteModulePath), $clientSidePath), $netteModulePath) && !file_exists($netteModulePath)) {
				File::copy($clientSidePath, $netteModulePath);
			}
		}
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


	public function afterCompile(\Nette\Utils\PhpGenerator\ClassType $class)
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
			$config->addSetup('addFactory', array(substr($factory, 0, -7)));
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
			$class = $container->getDefinition(substr($factory, 0, -7))->class;
			$config->addSetup('addWidget', array($meta, $class, "@{$factory}"));
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

		foreach ($container->findByTag("component") as $name => $item) {
			$definition = $container->getDefinition($name);
			$definition->setAutowired(FALSE);
		}
	}
}

