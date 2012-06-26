<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\Composer\Commands;

use Venne;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Venne\Module\Composer\ComposerManager;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class AddRequireCommand extends Command
{

	/** @var ComposerManager */
	protected $manager;

	function __construct(ComposerManager $manager)
	{
		parent::__construct();

		$this->manager = $manager;
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('venne:composer:add')
			->setDescription('Add package into project.')
			->setDefinition(array(
			new InputArgument('package', InputArgument::REQUIRED, 'Package name.'),
			new InputArgument('version', InputArgument::REQUIRED, 'version.')
		));
	}

	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$out = $this->manager->addRequire($input->getArgument('package'), $input->getArgument('version'));

		$output->writeln($out);
	}

}
