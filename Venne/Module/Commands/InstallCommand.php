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
use Venne\Module\DependencyResolver\Problem;
use Nette\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class InstallCommand extends Command
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
			->setName('venne:module:install')
			->addArgument('module', InputArgument::REQUIRED, 'Module name')
			->addOption('noconfirm', NULL, InputOption::VALUE_NONE, 'do not ask for any confirmation')
			->setDescription('Install module.');
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var $module IModule */
		$module = $this->moduleManager->createInstance($input->getArgument('module'));

		try {
			/** @var $problem Problem */
			$problem = $this->moduleManager->testInstall($module);
		} catch (InvalidArgumentException $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");
			return;
		}

		if (!$input->getOption('noconfirm') && count($problem->getSolutions()) > 0) {
			$output->writeln("<info>install : {$module->getName()}</info>");
			foreach ($problem->getSolutions() as $job) {
				$output->writeln("<info>{$job->getAction()} : {$job->getModule()->getName()}</info>");
			}

			$dialog = $this->getHelperSet()->get('dialog');
			if (!$dialog->askConfirmation($output, '<question>Continue with this actions? [y/N]</question>', false)) {
				return;
			}
		}

		try {
			foreach ($problem->getSolutions() as $job) {
				$this->moduleManager->doAction($job->getAction(), $job->getModule());
				$output->writeln("Module '{$job->getModule()->getName()}' has been installed.");
			}
			$this->moduleManager->install($module);
			$output->writeln("Module '{$input->getArgument('module')}' has been installed.");
		} catch (InvalidArgumentException $e) {
			$output->writeln("<error>{$e->getMessage()}</error>");
		}
	}
}
