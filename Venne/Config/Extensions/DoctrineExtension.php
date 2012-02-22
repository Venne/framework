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
class DoctrineExtension extends CompilerExtension
{


	public $defaults = array('debugger' => TRUE,);



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig();


		// Cache
		$cache = $container->addDefinition($this->prefix("cache"))
			->setInternal(true);
		if(function_exists("apc_fetch")){
			$cache->setClass("Doctrine\Common\Cache\ApcCache");
		}else{
			$cache->setClass("Doctrine\Common\Cache\ArrayCache");
		}


		// Annotations
		$container->addDefinition("doctrineAnnotationRegistry")
			->setFactory("Doctrine\Common\Annotations\AnnotationRegistry::registerFile", array($container->parameters["libsDir"] . '/Doctrine/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'))
			->setShared(false)
			->setInternal(true);
		$container->addDefinition("doctrineAnnotationReader")
			->setClass('Doctrine\Common\Annotations\SimpleAnnotationReader', array("@doctrineAnnotationRegistry"))
			->addSetup("addNamespace", 'Doctrine\\ORM\\Mapping')
			->setInternal(true);
		$container->addDefinition("doctrineCachedAnnotationReader")
			->setClass("Doctrine\Common\Annotations\CachedReader", array("@doctrineAnnotationReader", "@doctrine.cache"))
			->setInternal(true);
		$container->addDefinition("doctrineAnnotationDriver")
			->setClass("Doctrine\ORM\Mapping\Driver\AnnotationDriver", array("@doctrineCachedAnnotationReader", array($container->parameters["appDir"], $container->parameters["venneDir"])))
			->setInternal(true);

		$container->addDefinition("entityManagerConfig")
			->setClass("Doctrine\ORM\Configuration")
			->addSetup('setMetadataCacheImpl', "@doctrine.cache")
			->addSetup("setQueryCacheImpl", "@doctrine.cache")
			->addSetup("setMetadataDriverImpl", "@doctrineAnnotationDriver")
			->addSetup("setProxyDir", $container->parameters["appDir"] . '/proxies')
			->addSetup("setProxyNamespace", 'App\Proxies')
			->setInternal(true);

		if ($container->parameters["debugger"]["mode"] == "development") {
			$container->getDefinition("entityManagerConfig")
				->addSetup("setAutoGenerateProxyClasses", true);
		}

		$container->addDefinition("doctrinePanel")
			->setClass("Venne\Doctrine\Diagnostics\Panel")
			->setFactory("Venne\Doctrine\Diagnostics\Panel::register")
			->setShared(false)
			->setAutowired(false);

		if ($config["debugger"] == "development") {
			$container->getDefinition("entityManagerConfig")
				->addSetup("setSQLLogger", "@doctrinePanel");
		}

		$container->addDefinition('eventManager')
			->setClass("\Doctrine\Common\EventManager");

		$container->addDefinition('entityManager')
			->setClass("Doctrine\ORM\EntityManager")
			->setFactory("\Doctrine\ORM\EntityManager::create", array("%database%", "@entityManagerConfig", "@eventManager"));

		if($container->parameters["database"]["driver"] == "pdo_mysql" && $container->parameters["database"]["charset"]){
			$container->addDefinition($this->prefix("mysqlListener"))
				->setClass("Doctrine\DBAL\Event\Listeners\MysqlSessionInit", array($container->parameters["database"]["charset"], $container->parameters["database"]["collation"]))
				->setInternal(true);

			$container->getDefinition("eventManager")
				->addSetup("addEventSubscriber", "@doctrine.mysqlListener");
		}


		$container->addDefinition("doctrineConnection")
			->setClass("Doctrine\DBAL\Connection")
			->setFactory("Doctrine\DBAL\DriverManager::getConnection", array("%database%"));

		$container->addDefinition("schemaManager")
			->setClass("Doctrine\DBAL\Schema\AbstractSchemaManager")
			->setFactory("@doctrineConnection::getSchemaManager");

		$container->addDefinition('checkConnection')
			->setFactory("Venne\Config\DoctrineExtension::checkConnection")
			->setShared(false);
	}



	public static function checkConnectionErrorHandler()
	{

	}



	public static function checkConnection(\Nette\DI\Container $context, \Doctrine\ORM\EntityManager $entityManager)
	{
		if (!$context->parameters["database"]["driver"]) {
			return false;
		}

		$connection = $entityManager->getConnection();
		if ($connection->isConnected()) {
			return true;
		}

		$old = set_error_handler("Venne\Config\DoctrineExtension::checkConnectionErrorHandler");
		try {
			$connection->connect();
			if ($connection->isConnected()) {
				set_error_handler($old);
				return true;
			}
			set_error_handler($old);
			return false;
		} catch (\PDOException $ex) {
			set_error_handler($old);
			return false;
		}
	}

}

