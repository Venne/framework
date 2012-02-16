<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
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
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NetteExtension extends \Nette\Config\Extensions\NetteExtension
{


	public function loadConfiguration()
	{
		parent::loadConfiguration();
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);


		$container->getDefinition($this->prefix('presenterFactory'))
			->setClass('Venne\Application\PresenterFactory', array("@container"));

		$container->getDefinition('router')
			->setFactory("Venne\Application\Routers\CmsRouter", array("@container"));

		$container->getDefinition('session')
			->addSetup("setSavePath", '%tempDir%/sessions');

		$container->getDefinition($this->prefix('userStorage'))
			->setClass('Venne\Security\UserStorage')
			->setArguments(array("@session", "@core.loginRepository"));
	}


}

