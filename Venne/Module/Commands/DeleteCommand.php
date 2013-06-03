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

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class DeleteCommand extends Command
{

	/** @var string */
	protected $libsDir;


	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Container $container)
	{
		parent::__construct();

		$this->libsDir = $container->parameters['libsDir'];
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('venne:module:delete')
			->addArgument('module', InputArgument::REQUIRED, 'Module name')
			->setDescription('Delete module.');
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$module = $input->getArgument('module');
		$path = "{$this->libsDir}/venne/{$module}-module";

		if (!file_exists($path)) {
			$output->writeln("<error>Path '" . $path . "' does not exist.</error>");
			return;
		}

		\Venne\Utils\File::rmdir($path, TRUE);
	}
}
