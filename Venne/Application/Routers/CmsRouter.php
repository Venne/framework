<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\Routers;

use Nette\Application\Routers\Route;
use Nette;

/**
 * @author	   Josef Kříž
 */
class CmsRouter extends \Nette\Application\Routers\RouteList
{


	function __construct(\SystemContainer $container)
	{
		parent::__construct();


		/* Detect prefix */
		$prefix = $container->parameters["website"]["routePrefix"];
		$adminPrefix = $container->parameters["administration"]["routePrefix"];
		$languages = $container->parameters["website"]["languages"];
		$prefix = str_replace("<lang>/", "<lang " . implode("|", $languages) . ">/", $prefix);


		/* parameters */
		$parameters = array();
		$parameters["lang"] = count($languages) > 1 || $container->parameters["website"]["routePrefix"] ? NULL : $container->parameters["website"]["defaultLanguage"];


		/* args for event */
		$args = new EventArgs;
		$args->setRouteList($this);
		$args->setPrefix($prefix);


		/* Startup event */
		$container->eventManager->dispatchEvent(RouterEvents::onStartOfRouters, $args);


		/* Administration */
		if ($adminPrefix) {
			$this[] = $adminRouter = new \Venne\Application\Routers\RouteList("admin");
			$adminRouter[] = new Route($adminPrefix . '/<module>/<presenter>[/<action>[/<id>]]', array('module' => "Core", 'presenter' => 'Default', 'action' => 'default',));
		} else {
			$this[] = $adminRouter = new \Venne\Application\Routers\RouteList("admin");
			$adminRouter[] = new Route('<module>/<presenter>[/<action>[/<id>]]', array('module' => "Core", 'presenter' => 'Default', 'action' => 'default',));
		}


		if (!file_exists($container->parameters["flagsDir"] . "/installed")) {

			/* Installation	 */
			$this[] = new Route('<action>[/<id>]', array("presenter" => "Core:Installation:Default", "action" => "default",));

		} else {

			/* Upgrade */
			if ($container->core->moduleManager->checkModuleUpgrades()) {
				$this[] = new Route('[<url .+>]', "Modules:Admin:Default:", Route::ONE_WAY);
			}

			/* CMS Route */
			$this[] = new PageRoute($container->core->cmsManager, $container->core->pageRepository, $container->core->languageRepository, $container->cacheStorage, $prefix, $parameters, $container->parameters["website"]["languages"], $container->parameters["website"]["defaultLanguage"]);

		}


		/* Module routes event */
		$container->eventManager->dispatchEvent(RouterEvents::onCmsRouters, $args);


		/* Default route */
		if ($prefix) {
			$this[] = new Route($prefix . '<presenter>/<action>', array("presenter" => $container->parameters["website"]["defaultPresenter"], "action" => "default", "lang" => NULL));
		}
		$this[] = new Route($prefix . '', array("presenter" => $container->parameters["website"]["defaultPresenter"], "lang" => NULL), Route::ONE_WAY);

		$this[] = new Route($prefix . '<presenter>/<action>[/<id>]', 'Homepage:default');


		/* on end event */
		$container->eventManager->dispatchEvent(RouterEvents::onEndOfRouters, $args);
	}

}
