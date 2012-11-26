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
use Venne\Module\ModuleManager;
use Nette\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class UpgradeCommand extends Command
{

	/** @var ModuleManager */
	protected $moduleManager;


	/**
	 * @param \Venne\Module\ModuleManager $moduleManager
	 */
	public function __construct(ModuleManager $moduleManager)
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
			->setName('venne:module:upgrade')
			->addArgument('module', InputArgument::REQUIRED, 'Module name')
			->setDescription('Upgrade module.');
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$this->moduleManager->upgrade($this->moduleManager->createInstance($input->getArgument('module')));
			$output->writeln("Module '{$input->getArgument('module')}' has been upgraded.");
		} catch (InvalidArgumentException $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");
		}
	}
}
