<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Venne;
use Nette\Config\CompilerExtension;
use Nette\DI\ContainerBuilder;

/**
 * @author Josef Kříž
 */
class DoctrineExtension extends CompilerExtension {



	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		$container->addDefinition("doctrineCache")
				->setClass("Doctrine\Common\Cache\ArrayCache");

		$container->addDefinition("doctrineAnnotationRegistry")
				->setFactory("Doctrine\Common\Annotations\AnnotationRegistry::registerFile", array($container->parameters["libsDir"]. '/Doctrine/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'))
				->setShared(false);
		
		$container->addDefinition("doctrineAnnotationReader")
				->setClass('Doctrine\Common\Annotations\AnnotationReader', array("@doctrineAnnotationRegistry"))
				->addSetup("setDefaultAnnotationNamespace", 'Doctrine\ORM\Mapping\\')
				->addSetup("setIgnoreNotImportedAnnotations", true)
				->addSetup("setEnableParsePhpImports", false);
		
		$container->addDefinition("doctrineIndexReader")
				->setClass("Doctrine\Common\Annotations\IndexedReader", array("@doctrineAnnotationReader"));

		$container->addDefinition("doctrineCachedAnnotationReader")
				->setClass("Doctrine\Common\Annotations\CachedReader", array("@doctrineIndexReader", "@doctrineCache"));

		$container->addDefinition("doctrineAnnotationDriver")
				->setClass("Doctrine\ORM\Mapping\Driver\AnnotationDriver", array("@doctrineCachedAnnotationReader", array($container->parameters["appDir"], $container->parameters["venneDir"])));

		$container->addDefinition("entityManagerConfig")
				->setClass("Doctrine\ORM\Configuration")
				->addSetup('setMetadataCacheImpl', "@doctrineCache")
				->addSetup("setQueryCacheImpl", "@doctrineCache")
				->addSetup("setMetadataDriverImpl", "@doctrineAnnotationDriver")
				->addSetup("setProxyDir", $container->parameters["appDir"] . '/proxies')
				->addSetup("setProxyNamespace", 'App\Proxies')
				->addSetup("setAutoGenerateProxyClasses", true);
		
		$container->addDefinition("doctrinePanel")
				->setClass("Venne\Doctrine\Diagnostics\Panel")
				->setFactory("Venne\Doctrine\Diagnostics\Panel::register")
				->setShared(false)
				->setAutowired(false);
		
		if($container->parameters["debugger"]["mode"] == "development"){
			$container->getDefinition("entityManagerConfig")
					->addSetup("setSQLLogger", "@doctrinePanel");
		}

		$container->addDefinition('eventManager')
				->setClass("\Doctrine\Common\EventManager");

		$container->addDefinition('entityManager')
				->setClass("Doctrine\ORM\EntityManager")
				->setFactory("\Doctrine\ORM\EntityManager::create", array(
					"%database%",
					"@entityManagerConfig",
					"@eventManager"
				));
		
	
		$container->addDefinition("doctrineConnection")
				->setClass("Doctrine\DBAL\Connection")
				->setFactory("Doctrine\DBAL\DriverManager::getConnection", array("%database%"));
		
		$container->addDefinition("schemaManager")
				->setClass("Doctrine\DBAL\Schema\AbstractSchemaManager")
				->setFactory("@doctrineConnection::getSchemaManager");
		
		$container->addDefinition('checkConnection')
				->setFactory("Venne\Config\DoctrineExtension::checkConnection")
				->setShared(false);
		
		
		
		$container->addDefinition("doctrineContainer")
				->setClass("Venne\Doctrine\Container", array("@container"))
				->setAutowired(false);
		
	}
	
	
	public static function checkConnectionErrorHandler()
	{
		
	}
	
	public static function checkConnection(\Doctrine\ORM\EntityManager $entityManager)
	{
		$connection = $entityManager->getConnection();
		if (!$connection->isConnected()) {
			$old = set_error_handler("Venne\Config\DoctrineExtension::checkConnectionErrorHandler");
			try {
				$connection->connect();
			} catch (\PDOException $ex) {
				set_error_handler($old);
				return false;
			}
			set_error_handler($old);
		}
		return true;
	}



}

