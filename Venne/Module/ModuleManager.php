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
use Venne\Utils\File;
use Nette\DI\Container;
use Nette\Object;
use Nette\Utils\Finder;
use Nette\Config\Adapters\PhpAdapter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleManager extends Object
{

	const MODULE_CLASS = 'class';

	const MODULE_PATH = 'path';

	const MODULE_STATUS = 'status';

	const MODULE_VERSION = 'version';

	const MODULE_AUTOLOAD = 'autoload';

	const STATUS_UNINSTALLED = 'uninstalled';

	const STATUS_INSTALLED = 'installed';

	const STATUS_FOR_INSTALL = 'forInstall';

	const STATUS_FOR_UNINSTALL = 'forUninstall';

	const STATUS_FOR_UPGRADE = 'forUpgrade';

	/** @var Container */
	protected $context;

	/** @var string */
	protected $libsDir;

	/** @var string */
	protected $configDir;

	/** @var array */
	protected $modules;

	/** @var IModule[] */
	protected $_findModules;

	/** @var IModule[] */
	protected $_modules;


	/**
	 * @param \Nette\DI\Container $context
	 * @param $modules
	 * @param $libsDir
	 * @param $configDir
	 */
	public function __construct(Container $context, $modules, $libsDir, $configDir)
	{
		$this->context = $context;
		$this->modules = $modules;
		$this->libsDir = $libsDir;
		$this->configDir = $configDir;

		$this->findModules();
	}


	public function update()
	{
		// unregister
		foreach ($this->getModulesForUnregister() as $name => $args) {
			$this->unregister($name);
		}

		// register
		foreach ($this->getModulesForRegister() as $module) {
			$this->register($module);
		}

		// uninstall
		foreach ($this->getModulesForUninstall() as $module) {
			$this->uninstall($module);
		}

		// upgrade
		foreach ($this->getModulesForUpgrade() as $module) {
			$this->upgrade($module);
		}

		// install
		foreach ($this->getModulesForInstall() as $module) {
			$this->install($module);
		}
	}


	/**
	 * @param IModule $module
	 */
	protected function register(IModule $module)
	{
		$modules = $this->loadModuleConfig();
		if (!array_search($module->getName(), $modules['modules'])) {
			$modules['modules'][$module->getName()] = array(
				self::MODULE_STATUS => self::STATUS_UNINSTALLED,
				self::MODULE_CLASS => $module->getClassName(),
				self::MODULE_VERSION => $module->getVersion(),
				self::MODULE_PATH => str_replace($this->libsDir, '%libsDir%', $module->getPath()),
				self::MODULE_AUTOLOAD => str_replace($this->libsDir, '%libsDir%', $module->getAutoload()),
			);
		}
		$this->saveModuleConfig($modules);
		$this->modules = $modules['modules'];
	}


	/**
	 * @param $name
	 */
	protected function unregister($name)
	{
		$modules = $this->loadModuleConfig();
		unset($modules['modules'][$name]);
		$this->saveModuleConfig($modules);
		$this->modules = $modules['modules'];
	}


	/**
	 * @param IModule $module
	 */
	protected function install(IModule $module)
	{
		foreach ($module->getInstallers() as $class) {
			$installer = $this->context->createInstance($class);
			$installer->install($module);
		}

		$modules = $this->loadModuleConfig();
		$modules['modules'][$module->getName()][self::MODULE_STATUS] = self::STATUS_INSTALLED;
		$this->saveModuleConfig($modules);
	}


	/**
	 * @param IModule $module
	 */
	protected function uninstall(IModule $module)
	{
		foreach ($module->getInstallers() as $class) {
			$installer = $this->context->createInstance($class);
			$installer->uninstall($module);
		}

		$modules = $this->loadModuleConfig();
		$modules['modules'][$module->getName()][self::MODULE_STATUS] = self::STATUS_UNINSTALLED;
		$this->saveModuleConfig($modules);
	}


	/**
	 * @param IModule $module
	 */
	protected function upgrade(IModule $module)
	{
		$modules = $this->loadModuleConfig();
		$modules['modules'][$module->getName()][self::MODULE_STATUS] = self::STATUS_INSTALLED;
		$this->saveModuleConfig($modules);
	}


	/**
	 * @return IModule[]
	 */
	protected function findModules()
	{
		if ($this->_findModules === NULL) {
			$this->_findModules = array();

			foreach (Finder::findDirectories('*')->in($this->libsDir) as $dir) {
				foreach (Finder::findDirectories('*')->in($dir) as $dir2) {
					foreach (Finder::findFiles('Module.php')->in($dir2) as $file) {
						$classes = get_declared_classes();
						require_once $file->getPathname();
						$class = array_diff(get_declared_classes(), $classes);
						$class = end($class);

						/** @var $module IModule */
						$module = $this->createInstanceOfModule($class, dirname($file->getPathname()));

						$this->_findModules[$module->getName()] = $module;
					}
				}
			}
		}

		return $this->_findModules;
	}


	/**
	 * @return IModule[]
	 */
	protected function getModules()
	{
		if ($this->_modules === NULL) {
			$this->_modules = array();

			foreach ($this->modules as $name => $args) {
				if (file_exists($args[self::MODULE_PATH])) {
					$this->_modules[$name] = $this->createInstanceOfModule($args[self::MODULE_CLASS], $args[self::MODULE_PATH]);
				}
			}
		}

		return $this->_modules;
	}


	/**
	 * @return IModule[]
	 */
	protected function getModulesForRegister()
	{
		$activModules = $this->getModules();
		$modules = $this->findModules();
		$diff = array_diff(array_keys($modules), array_keys($activModules));

		$ret = array();
		foreach ($diff as $name) {
			$ret[$name] = $modules[$name];
		}

		return $ret;
	}


	/**
	 * @return array
	 */
	protected function getModulesForUnregister()
	{
		$ret = array();
		foreach ($this->modules as $name => $args) {
			if (!file_exists($args[self::MODULE_PATH])) {
				$ret[$name] = $args;
			}
		}
		return $ret;
	}


	/**
	 * @return IModule[]
	 */
	protected function getModulesForInstall()
	{
		$ret = array();
		foreach ($this->findModules() as $name => $module) {
			if ($this->getStatus($module) === self::STATUS_FOR_INSTALL) {
				$ret[$name] = $module;
			}
		}
		return $ret;
	}


	/**
	 * @return IModule[]
	 */
	protected function getModulesForUninstall()
	{
		$ret = array();
		foreach ($this->findModules() as $name => $module) {
			if ($this->getStatus($module) === self::STATUS_FOR_UNINSTALL) {
				$ret[$name] = $module;
			}
		}
		return $ret;
	}


	/**
	 * @return IModule[]
	 */
	protected function getModulesForUpgrade()
	{
		$ret = array();
		foreach ($this->findModules() as $name => $module) {
			if ($this->getStatus($module) === self::STATUS_FOR_UPGRADE) {
				$ret[$name] = $module;
			}
		}
		return $ret;
	}


	/**
	 * @param IModule $module
	 * @return string
	 */
	protected function getStatus(IModule $module)
	{
		return $this->modules[$module->getName()]['status'];
	}


	protected function formatClass($class)
	{
		return '\\' . trim($class, '\\');
	}


	protected function createInstanceOfModule($class, $path)
	{
		if (!class_exists($class)) {
			require_once $path . '/Module.php';
		}
		return new $class;
	}


	/**
	 * @return string
	 */
	protected function getModuleConfigPath()
	{
		return $this->configDir . '/settings.php';
	}


	/**
	 * @return array
	 */
	protected function loadModuleConfig()
	{
		$config = new PhpAdapter;
		return $config->load($this->getModuleConfigPath());
	}


	/**
	 * @param $data
	 */
	public function saveModuleConfig($data)
	{
		$config = new PhpAdapter;
		file_put_contents($this->getModuleConfigPath(), $config->dump($data));
	}
}

