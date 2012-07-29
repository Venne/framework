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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Venne\Module\ModuleManager;


class RenameCommand extends Command
{
	/** @var Venne\Module\ModuleManager */
	protected $moduleManager;

	const PATH = "./vendor/venne/";

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
			->setName('venne:module:rename')
			->setDescription('Rename module.')
			->setDefinition(array(
			new InputArgument('module', InputArgument::REQUIRED, 'Module name.'),
			new InputArgument('newName', InputArgument::REQUIRED, 'New name for module.')
		));
	}

	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$old = $input->getArgument('module');
		$new = $input->getArgument('newName');


		$oldLower = lcfirst($old);
		$newLower = lcfirst($new);

		$oldUpper = ucfirst($old);
		$newUpper = ucfirst($new);

		$modulePath = self::PATH.$oldLower."-module";

		if(!is_dir($modulePath)){
			$output->writeln("Module doesn't exists");
			$output->writeln("Exiting...");
			return;
		}

		$files = $this->glob_recursive("$modulePath/*.*");

		foreach($files as $file){
			$this->replace($file,$oldLower,$newLower);
			$this->replace($file,$oldUpper,$newUpper);
		}
		rename($modulePath, self::PATH.$newLower."-module");
		$output->writeln("Module successfully renamed.");
	}

	/**
	 * Return files matching pattern. Works recursively.
	 * @param $pattern
	 * @param int $flags
	 * @return array
	 */
	protected function glob_recursive($pattern, $flags = 0)
	{
		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->glob_recursive($dir . '/' . basename($pattern), $flags));
		}
		return $files;
	}

	/**
	 * Replace string in file
	 * @param $filename
	 * @param $old
	 * @param $new
	 */
	protected function replace($filename, $old, $new)
	{
		$file = file_get_contents($filename);
		file_put_contents($filename, preg_replace("/$old/","$new",$file));
	}
}

