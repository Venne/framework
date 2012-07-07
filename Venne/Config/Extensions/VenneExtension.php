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
use Venne\Config\CompilerExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class VenneExtension extends CompilerExtension
{


	public $defaults = array(
		'stopwatch' => array(
			'debugger' => TRUE,
		),
		'moduleManager' => array(
			'resourcesMode' => 'symlink'
		)
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);


		$container->getDefinition('nette.presenterFactory')
			->setClass('Venne\Application\PresenterFactory', array(
			isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
		));

		$container->getDefinition('user')
			->setClass('Venne\Security\User');


		// session
		$container->getDefinition('session')
			->addSetup("setSavePath", '%tempDir%/sessions');

		// template
		$container->addDefinition($this->prefix("templateConfigurator"))
			->setClass("Venne\Templating\TemplateConfigurator");


		// helpers
		$container->addDefinition($this->prefix("helpers"))
			->setClass("Venne\Templating\Helpers");


		// modules
		$container->addDefinition($this->prefix('moduleManager'))
			->setClass('Venne\Module\ModuleManager', array('@container', '%modules%', '%configDir%/modules.neon', $config['moduleManager']['resourcesMode'], '%resourcesDir%'));
		$container->addDefinition($this->prefix('composerManager'))
			->setClass('Venne\Module\Composer\ComposerManager', array($container->parameters['appDir'] . '/..'));


		foreach ($container->parameters["modules"] as $module => $item) {
			if ($item['status'] == \Venne\Module\ModuleManager::MODULE_STATUS_INSTALLED) {
				$container->addDefinition($module . "Module")
					->addTag("module")
					->setClass(ucfirst($module) . "Module\\Module");
			}
		}


		// widgets
		$container->addDefinition($this->prefix('widgetManager'))
			->setClass('Venne\Widget\WidgetManager');


		// CLI
		$cliRoute = $container->addDefinition($this->prefix("CliRoute"))
			->setClass("Venne\Application\Routers\CliRouter")
			->setAutowired(false);

		$container->getDefinition('router')
			->addSetup('offsetSet', array(NULL, $cliRoute));

		// Commands
		$commands = array(
			'composerInstall' => 'Venne\Module\Composer\Commands\Install',
			'composerUpdate' => 'Venne\Module\Composer\Commands\Update',
			'composerAdd' => 'Venne\Module\Composer\Commands\AddRequire',
			'composerRemove' => 'Venne\Module\Composer\Commands\RemoveRequire',
			'composerSearch' => 'Venne\Module\Composer\Commands\Search',
			'install' => 'Venne\Module\Commands\Install',
			'uninstall' => 'Venne\Module\Commands\Uninstall',
			'cache' => 'Venne\Caching\Commands\Cache',
		);
		foreach ($commands as $name => $cmd) {
			$container->addDefinition($this->prefix(lcfirst($name) . 'Command'))
				->setClass("{$cmd}Command")
				->addTag('command');
		}

	}


	public function beforeCompile()
	{
		$this->prepareComponents();

		$this->registerMacroFactories();
		$this->registerHelperFactories();
		$this->registerRoutes();
		$this->registerWidgets();
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
			$router->addSetup('$service[] = $this->getService(?)', array($route));
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
			$config->addSetup('addHelper', array($meta, "@{$factory}"));
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

}

