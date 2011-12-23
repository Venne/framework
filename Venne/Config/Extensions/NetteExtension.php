<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Venne;
use Nette\DI\ContainerBuilder;
use Nette\DI\Container;
use Nette\Application\Routers\Route;
use Venne\Application\Routers\PageRoute;

/**
 * @author Josef Kříž
 */
class NetteExtension extends \Nette\Config\Extensions\NetteExtension {



	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		parent::loadConfiguration($container, $config);

		$container->getDefinition('presenterFactory')
				->setClass('Venne\Application\PresenterFactory', array("@container"));

		$container->getDefinition('router')
				->setFactory("Venne\Config\NetteExtension::createServiceRouter", array("@container"));
		
		$container->getDefinition('session')
				->addSetup("setSavePath", '%tempDir%/sessions');
	}



	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createServiceRouter(Container $container)
	{
		$router = new \Nette\Application\Routers\RouteList;


		/* Detect prefix */
		$prefix = $container->parameters["website"]["routePrefix"];
		if ($container->parameters["website"]["multilang"]) {
			$langs = array();
			foreach ($container->languageRepository->findAll() as $entity) {
				$langs[] = $entity->alias;
			}
			$prefix = str_replace("<lang>/", "[<lang " . implode("|", $langs) . ">/]", $prefix);
		}


		/* Administration */
		$router[] = $adminRouter = new \Venne\Application\Routers\RouteList("admin");
		$adminRouter[] = new Route('admin/<module>/<presenter>[/<action>[/<id>]]', array(
					'module' => "Core",
					'presenter' => 'Default',
					'action' => 'default',
				));


		/* Installation	 */
		if (!file_exists($container->parameters["flagsDir"] . "/installed")) {
			$router[] = new Route('[<url .+>]', "Installation:Admin:Default:", Route::ONE_WAY);
		}


		/* Upgrade */
		if ($container->moduleManager->checkModuleUpgrades()) {
			$router[] = new Route('[<url .+>]', "Modules:Admin:Default:", Route::ONE_WAY);
		}


		/* CMS Route */
		$router[] = new PageRoute(
						$container->cmsManager,
						$container->pageRepository,
						$container->languageRepository,
						$container->cacheStorage,
						$prefix,
						$container->parameters["website"]["multilang"],
						$container->parameters["website"]["defaultLangAlias"]
		);
		
		
		/* Modules */
		foreach($container->findByTag("module") as $module=>$item){
			$container->{$module}->setRoutes($router, $prefix);
		}


		/* Default route */
		if ($prefix) {
			$router[] = new Route($prefix . '<presenter>/<action>', array(
						"presenter" => $container->parameters["website"]["defaultPresenter"],
						"action" => "default",
						"lang" => NULL
					));
		}
		$router[] = new Route('', array(
					"presenter" => $container->parameters["website"]["defaultPresenter"],
					"lang" => NULL
						), Route::ONE_WAY);

		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}

}

