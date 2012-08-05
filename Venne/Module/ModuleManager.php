<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module;

use Venne;
use Nette\Object;
use Nette\DI\Container;
use Nette\Utils\Strings;
use Composer\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleManager extends Object
{

	const RESOURCES_MODE_SYMLINK = 'symlink';

	const RESOURCES_MODE_COPY = 'copy';

	const MODULE_STATUS_INSTALLED = 'installed';

	const MODULE_STATUS_PAUSED = 'paused';

	/** @var string */
	protected $resourcesMode;

	/** @var string */
	protected $moduleConfig;

	/** @var string */
	protected $resourcesDir;

	/** @var Container|SystemContainer */
	protected $context;

	/** @var string */
	protected $resourceModes = array(
		self::RESOURCES_MODE_SYMLINK,
		self::RESOURCES_MODE_COPY,
	);

	protected $moduleStatuses = array(
		self::MODULE_STATUS_INSTALLED,
		self::MODULE_STATUS_PAUSED,
	);


	/**
	 * @param \Nette\DI\Container $context
	 * @param array $modules
	 * @param string $moduleConfig
	 * @param string $resourcesMode
	 * @param string $resourcesDir
	 */
	public function __construct(Container $context, $moduleConfig, $resourcesMode, $resourcesDir)
	{
		$this->context = $context;
		$this->moduleConfig = $moduleConfig;
		$this->setResourcesMode($resourcesMode);
		$this->resourcesDir = $resourcesDir;
	}


	/**
	 * Update dependencies by Composer.
	 *
	 * @param null|string $composerFile
	 */
	public function updateByComposer($composerFile = NULL)
	{
		putenv("COMPOSER_VENDOR_DIR={$this->context->parameters['libsDir']}");
		$composerFile = $composerFile ?: $this->context->parameters['appDir'] . '/../composer.json';

		if($composerFile) {
			putenv("COMPOSER=$composerFile");
		}

		$data = $this->runCommand('update');

		if($composerFile) {
			putenv("COMPOSER=''");
		}

		return $data;
	}


	/**
	 * @param $resourcesMode
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setResourcesMode($resourcesMode)
	{
		if (array_search($resourcesMode, $this->resourceModes) === false) {
			throw new \Nette\InvalidArgumentException;
		}

		$this->resourcesMode = $resourcesMode;
	}


	public function scanRepositoryModules()
	{
		$arr = array();

		$modules = $this->context->venne->composerManager->runCommand('search venne');
		$modules = explode("\n", $modules);
		foreach ($modules as $module) {
			$module = explode(" ", $module);
			$module = $module[0];

			if (strpos($module, '<highlight>venne</highlight>/') !== false && substr($module, -7) == '-module') {
				$module = substr($module, 29, -7);
				if (!isset($arr[$module])) {
					$arr[$module] = true;
				}
			}
		}

		foreach ($arr as $name => $item) {
			$values = array();
			$data = $this->context->venne->composerManager->runCommand("show venne/{$name}-module");
			$data = explode("\n", $data);
			foreach ($data as $row) {
				$row = explode(':', $row);
				if (count($row) == 2) {
					$values[trim($row[0])] = trim($row[1]);
				}
			}

			$arr[$name] = $values;
		}

		file_put_contents($this->context->parameters['tempDir'] . '/modules-cache', json_encode($arr));
	}


	public function findRepositoryModules()
	{
		$file = $this->context->parameters['tempDir'] . '/modules-cache';

		if (!file_exists($file)) {
			$this->scanRepositoryModules();
		}

		return json_decode(file_get_contents($file));
	}

	/*************************************************************************************/


	/**
	 * @param $string
	 * @return string
	 */
	protected function runCommand($string)
	{
		$application = new Application();
		$application->setAutoExit(false);

		$filename = $this->context->parameters['tempDir'] . '/moduleManager-command';
		$file = fopen($filename, "w");

		$input = new StringInput($string);
		$output = new StreamOutput($file);

		$application->run($input, $output);
		\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_VERBOSE;

		fclose($file);

		$data = file_get_contents($filename);
		unlink($filename);
		return $data;
	}
}

