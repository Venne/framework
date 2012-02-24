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
class VenneExtension extends CompilerExtension
{


	public $defaults = array('stopwatch' => array('debugger' => TRUE,),);



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig();


		$container->addDefinition("translator")
			->setClass("Venne\Localization\Translator")
			->addSetup("setLang", "cs");
		//->addSetup("addDictionary", array('Venne'));

		$container->addDefinition("translatorPanel")
			->setClass("Venne\Localization\Panel");

		$container->addDefinition("authorizatorFactory")
			->setFactory("App\CoreModule\AuthorizatorFactory")
			->setAutowired(false);

		$container->addDefinition("authorizator")
			->setClass("Nette\Security\Permission")
			->setFactory("@authorizatorFactory::getCurrentPermissions");

		$container->addDefinition("authenticator")
			->setClass("App\CoreModule\Authenticator", array("@container"));

		foreach ($container->parameters["modules"] as $module => $item) {
			$container->addDefinition($module . "Module")
				->addTag("module")
				->setClass("App\\" . ucfirst($module) . "Module\\Module");
		}

		if ($config["stopwatch"]["debugger"]) {
			$container->getDefinition("user")
				->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array(new \Nette\DI\Statement('Venne\Panels\Stopwatch')));
		}

		$container->addDefinition("configManager")
			->setClass("Venne\Config\ConfigBuilder", array("%configDir%/global.neon"))
			->addTag("manager");


		// Mappers
		$container->addDefinition("configFormMapper")
			->setClass("Venne\Forms\Mapping\ConfigFormMapper", array($container->parameters["appDir"] . "/config/global.neon"));

		$container->addDefinition("entityFormMapper")
			->setClass("Venne\Forms\Mapping\EntityFormMapper", array("@entityManager", new \Venne\Doctrine\Mapping\TypeMapper));


		// Template
		$container->addDefinition($this->prefix("templateConfigurator"))
			->setClass("Venne\Templating\TemplateConfigurator");


		// Macros
		$this->compileMacro("Venne\Assets\Macros\CssMacro", $this->prefix("cssMacro"));
		$this->compileMacro("Venne\Assets\Macros\JsMacro", $this->prefix("jsMacro"));


		// Helpers
		$container->addDefinition($this->prefix("helpers"))
			->setClass("Venne\Templating\Helpers");
	}


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		$this->registerMacroFactories($container);
		$this->registerHelperFactories($container);
	}


	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerMacroFactories(ContainerBuilder $container)
	{
		$config = $container->getDefinition($this->prefix('templateConfigurator'));

		foreach ($container->findByTag('macro') as $factory => $meta) {
			$config->addSetup('addFactory', array($factory));
		}
	}


	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 */
	protected function registerHelperFactories(ContainerBuilder $container)
	{
		$config = $container->getDefinition($this->prefix('helpers'));

		foreach ($container->findByTag('helper') as $factory => $meta) {
			$config->addSetup('addHelper', array(substr($factory, strrpos($factory, ".") + 1, -6), $factory));
		}
	}

}

