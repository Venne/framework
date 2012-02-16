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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CompilerExtension extends \Nette\Config\CompilerExtension
{


	const CONTROL = "control";

	const SUBSCRIBER = "subscriber";

	const SERVICE = "service";

	const MACRO = "macro";

	const FORM = "form";

	const MANAGER = "manager";

	const REPOSITORY = "repository";

	const WIDGET = "widget";



	/**
	 * Compile macro.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileMacro($class, $name)
	{
		$this->getContainerBuilder()->addDefinition($name)->setParameters(array("compiler"))->setFactory($class . "::install", array("%compiler%"))->setShared(false)->setAutowired(false)->addTag(self::MACRO);
	}



	/**
	 * Compile manager.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileManager($class, $name)
	{
		$this->getContainerBuilder()->addDefinition($name)->setClass($class)->addTag(self::MANAGER);
	}



	/**
	 * Compile service.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileService($class, $name)
	{
		$this->getContainerBuilder()->addDefinition($name)->setClass($class)->addTag(self::SERVICE);
	}



	/**
	 * Compile subscriber.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileSubscriber($class, $name)
	{
		$this->getContainerBuilder()->addDefinition($name)->setClass($class)->setAutowired(false)->addTag(self::SUBSCRIBER);
	}



	/**
	 * Compile control.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileControl($class, $name)
	{
		$this->getContainerBuilder()->addDefinition($name)->setClass($class)->setShared(false)->setAutowired(false)->addTag(self::CONTROL);
	}



	/**
	 * Compile widget.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileWidget($class, $name)
	{
		$this->getContainerBuilder()->addDefinition($name)->setClass($class)->setShared(false)->setAutowired(false)->addTag(self::WIDGET);
	}



	/**
	 * Compile form.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileForm($class, $name)
	{
		$this->getContainerBuilder()->addDefinition($name)->setClass($class)->setAutowired(false)->setShared(false)->addTag(self::FORM);
	}



	/**
	 * Compile repository.
	 *
	 * @param string $class
	 * @param string $name
	 */
	protected function compileRepository($class, $name)
	{
		$repositoryName = lcfirst(substr($name, strrpos($name, "\\") + 1, -6) . "Repository");

		if (\Nette\Utils\Strings::startsWith($name, $this->prefix(""))) {
			$repositoryName = $this->prefix($repositoryName);
			$name = substr($name, strlen($this->prefix("")));
		}

		$this->getContainerBuilder()->addDefinition($repositoryName)->setClass($class)->setFactory("@entityManager::getRepository", array("\\" . $name))->addTag("repository")->setAutowired(false);
	}

}

