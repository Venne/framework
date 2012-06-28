<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Caching\Commands;

use Venne;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Nette\Caching\Cache;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class CacheCommand extends Command
{

	/** @var Cache */
	protected $cache;

	function __construct(\Nette\Caching\Storages\FileStorage $fileStorage)
	{
		parent::__construct();

		$this->cache = new Cache($fileStorage);
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('venne:cache:clear')
			->setDescription('Clear cache.')
			->addOption('tag', NULL, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Tags to invalidate');
	}

	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (($ns = $input->getOption('tag'))) {
			$this->cache->clean(array(
				Cache::TAGS => $input->getOption('tag'),
			));
			$output->writeln('Cache tags "' . implode(', ', $ns) . '" has been invalidated.');
		} else {
			$this->cache->clean();
			$output->writeln('Cache has been invalidated.');
		}
	}

}
