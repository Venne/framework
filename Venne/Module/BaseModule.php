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
abstract class BaseModule extends Object implements IModule
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

		$path = $this->getPath();
		$pos = strrpos($path, "/");
		return lcfirst(substr($path, $pos + 1, -6));
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
		$compiler->addExtension($this->getName(), new CompilerExtension($this->getPath(), $this->getNamespace()));
	}



	public function getForm(Container $container)
	{
		return new \App\CoreModule\Forms\ModuleForm($container->configFormMapper, $this->getName());
	}



	public function configure(Container $container)
	{

	}



	public function install(Container $container)
	{
		$em = $container->entityManager;
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);


		/* Create db schema  */
		$classes = array();
		$entities = $container->core->scannerService->searchClassesBySubclass("\Nette\Object", "App\\" . ucfirst($this->getName()) . "Module\\");
		foreach ($entities as $entity) {
			$ref = \Nette\Reflection\ClassType::from($entity);
			if ($ref->hasAnnotation("Entity")) {
				$classes[] = $em->getClassMetadata($entity);
			}
		}
		$tool->createSchema($classes);
	}



	public function uninstall(Container $container)
	{
		$em = $container->entityManager;
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);


		/* Drop db schema */
		$classes = array();
		$entities = $container->core->scannerService->searchClassesBySubclass("\Venne\Doctrine\ORM\BaseEntity", "App\\" . ucfirst($this->getName()) . "Module\\");
		foreach ($entities as $entity) {
			$ref = \Nette\Reflection\ClassType::from($entity);
			if ($ref->hasAnnotation("Entity")) {
				$classes[] = $em->getClassMetadata($entity);
			}
		}
		$tool->dropSchema($classes);
	}

}

