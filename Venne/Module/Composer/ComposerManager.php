<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\Composer;

use Venne;
use Nette\Object;
use Composer\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ComposerManager extends Object
{

	/** @var string */
	protected $composerPath;

	/** @var Application */
	protected $application;

	function __construct($composerPath)
	{
		$this->composerPath = $composerPath;

		$this->application = new Application();
		$this->application->setAutoExit(false);
	}


	public function runCommand($string)
	{
		$filename = '/tmp/aaa';
		$file = fopen($filename, "w");

		$input = new StringInput($string);
		$output = new StreamOutput($file);

		$this->application->run($input, $output);
		\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE;

		fclose($file);

		return file_get_contents($filename);
	}


	protected function loadConfig()
	{
		return json_decode(file_get_contents($this->composerPath . '/composer.json'), true);
	}


	protected function saveConfig($data)
	{
		file_put_contents($this->composerPath . '/composer.json', str_replace('\/', '/', json_encode($data, JSON_PRETTY_PRINT)));
	}


	public function addRequire($name, $version)
	{
		$data = $this->loadConfig();
		$data['require'][$name] = $version;
		$this->saveConfig($data);
	}


	public function removeRequire($name)
	{
		$data = $this->loadConfig();
		unset($data['require'][$name]);
		$this->saveConfig($data);
	}


	public function install()
	{
		return $this->runCommand('install');
	}


	public function update()
	{
		return $this->runCommand('install');
	}


}

