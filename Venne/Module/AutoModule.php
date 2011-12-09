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

/**
 * @author Josef Kříž
 */
abstract class AutoModule extends BaseModule {


	protected $cache;
	protected $cacheLoaded;
	protected $cacheData;



	protected function startCache($cacheStorage)
	{
		$this->cache = new \Nette\Caching\Cache($cacheStorage, "Venne.AutoModule");
		$this->cacheLoaded = true;
		$this->cacheData = $this->cache->load($this->getName());
	}



	protected function saveCache()
	{
		$this->cache->save($this->getName(), $this->cacheData);
	}



	protected function scan($container)
	{
		$resources = array();
		//$presenters = array();

		$paths = array(
			$container->params["appDir"] . "/" . ucfirst($this->getName()) . "Module",
			$container->params["libsDir"] . "/App/" . ucfirst($this->getName()) . "Module"
		);


		foreach ($paths as $path) {
			if (file_exists($path)) {
				foreach (array("" => $path . "/presenters", "\\AdminModule" => $path . "/AdminModule/presenters") as $key => $pathP) {
					if (file_exists($pathP)) {
						foreach (\Nette\Utils\Finder::findFiles("*Presenter.php")->in($pathP) as $file) {

							$className = "\\App\\" . ucfirst($this->getName()) . "Module{$key}\\" . substr($file->getBaseName(), 0, -4);

							//$presenters[] = $className;

							$ref = new \Nette\Reflection\ClassType($className);
							if ($ref->hasAnnotation("secured")) {
								$secured = $ref->getAnnotation("resource");

								if (isset($secured["resource"])) {
									$resource = $secured["resource"];
								} else {
									$resource = $ref->getName();
								}
								$resource = substr($resource, 0, 4) == "App\\" ? substr($resource, 4) : $resource;

								$resources[$resource] = $this->getParentResource($resource);


								foreach ($ref->getMethods() as $method) {
									if ($method->hasAnnotation("resource")) {
										$methodResource = $method->getAnnotation("resource");
									} else {
										$methodResource = $resource;
									}

									$methodResource .= $method->hasAnnotation("privilege") ? "\\" . $method->getAnnotation("privilege") : "";
									$methodResource = substr($methodResource, 0, 4) == "App\\" ? substr($methodResource, 4) : $methodResource;

									if ($methodResource != $resource) {
										$resources[$methodResource] = $this->getParentResource($methodResource);
									}
								}
							}
						}
					}
				}
			}
		}
		$this->cacheData["resources"] = $resources;
		//$this->cacheData["presenters"] = $presenters;
		$this->saveCache();
	}



	public function setServices(\Venne\DI\Container $container)
	{
		parent::setServices($container);

		if (!$this->cacheLoaded) {
			$this->startCache($container->cacheStorage);
		}

//		if (!isset($this->cacheData["presenters"]) || $this->cacheData["presenters"] === NULL) {
//			$this->scan($container);
//		}
//		foreach ($this->cacheData["presenters"] as $presenter) {
//			$container->addServiceAutoWire(substr($presenter, 1), $presenter);
//		}
	}



	public function setPermissions(\Venne\DI\Container $container, \Nette\Security\Permission $permissions)
	{
		parent::setPermissions($container, $permissions);

		if (!$this->cacheLoaded) {
			$this->startCache($container->cacheStorage);
		}

		if (!isset($this->cacheData["resources"]) || $this->cacheData["resources"] === NULL) {
			$this->scan($container);
		}

		foreach ($this->cacheData["resources"] as $resource => $parent) {
			$this->addResource($permissions, $resource, $parent);
		}
	}



	protected function addResource($permissions, $resource, $parent)
	{
		if (!$parent) {
			$parent = NULL;
		} else {
			if (!$permissions->hasResource($parent)) {
				$this->addResource($permissions, $parent, $this->getParentResource($parent));
			}
		}

		$permissions->addResource($resource, $parent);
	}



	protected function getParentResource($resource)
	{
		$parent = explode("\\", $resource);
		unset($parent[count($parent) - 1]);
		return join("\\", $parent);
	}



	public function install(\Venne\DI\Container $container)
	{
		parent::install($container);
		$em = $container->doctrineContainer->entityManager;
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);

		/*
		 * Create db schema 
		 */
		$classes = array();
		$entities = $container->scannerService->searchClassesBySubclass("\Nette\Object", "App\\" . ucfirst($this->getName()) . "Module\\");
		foreach ($entities as $entity) {
			$ref = \Nette\Reflection\ClassType::from($entity);
			if ($ref->hasAnnotation("Entity")) {
				$classes[] = $em->getClassMetadata($entity);
			}
		}
		$tool->createSchema($classes);
	}



	public function uninstall(\Venne\DI\Container $container)
	{
		parent::uninstall($container);
		$em = $container->doctrineContainer->entityManager;
		$tool = new \Doctrine\ORM\Tools\SchemaTool($em);

		/*
		 * Drop db schema
		 */
		$classes = array();
		$entities = $container->scannerService->searchClassesBySubclass("\Venne\Doctrine\ORM\BaseEntity", "App\\" . ucfirst($this->getName()) . "Module\\");
		foreach ($entities as $entity) {
			$ref = \Nette\Reflection\ClassType::from($entity);
			if ($ref->hasAnnotation("Entity")) {
				$classes[] = $em->getClassMetadata($entity);
			}
		}
		$tool->dropSchema($classes);

		/*
		 * @ToDo Delete permission in db
		 */
	}

}

