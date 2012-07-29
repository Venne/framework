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

use Venne;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Venne\Module\ModuleManager;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class UninstallCommand extends Command
{

	/** @var ModuleManager */
	protected $moduleManager;

	function __construct(ModuleManager $moduleManager)
	{
		parent::__construct();

		$this->moduleManager = $moduleManager;
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('venne:module:uninstall')
			->setDescription('Uninstall module.')
			->setDefinition(array(
			new InputArgument('module', InputArgument::REQUIRED, 'Module name.')
		));
	}

	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$module = $input->getArgument('module');

		$this->moduleManager->uninstallModule($module);
	}

}
