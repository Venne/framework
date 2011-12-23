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
class VenneExtension extends CompilerExtension {



	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		$container->addDefinition("latteEngine")
				->setClass("Nette\Latte\Engine");

		$container->addDefinition("templateContainer")
				->setClass("Venne\Templating\TemplateContainer");

		$container->addDefinition("translator")
				->setClass("Venne\Localization\Translator")
				->addSetup("setLang", "cs")
				->addSetup("addDictionary", array('Venne', $container->parameters["wwwDir"] . "/themes/" . $container->parameters["website"]["theme"]));

		$container->addDefinition("translatorPanel")
				->setClass("Venne\Localization\Panel");

		$container->addDefinition("authorizatorFactory")
				->setFactory("App\SecurityModule\AuthorizatorFactory", array("@container"))
				->setInternal(TRUE)
				->setShared(FALSE)
				->setAutowired(false);

		$container->addDefinition("authorizator")
				->setClass("Nette\Security\Permission")
				->setFactory("@authorizatorFactory::create");

		$container->addDefinition("authenticator")
				->setClass("App\CoreModule\Authenticator", array("@container"));

		foreach ($container->parameters["modules"] as $module=>$item) {
			$container->addDefinition(ucfirst($module) . "Plugin")
					->addTag("module")
					->setClass("App\\" . ucfirst($module) . "Module\\Module");
		}
	}

}

