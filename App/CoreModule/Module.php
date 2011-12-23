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

use Venne\Module\Service\IRouteService;
use Nette\DI\ContainerBuilder;

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



	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		$container->addDefinition('cmsManager')
				->setClass('\App\CoreModule\CmsManager', array('@container'));
		

		$container->addDefinition("pageRepository")
				->setClass("\Venne\Doctrine\ORM\BaseRepository")
				->setFactory("@entityManager::getRepository", array("\\App\\CoreModule\\PageEntity"))
				->setAutowired(false);

		$container->addDefinition("panelControl")
				->setClass("App\CoreModule\PanelControl")
				->setAutowired(false)
				->setShared(false);
		
		$container->addDefinition("websiteFormControl")
				->setClass("App\CoreModule\WebsiteForm")
				->addSetup("setSuccessLink", "default")
				->addSetup("setFlashMessage", "Website has been saved")
				->addSetup("setSubmitLabel", "Save")
				->addSetup("setRoot", "parameters.website")
				->setAutowired(false)
				->setShared(false);
		
		$container->addDefinition("modulesDefaultFormControl")
				->setClass("App\CoreModule\ModulesDefaultForm")
				->addSetup("setSuccessLink", "default")
				->addSetup("setFlashMessage", "Changes has been saved")
				->addSetup("setSubmitLabel", "Save")
				->addSetup("setRoot", "parameters.website")
				->setAutowired(false)
				->setShared(false);

		$container->addDefinition("systemFormControl")
				->setClass("App\CoreModule\SystemForm")
				->addSetup("setSuccessLink", "default")
				->addSetup("setFlashMessage", "Global settings has been updated")
				->addSetup("setSubmitLabel", "Save")
				->addSetup("setRoot", "parameters")
				->setAutowired(false)
				->setShared(false);
		
		$container->addDefinition("systemModeFormControl")
				->setClass("App\CoreModule\SystemModeForm", array("@configManager", "%configDir%", "%mode%"))
				->setParameters(array("mode"=>NULL))
				->addSetup("setSuccessLink", "default")
				->addSetup("setFlashMessage", "New mode has been saved")
				->addSetup("setSubmitLabel", "Save")
				->setAutowired(false)
				->setShared(false);
		
		$container->addDefinition("systemDebuggerFormControl")
				->setClass("App\CoreModule\SystemDebuggerForm")
				->addSetup("setSuccessLink", "default")
				->addSetup("setFlashMessage", "Debugger settings has been updated")
				->addSetup("setSubmitLabel", "Save")
				->addSetup("setRoot", "parameters.debugger")
				->setAutowired(false)
				->setShared(false);
		
		$container->addDefinition("systemDatabaseFormControl")
				->setClass("App\CoreModule\SystemDatabaseForm")
				->addSetup("setSuccessLink", "default")
				->addSetup("setFlashMessage", "Database settings has been updated")
				->addSetup("setSubmitLabel", "Save")
				->addSetup("setRoot", "parameters.database")
				->setAutowired(false)
				->setShared(false);
		
		$container->addDefinition("systemAccountFormControl")
				->setClass("App\CoreModule\SystemAccountForm")
				->addSetup("setSuccessLink", "default")
				->addSetup("setFlashMessage", "Account settings has been updated")
				->addSetup("setSubmitLabel", "Save")
				->addSetup("setRoot", "parameters.admin")
				->setAutowired(false)
				->setShared(false);
		
		$container->addDefinition("extensionsControl")
				->setClass("App\CoreModule\ContentExtensionsControl")
				->setShared(false)
				->setAutowired(false)
				->addTag("element");

		$container->addDefinition("scannerService")
				->setClass("App\CoreModule\ScannerService", array("@container"))
				->addTag("service");

		$container->addDefinition("layoutService")
				->setClass("App\CoreModule\LayoutService", array("@container", "layout", "@entityManager"))
				->addTag("service");

		$container->addDefinition("userService")
				->setClass("App\CoreModule\UserService", array("@container", "user", "@entityManager"))
				->addTag("service");

		$container->addDefinition("roleService")
				->setClass("App\CoreModule\RoleService", array("@container", "role", "@entityManager"))
				->addTag("service");

		$container->addDefinition("permissionService")
				->setClass("App\CoreModule\RoleService", array("@container", "permission", "@entityManager"))
				->addTag("service");


		$container->addDefinition("userRepository")
				->setClass("Venne\Doctrine\ORM\BaseRepository")
				->setFactory("@entityManager::getRepository", array("\\App\\CoreModule\\UserEntity"))
				->addTag("repository")
				->setAutowired(false);

		$container->addDefinition("roleRepository")
				->setClass("Venne\Doctrine\ORM\BaseRepository")
				->setFactory("@entityManager::getRepository", array("\\App\\CoreModule\\RoleEntity"))
				->addTag("repository")
				->setAutowired(false);

		$container->addDefinition("permissionRepository")
				->setClass("Venne\Doctrine\ORM\BaseRepository")
				->setFactory("@entityManager::getRepository", array("\\App\\CoreModule\\PermissionEntity"))
				->addTag("repository")
				->setAutowired(false);

		$container->addDefinition("languageRepository")
				->setClass("Venne\Doctrine\ORM\BaseRepository")
				->setFactory("@entityManager::getRepository", array("\\App\\CoreModule\\LanguageEntity"))
				->addTag("repository")
				->setAutowired(false);


		$container->addDefinition("configManager")
				->setClass("Venne\Config\ConfigBuilder", array("%configDir%/global.neon"))
				->addTag("manager");

		$container->addDefinition("moduleManager")
				->setClass("App\CoreModule\ModuleManager", array("@container"))
				->addTag("manager");


		


		/** ------------------- mappers --------------------- */
		$container->addDefinition("configFormMapper")
				->setClass("Venne\Forms\Mapping\ConfigFormMapper", array($container->parameters["appDir"] . "/config/global.neon"));

		$container->addDefinition("entityFormMapper")
				->setClass("Venne\Forms\Mapping\EntityFormMapper", array("@entityManager", new \Venne\Doctrine\Mapping\TypeMapper));
	}



	public function configure(\Nette\DI\Container $container, \App\CoreModule\CmsManager $manager)
	{
		parent::configure($container, $manager);

		$manager->addEventListener(array(\App\CoreModule\Events::onAdminMenu), $this);
		$manager->addEventSubscriber(new \Venne\Doctrine\Mapping\DiscriminatorMapDiscoveryListener(new \Doctrine\Common\Annotations\AnnotationReader));
	}



	public function setPermissions(\Nette\DI\Container $container, \Nette\Security\Permission $permissions)
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
		$permissions->addResource("SystemModule\\AdminModule\\LogPresenter", "SystemModule\\AdminModule");

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



	public function install(\Nette\DI\Container $container)
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
