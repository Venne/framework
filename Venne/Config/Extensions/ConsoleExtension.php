<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config\Extensions;

use Venne;
use Nette\DI\ContainerBuilder;
use Nette\Config\CompilerExtension;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConsoleExtension extends CompilerExtension
{

	/** @var string|NULL */
	protected $consoleEntityManager;


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('consoleCommandDBALRunSql'))
			->setClass('Doctrine\DBAL\Tools\Console\Command\RunSqlCommand')
			->addTag('commnad')
			->setAutowired(FALSE);
		$container->addDefinition($this->prefix('consoleCommandDBALImport'))
			->setClass('Doctrine\DBAL\Tools\Console\Command\ImportCommand')
			->addTag('command')
			->setAutowired(FALSE);

		// console commands - ORM
		$container->addDefinition($this->prefix('consoleCommandORMCreate'))
			->setClass('Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand')
			->addTag('command')
			->setAutowired(FALSE);
		$container->addDefinition($this->prefix('consoleCommandORMUpdate'))
			->setClass('Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand')
			->addTag('command')
			->setAutowired(FALSE);
		$container->addDefinition($this->prefix('consoleCommandORMDrop'))
			->setClass('Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand')
			->addTag('command')
			->setAutowired(FALSE);
		$container->addDefinition($this->prefix('consoleCommandORMGenerateProxies'))
			->setClass('Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand')
			->addTag('command')
			->setAutowired(FALSE);
		$container->addDefinition($this->prefix('consoleCommandORMRunDql'))
			->setClass('Doctrine\ORM\Tools\Console\Command\RunDqlCommand')
			->addTag('command')
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('consoleHelperset'))
			->setClass('Symfony\Component\Console\Helper\HelperSet');

		// console
		$container->addDefinition($this->prefix('helperSet'))
			->setClass('Symfony\Component\Console\Helper\HelperSet');

		$container->addDefinition($this->prefix('console'))
			->setClass('Symfony\Component\Console\Application')
			->addSetup('setHelperSet', array('@console.helperSet'))
			->addSetup('setCatchExceptions', false);
	}


	/**
	 * @param \Doctrine\ORM\EntityManager
	 * @return \Symfony\Component\Console\Helper\HelperSet
	 */
	public static function createConsoleHelperSet(\Doctrine\ORM\EntityManager $em)
	{
		$helperSet = new \Symfony\Component\Console\Helper\HelperSet;
		$helperSet->set(new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em), 'em');
		$helperSet->set(new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()), 'db');
		$helperSet->set(new \Symfony\Component\Console\Helper\DialogHelper, 'dialog');

		return $helperSet;
	}


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();

		$this->registerCommands();
		$this->registerHelpers();
	}

	protected function registerHelpers()
	{
		$container = $this->getContainerBuilder();
		$definition = $container->getDefinition($this->prefix('helperSet'));

		foreach ($container->findByTag("commandHelper") as $item=>$meta) {
			$definition->addSetup("set", array("@{$item}", $meta));
		}
	}


	protected function registerCommands()
	{
		$container = $this->getContainerBuilder();
		$console = $container->getDefinition($this->prefix('console'));

		foreach ($container->findByTag("command") as $item=>$meta) {
			$console->addSetup("add", "@{$item}");
		}
	}

}

