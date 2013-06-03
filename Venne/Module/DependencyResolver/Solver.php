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

use Nette\InvalidArgumentException;
use Nette\Object;
use Venne\Module\IModule;
use Venne\Module\VersionHelpers;

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
	public function testInstall(IModule $module, Problem $problem = NULL)
	{
		$installedModules = $this->installedModules;
		$modules = $this->modules;

		foreach ($module->getRequire() as $name => $require) {
			$requires = VersionHelpers::normalizeRequire($require);

			if (!isset($installedModules[$name])) {

				if ($problem && isset($modules[$name])) {

					try {
						$solver = new Solver($modules, $installedModules);
						$solver->testInstall($modules[$name], $problem, TRUE);
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
		$installedModules = $this->installedModules;
		$modules = $this->modules;

		foreach ($installedModules as $sourceModule) {
			if ($sourceModule->getName() === $module->getName()) {
				continue;
			}

			foreach ($sourceModule->getRequire() as $name => $require) {
				if ($name == $module->getName()) {

					if ($problem) {

						try {
							$solver = new Solver($modules, $installedModules);
							$solver->testUninstall($sourceModule, $problem, TRUE);
						} catch (InvalidArgumentException $e) {
							throw new InvalidArgumentException("Module '{$sourceModule->getName()}' depend on '{$module->getName()}' which is not installed.");
						}

						$job = new Job('uninstall', $sourceModule);
						if (!$problem->hasSolution($job)) {
							$problem->addSolution($job);
						}
					} else {
						throw new InvalidArgumentException("Module '{$sourceModule->getName()}' depend on '{$module->getName()}'.");
					}
				}
			}
		}
	}


	public function testUpgrade(IModule $module)
	{
	}
}

