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
use Nette\Config\Compiler;
use Nette\Config\Configurator;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class Module extends Object implements IModule
{


	/** @var string */
	protected $name;

	/** @var string */
	protected $version = "1";

	/** @var string */
	protected $description = "";

	/** @var array */
	protected $dependencies = array();



	public function getName()
	{
		if ($this->name !== NULL) {
			return $this->name;
		}

		return lcfirst(substr($this->getReflection()->getNamespaceName(), 0, -6));
	}



	public function getVersion()
	{
		return $this->version;
	}



	public function getDescription()
	{
		return $this->description;
	}



	public function getDependencies()
	{
		return $this->dependencies;
	}



	public function getPath()
	{
		return dirname($this->getReflection()->getFileName());
	}



	public function getNamespace()
	{
		return $this->getReflection()->getNamespaceName();
	}



	public function compile(Compiler $compiler)
	{
		
	}



	public function install(Container $container)
	{
		
	}



	public function uninstall(Container $container)
	{
		
	}

}

