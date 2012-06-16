<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module;

use Nette\DI\Container;
use Nette\Security\Permission;
use Nette\Application\Routers\RouteList;
use Nette\Config\Compiler;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface IModule
{


	public function getName();



	public function getVersion();



	public function getDescription();



	public function getDependencies();



	public function getPath();



	public function getNamespace();



	public function compile(Compiler $compiler);



	public function install(Container $container);



	public function uninstall(Container $container);
}

