<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module;

use Nette\DI\ContainerBuilder;
use Nette\DI\Container;
use Nette\Security\Permission;
use Nette\Application\Routers\RouteList;

/**
 * @author Josef Kříž
 */
interface IModule {



	public function getName();



	public function getVersion();



	public function getDescription();



	public function getDependencies();



	public function setRoutes(RouteList $router, $prefix = "");



	public function loadConfiguration(ContainerBuilder $container, array $config);



	public function setPermissions(Container $container, Permission $permissions);



	public function install(Container $container);



	public function uninstall(Container $container);



	public function getForm(Container $container);
}

