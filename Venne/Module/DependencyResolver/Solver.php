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
use Venne\Module\ModuleManager;
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

	/** @var array */
	protected $modulesConfig;

	/** @var string */
	protected $libsDir;

	/** @var string */
	protected $modulesDir;


	/**
	 * @param $modules
	 * @param $installedModules
	 * @param $modulesConfig
	 * @param $libsDir
	 * @param $modulesDir
	 */
	public function __construct($modules, $installedModules, $modulesConfig, $libsDir, $modulesDir)
	{
		$this->modules = $modules;
		$this->installedModules = $installedModules;
		$this->modulesConfig = & $modulesConfig;
		$this->libsDir = $libsDir;
		$this->modulesDir = $modulesDir;
	}


	/**
	 * @param IModule $module
	 * @param Problem $problem
	 */
	public function testInstall(IModule $module, Problem $problem = NULL)
	{
		return $this->testUpgrade($module, $problem);
	}


	/**
	 * @param IModule $module
	 * @param Problem $problem
	 * @throws InvalidArgumentException
	 */
	public function testUninstall(IModule $module, Problem $problem = NULL)
	{
		$installedModules = & $this->installedModules;

		foreach ($installedModules as $sourceModule) {
			if ($sourceModule->getName() === $module->getName()) {
				continue;
			}

			foreach ($sourceModule->getRequire() as $name => $require) {
				if ($name == $module->getName()) {

					if ($problem) {

						try {
							$solver = $this->createSolver();
							$solver->testUninstall($sourceModule, $problem);
						} catch (InvalidArgumentException $e) {
							throw new InvalidArgumentException("Module '{$sourceModule->getName()}' depend on '{$module->getName()}' which is not installed.");
						}

						$job = new Job(Job::ACTION_UNINSTALL, $sourceModule);
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


	/**
	 * @param IModule $module
	 * @param Problem $problem
	 * @throws InvalidArgumentException
	 */
	public function testUpgrade(IModule $module, Problem $problem = NULL)
	{
		$installedModules = & $this->installedModules;
		$modules = & $this->modules;

		foreach ($module->getRequire() as $name => $require) {
			$requires = VersionHelpers::normalizeRequire($require);

			if (!isset($installedModules[$name])) {

				if ($problem && isset($modules[$name])) {

					try {
						$solver = $this->createSolver();
						$solver->testInstall($modules[$name], $problem);
					} catch (InvalidArgumentException $e) {
						throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' which is not installed.");
					}

					$job = new Job(Job::ACTION_INSTALL, $modules[$name]);
					if (!$problem->hasSolution($job)) {
						$problem->addSolution($job);
					}
					$installedModules[$name] = $modules[$name];
					$tr = array(
						$this->libsDir => '%libsDir%',
						$this->modulesDir => '%modulesDir%',
					);
					$this->modulesConfig[$name] = array(
						ModuleManager::MODULE_STATUS => ModuleManager::STATUS_INSTALLED,
						ModuleManager::MODULE_ACTION => ModuleManager::ACTION_NONE,
						ModuleManager::MODULE_CLASS => $module->getClassName(),
						ModuleManager::MODULE_VERSION => $module->getVersion(),
						ModuleManager::MODULE_PATH => str_replace(array_keys($tr), array_merge($tr), $module->getPath()),
						ModuleManager::MODULE_AUTOLOAD => str_replace(array_keys($tr), array_merge($tr), $module->getAutoload()),
						ModuleManager::MODULE_REQUIRE => $module->getRequire(),
					);

				} else {
					throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' which is not installed.");
				}
			}

			foreach ($requires as $items) {
				foreach ($items as $operator => $version) {
					$dVersion = $this->modulesConfig[$name][ModuleManager::MODULE_VERSION];
					if (!version_compare($dVersion, $version, $operator)) {

						if (!version_compare($installedModules[$name]->getVersion(), $version, $operator)) {
							throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' with version '{$require}'. Current version is '{$dVersion}'.");
						}

						// dependency must be upgraded
						if ($problem && isset($modules[$name])) {

							try {
								$solver = $this->createSolver();
								$solver->testUpgrade($modules[$name], $problem);
							} catch (InvalidArgumentException $e) {
								throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' with version '{$require}'. Current version is '{$dVersion}'.");
							}

							$job = new Job(Job::ACTION_UPGRADE, $modules[$name]);
							if (!$problem->hasSolution($job)) {
								$problem->addSolution($job);
							}
							$this->modulesConfig[$name][ModuleManager::MODULE_VERSION] = $modules[$name]->getVersion();

						} else {
							throw new InvalidArgumentException("Module '{$module->getName()}' depend on '{$name}' with version '{$require}'. Current version is '{$dVersion}'.");
						}
					}
				}
			}
		}
	}


	/**
	 * @return Solver
	 */
	private function createSolver()
	{
		return new static($this->modules, $this->installedModules, $this->modulesConfig, $this->libsDir, $this->modulesDir);
	}
}

