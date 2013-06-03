<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\Commands;

use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Venne\Module\ModuleManager;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class CreateCommand extends Command
{

	/** @var string */
	protected $modulesDir;

	/** @var ModuleManager */
	protected $moduleManager;


	/**
	 * @param \Venne\Module\ModuleManager $moduleManager
	 */
	public function __construct(ModuleManager $moduleManager, Container $container)
	{
		parent::__construct();

		$this->moduleManager = $moduleManager;
		$this->modulesDir = $container->parameters['modulesDir'];
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('venne:module:create')
			->addArgument('module', InputArgument::REQUIRED, 'Module name')
			->setDescription('Create module.');
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$module = $input->getArgument('module');
		$modules = $this->moduleManager->getModules();
		$path = "{$this->modulesDir}/{$module}-module";

		if (isset($modules[$module])) {
			$output->writeln("<error>Module '{$module}' already exists.</error>");
			return;
		}

		if (file_exists($path)) {
			$output->writeln("<error>Path '" . $path . "' exists.</error>");
			return;
		}

		if (!is_writable(dirname($path))) {
			$output->writeln("<error>Path '" . dirname($path) . "' is not writable.</error>");
			return;
		}

		umask(0000);
		mkdir($path, 0777, TRUE);

		file_put_contents($path . '/Module.php', $this->getModuleFile($module));
		file_put_contents($path . '/composer.json', $this->getComposerFile($module));
		file_put_contents($path . '/readme.md', $this->getReadmeFile($module));

		mkdir($path . '/Resources/config', 0777, TRUE);
		mkdir($path . '/Resources/public', 0777, TRUE);
		mkdir($path . '/Resources/translations', 0777, TRUE);
		mkdir($path . '/Resources/layouts', 0777, TRUE);
		mkdir($path . '/' . ucfirst($module) . 'Module', 0777, TRUE);
	}


	protected function getModuleFile($name)
	{
		return '<?php

namespace ' . ucfirst($name) . 'Module;

use Venne\Module\ComposerModule;

class Module extends ComposerModule
{


}
';
	}


	protected function getComposerFile($name)
	{
		return '{
	"name":"venne/' . $name . '-module",
	"description":"",
	"keywords":["cms", "nette", "venne", "module"],
	"version":"2.0.0",
	"require":{
		"php":">=5.3.2"
	},
	"autoload":{
		"psr-0":{
			"' . ucfirst($name) . 'Module":""
		}
	},
	"extra":{
		"branch-alias":{
			"dev-master":"2.0.x-dev"
		}
	}
}
';
	}


	protected function getReadmeFile($name)
	{
		return '
' . ucfirst($name) . 'Module module for Venne:CMS
======================================

Thank you for your interest.

Installation
------------

- Copy this folder to /vendor/venne
- Active this module in administration
';
	}
}
