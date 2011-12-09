<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use \Venne\Module\Service\IRouteService;

/**
 * @author Josef Kříž
 */
class Module extends \Venne\Module\AutoModule {



	public function getName()
	{
		return "core";
	}



	public function getDescription()
	{
		return "Core module for Venne:CMS";
	}



	public function getVersion()
	{
		return "2.0";
	}



	public function setRoutes(\Nette\Application\Routers\RouteList $router, $prefix = "")
	{
		$router[] = new \Nette\Application\Routers\Route($prefix . "sitemap-<sitemap>.xml", $values + array(
					'sitemap' => NULL,
					'module' => 'Core',
					'presenter' => 'Sitemap',
					'action' => 'sitemap'
						)
		);

		$router[] = new \Nette\Application\Routers\Route($prefix . "robots.txt", $values + array(
					'module' => 'Core',
					'presenter' => 'Sitemap',
					'action' => 'robots'
						)
		);
	}



	public function configure(\Venne\DI\Container $container, \App\CoreModule\CmsManager $manager)
	{
		parent::configure($container, $manager);

		$manager->addEventListener(array(\App\CoreModule\Events::onAdminMenu), $this);
		$manager->addEventSubscriber(new \Venne\Doctrine\Mapping\DiscriminatorMapDiscoveryListener(new \Doctrine\Common\Annotations\AnnotationReader));
		
		$manager->addService("scanner", function() use ($container) {
					return new ScannerService($container);
				});
		$manager->addManager("config", function() use ($container) {
					$class = new \Venne\Config\ConfigBuilder($container->params["appDir"] . "/config.neon");
					$class->load();
					return $class;
				});
		$manager->addManager("module", function() use ($container) {
					return new ModuleManager($container, $container->configManager);
				});

		$manager->addService("user", function() use ($container) {
					return new UserService($container, "user", $container->doctrineContainer->entityManager);
				});
		$manager->addRepository("user", function() use ($container) {
					return $container->doctrineContainer->entityManager->getRepository("\\App\\CoreModule\\UserEntity");
				});
		$manager->addService("role", function() use ($container) {
					return new RoleService($container, "role", $container->doctrineContainer->entityManager);
				});
		$manager->addRepository("role", function() use ($container) {
					return $container->doctrineContainer->entityManager->getRepository("\\App\\CoreModule\\RoleEntity");
				});
		$manager->addService("permission", function() use ($container) {
					return new RoleService($container, "permission", $container->doctrineContainer->entityManager);
				});
		$manager->addRepository("permission", function() use ($container) {
					return $container->doctrineContainer->entityManager->getRepository("\\App\\CoreModule\\PermissionEntity");
				});
		$manager->addRepository("page", function() use ($container) {
					return $container->doctrineContainer->entityManager->getRepository("\\App\\CoreModule\\PageEntity");
				});
		$manager->addRepository("language", function() use ($container) {
					return $container->doctrineContainer->entityManager->getRepository("\\App\\CoreModule\\LanguageEntity");
				});
		$manager->addService("layout", function() use ($container) {
					return new LayoutService($container, "layout", $container->doctrineContainer->entityManager);
				});

		$container->addService("websiteForm", function() use ($container) {
					$form = $container->getAutoWireClassInstance("App\WebsiteModule\WebsiteForm");
					$form->setSuccessLink("default");
					$form->setFlashMessage("Website has been saved");
					$form->setSubmitLabel("Save");
					return $form;
				});
				
		$manager->addElement("panel", function(){
			return new \Venne\CoreModule\PanelElement();
		});
		$manager->addElement("extensions", function(){
			return new ContentExtensionsElement;
		});

		
	}



	public function setPermissions(\Venne\DI\Container $container, \Nette\Security\Permission $permissions)
	{
		$permissions->addResource("CoreModule");
		$permissions->addResource("CoreModule\\Panel", "CoreModule");
		$permissions->addResource("CoreModule\\AdminModule", "CoreModule");
		$permissions->addResource("CoreModule\\AdminModule\\DefaultPresenter", "CoreModule\\AdminModule");
		$permissions->addResource("CoreModule\\AdminModule\\AboutPresenter", "CoreModule\\AdminModule");


		$permissions->addResource("WebsiteModule", "CoreModule");
		$permissions->addResource("WebsiteModule\\AdminModule", "WebsiteModule");
		$permissions->addResource("WebsiteModule\\AdminModule\\DefaultPresenter", "WebsiteModule\\AdminModule");
		$permissions->addResource("WebsiteModule\\AdminModule\\LanguagePresenter", "WebsiteModule\\AdminModule");


		$permissions->addResource("ModulesModule", "CoreModule");
		$permissions->addResource("ModulesModule\\AdminModule", "ModulesModule");
		$permissions->addResource("ModulesModule\\AdminModule\\DefaultPresenter", "ModulesModule\\AdminModule");


		$permissions->addResource("SecurityModule", "CoreModule");
		$permissions->addResource("SecurityModule\\AdminModule", "SecurityModule");
		$permissions->addResource("SecurityModule\\AdminModule\\DefaultPresenter", "SecurityModule\\AdminModule");
		$permissions->addResource("SecurityModule\\AdminModule\\PermissionsPresenter", "SecurityModule\\AdminModule");
		$permissions->addResource("SecurityModule\\AdminModule\\UsersPresenter", "SecurityModule\\AdminModule");
		$permissions->addResource("SecurityModule\\AdminModule\\RolesPresenter", "SecurityModule\\AdminModule");


		$permissions->addResource("SystemModule");
		$permissions->addResource("SystemModule\\AdminModule", "SystemModule");
		$permissions->addResource("SystemModule\\AdminModule\\DefaultPresenter", "SystemModule\\AdminModule");
		$permissions->addResource("SystemModule\\AdminModule\\DebuggerPresenter", "SystemModule\\AdminModule");
		$permissions->addResource("SystemModule\\AdminModule\\AccountPresenter", "SystemModule\\AdminModule");
		$permissions->addResource("SystemModule\\AdminModule\\DatabasePresenter", "SystemModule\\AdminModule");

		$permissions->addResource("ContentModule", "CoreModule");
		$permissions->addResource("ContentModule\\AdminModule", "ContentModule");
		$permissions->addResource("ContentModule\\AdminModule\\DefaultPresenter", "ContentModule\\AdminModule");
	}



	public function onAdminMenu($menu)
	{
		$nav = new \App\CoreModule\NavigationEntity("Content");
		$nav->setLink(":Content:Admin:Default:");
		$nav->setMask(":Content:Admin:*:*");
		$menu->addNavigation($nav);
	}



	public function install(\Venne\DI\Container $container)
	{
		parent::install($container);

		/*
		 * Install default roles
		 */
		$repository = $container->roleRepository;
		foreach (array("admin" => NULL, "guest" => NULL, "registered" => "guest") as $name => $parent) {
			$role = $repository->createNew();
			$role->name = $name;
			if ($parent) {
				$role->parent = $repository->findOneBy(array("name" => $parent));
			}
			$repository->save($role);
		}
	}

}
