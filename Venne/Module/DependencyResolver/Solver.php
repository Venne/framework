<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\DependencyResolver;

use Venne;
use Venne\Module\VersionHelpers;
use Venne\Module\IModule;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Solver extends Object
{

	/** @var IModule[] */
	protected $installedModules;

	/** @var IModule[] */
	protected $modules;


	/**
	 * @param $modules
	 * @param $installedModules
	 */
	public function __construct($modules, $installedModules)
	{
		$this->modules = $modules;
		$this->installedModules = $installedModules;
	}


	/**
	 * @param IModule $module
	 * @throws InvalidArgumentException
	 */
	public function testInstall(IModule $module, Problem $problem = NULL, $isRecursion = false)
	{
		$installedModules = $this->installedModules;
		$modules = $this->modules;

		foreach ($module->getRequire() as $name => $require) {
			$requires = VersionHelpers::normalizeRequire($require);

			if (!isset($installedModules[$name])) {

				if ($problem && isset($modules[$name])) {

					try {
						$solver = new Solver($modules, $installedModules);
						$solver->testInstall($modules[$name], $problem, true);
					} catch (InvalidArgumentException $e) {
						throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' which is not installed.");
					}

					$job = new Job('install', $modules[$name]);
					if (!$problem->hasSolution($job)) {
						$problem->addSolution($job);
					}
					$installedModules[$name] = $modules[$name];
				} else {
					throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' which is not installed.");
				}
			}

			foreach ($requires as $items) {
				foreach ($items as $operator => $version) {
					if (!version_compare($installedModules[$name]->getVersion(), $version, $operator)) {
						throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' with version '{$require}'. Current version is '{$installedModules[$name]->getVersion()}'.");
					}
				}
			}
		}
	}


	/**
	 * @param IModule $module
	 * @throws InvalidArgumentException
	 */
	public function testUninstall(IModule $module, Problem $problem = NULL)
	{
		foreach ($this->installedModules as $sourceModule) {
			if ($sourceModule->getName() === $module->getName()) {
				continue;
			}

			foreach ($sourceModule->getRequire() as $name => $require) {
				if ($name == $module->getName()) {
					throw new InvalidArgumentException("Module '{$sourceModule->getName()}' depend on '{$module->getName()}'.");
				}
			}
		}
	}


	public function testUpgrade(IModule $module)
	{
	}
}

