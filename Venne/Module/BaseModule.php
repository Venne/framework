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
		$compiler->addExtension($this->getName(), new CompilerExtension($this->getPath(), $this->getNamespace()));
	}



	public function getForm(Container $container)
	{
		return new \CoreModule\Forms\ModuleForm($container->configFormMapper, $this->getName());
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
		$entities = $container->core->scannerService->searchClassesBySubclass("\Nette\Object", ucfirst($this->getName()) . "Module\\");
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
		$connection = $em->getConnection();
		$dbPlatform = $connection->getDatabasePlatform();
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);


		// load
		$classes = array();
		$entities = $container->core->scannerService->searchClassesBySubclass("\Venne\Doctrine\ORM\BaseEntity", ucfirst($this->getName()) . "Module\\");
		foreach ($entities as $entity) {
			$ref = \Nette\Reflection\ClassType::from($entity);
			if ($ref->hasAnnotation("Entity")) {
				$classes[] = $em->getClassMetadata($entity);
			}
		}
		
		// delete entities
		$connection->beginTransaction();
		try {
			foreach($classes as $class){
				$repository = $em->getRepository($class->getName());
				foreach($repository->findAll() as $entity){
					$em->remove($entity);
				}
			}
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
		}
		
		/*
		$connection->beginTransaction();
		try {
			$connection->query('SET FOREIGN_KEY_CHECKS=0');
			foreach($classes as $class){
				$q = $dbPlatform->getTruncateTableSql($class->getTableName());
				$connection->executeUpdate($q);
			}
			$connection->query('SET FOREIGN_KEY_CHECKS=1');
			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
		}*/
				
		// drop schema
		$tool->dropSchema($classes);
	}

}

