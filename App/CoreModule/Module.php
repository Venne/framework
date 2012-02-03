<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Nette\Config\Compiler;
use Nette\Config\Configurator;
use Nette\DI\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Module extends \Venne\Module\BaseModule {


	/** @var string */
	protected $version = "2.0";

	/** @var string */
	protected $description = "Core module for Venne:CMS";



	public function install(\Nette\DI\Container $container)
	{
		parent::install($container);

		/* Install default roles */
		$repository = $container->core->roleRepository;
		foreach (array("admin" => NULL, "guest" => NULL, "registered" => "guest") as $name => $parent) {
			$role = $repository->createNew();
			$role->name = $name;
			if ($parent) {
				$role->parent = $repository->findOneBy(array("name" => $parent));
			}
			$repository->save($role);
		}
	}



	public function getPath()
	{
		return str_replace("libs-all/venne/App", "libs/App", dirname($this->getReflection()->getFileName()));
	}

}
