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
use Nette\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Venne\Module\ModuleManager;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class ListCommand extends Command
{

	/** @var ModuleManager */
	protected $moduleManager;

	/** @var Container|\SystemContainer */
	protected $container;


	/**
	 * @param Container $container
	 * @param ModuleManager $moduleManager
	 */
	public function __construct(Container $container, ModuleManager $moduleManager)
	{
		parent::__construct();

		$this->container = $container;
		$this->moduleManager = $moduleManager;
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('venne:module:list')
			->setDescription('List modules.');
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			foreach ($this->moduleManager->findModules() as $module) {
				$configVersion = $this->container->parameters['modules'][$module->getName()][ModuleManager::MODULE_VERSION];

				if ($configVersion == $module->getVersion()) {
					$version = $module->getVersion();
				} else {
					$version = $module->getVersion() . ' (needs upgrade from: '. $configVersion .')';
				}

				$output->writeln(sprintf('<info>%25s</info> | status: <comment>%-12s</comment> | version: <comment>%s</comment>', $module->getName(), $this->moduleManager->getStatus($module), $version));
			}
		} catch (InvalidArgumentException $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");
		}
	}
}
