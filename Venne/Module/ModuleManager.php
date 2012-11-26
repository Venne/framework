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
use Nette\InvalidArgumentException;
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

	const MODULE_ACTION = 'action';

	const MODULE_VERSION = 'version';

	const MODULE_AUTOLOAD = 'autoload';

	const STATUS_UNINSTALLED = 'uninstalled';

	const STATUS_INSTALLED = 'installed';

	const STATUS_UNREGISTERED = 'unregistered';

	const ACTION_INSTALL = 'install';

	const ACTION_UNINSTALL = 'uninstall';

	const ACTION_UPGRADE = 'upgrade';

	const ACTION_NONE = '';

	/** @var array */
	protected static $statuses = array(
		self::STATUS_INSTALLED => 'Installed',
		self::STATUS_UNINSTALLED => 'Uninstalled',
		self::STATUS_UNREGISTERED => 'Unregistered',
	);

	/** @var array */
	protected static $actions = array(
		self::ACTION_NONE => '',
		self::ACTION_INSTALL => 'Install',
		self::ACTION_UNINSTALL => 'Uninstall',
		self::ACTION_UPGRADE => 'Upgrade',
	);

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
	}


	/**
	 * Create instance of module.
	 *
	 * @param $name
	 * @return IModule
	 */
	public function createInstance($name)
	{
		if (!isset($this->modules[$name])) {
			throw new InvalidArgumentException("Module '{$name}' does not exist.");
		}

		$class = $this->modules[$name][self::MODULE_CLASS];
		if (!class_exists($class)) {
			require_once $this->modules[$name][self::MODULE_PATH] . '/Module.php';
		}
		return new $class;
	}


	/**
	 * Do all actions
	 */
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
	 * Get module status
	 *
	 * @param IModule $module
	 * @return string
	 */
	public function getStatus(IModule $module)
	{
		if (!isset($this->modules[$module->getName()])) {
			return self::STATUS_UNREGISTERED;
		}

		return $this->modules[$module->getName()][self::MODULE_STATUS];
	}


	/**
	 * Set module status
	 *
	 * @param IModule $module
	 * @param $status
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setStatus(IModule $module, $status)
	{
		if (!isset(self::$statuses[$status])) {
			throw new InvalidArgumentException("Status '{$status}' not exists.");
		}

		if ($status === self::STATUS_UNREGISTERED) {
			throw new InvalidArgumentException("Cannot set status '{$status}'.");
		}

		$modules = $this->loadModuleConfig();
		$modules['modules'][$module->getName()][self::MODULE_STATUS] = $status;
		$this->saveModuleConfig($modules);
	}


	/**
	 * Get module action
	 *
	 * @param IModule $module
	 * @return string
	 */
	public function getAction(IModule $module)
	{
		return $this->modules[$module->getName()][self::MODULE_ACTION];
	}


	/**
	 * Set module action
	 *
	 * @param IModule $module
	 * @param $status
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setAction(IModule $module, $action)
	{
		if (!isset(self::$actions[$action])) {
			throw new InvalidArgumentException("Action '{$action}' not exists");
		}

		$modules = $this->loadModuleConfig();
		$modules['modules'][$module->getName()][self::MODULE_ACTION] = $action;
		$this->saveModuleConfig($modules);
	}


	/**
	 * Registration of module.
	 *
	 * @param IModule $module
	 */
	public function register(IModule $module)
	{
		if ($this->getStatus($module) !== self::STATUS_UNREGISTERED) {
			throw new InvalidArgumentException("Module '{$module->getName()}' is already registered");
		}

		$modules = $this->loadModuleConfig();
		if (!array_search($module->getName(), $modules['modules'])) {
			$modules['modules'][$module->getName()] = array(
				self::MODULE_STATUS => self::STATUS_UNINSTALLED,
				self::MODULE_ACTION => self::ACTION_NONE,
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
	 * Unregistration of module.
	 *
	 * @param $name
	 */
	public function unregister($name)
	{
		if ($this->getStatus($module) === self::STATUS_UNREGISTERED) {
			throw new InvalidArgumentException("Module '{$module->getName()}' is already unregistered");
		}

		$modules = $this->loadModuleConfig();
		unset($modules['modules'][$name]);
		$this->saveModuleConfig($modules);
		$this->modules = $modules['modules'];
	}


	/**
	 * Installation of module.
	 *
	 * @param IModule $module
	 */
	public function install(IModule $module)
	{
		if ($this->getStatus($module) === self::STATUS_INSTALLED) {
			throw new InvalidArgumentException("Module '{$module->getName()}' is already installed");
		}

		foreach ($module->getInstallers() as $class) {
			$installer = $this->context->createInstance($class);
			$installer->install($module);
		}

		$this->setAction($module, self::ACTION_NONE);
		$this->setStatus($module, self::STATUS_INSTALLED);
	}


	/**
	 * Uninstallation of module.
	 *
	 * @param IModule $module
	 */
	public function uninstall(IModule $module)
	{
		if ($this->getStatus($module) === self::STATUS_UNINSTALLED) {
			throw new InvalidArgumentException("Module '{$module->getName()}' is already uninstalled");
		}

		foreach ($module->getInstallers() as $class) {
			$installer = $this->context->createInstance($class);
			$installer->uninstall($module);
		}

		$this->setAction($module, self::ACTION_NONE);
		$this->setStatus($module, self::STATUS_UNINSTALLED);
	}


	/**
	 * @param IModule $module
	 */
	public function upgrade(IModule $module)
	{
//		foreach ($module->getInstallers() as $class) {
//			$installer = $this->context->createInstance($class);
//			$installer->upgrade($module);
//		}

		$this->setAction($module, self::ACTION_NONE);
		$this->setStatus($module, self::STATUS_INSTALLED);
	}


	/**
	 * Find all modules in filesystem.
	 *
	 * @return IModule[]
	 */
	public function findModules()
	{
		if ($this->_findModules === NULL) {
			$this->_findModules = array();

			foreach (Finder::findDirectories('*')->in($this->libsDir) as $dir) {
				foreach (Finder::findDirectories('*')->in($dir) as $dir2) {
					foreach (Finder::findFiles('Module.php')->in($dir2) as $file) {
						$class = $this->getModuleClassByFile($file->getPathname());
						$module = $this->createInstanceOfModule($class, dirname($file->getPathname()));
						$this->_findModules[$module->getName()] = $module;
					}
				}
			}
		}

		return $this->_findModules;
	}


	/**
	 * Get activated modules.
	 *
	 * @return IModule[]
	 */
	public function getModules()
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
		return $this->getModulesByAction(self::ACTION_INSTALL);
	}


	/**
	 * @return IModule[]
	 */
	protected function getModulesForUninstall()
	{
		return $this->getModulesByAction(self::ACTION_UNINSTALL);
	}


	/**
	 * @return IModule[]
	 */
	protected function getModulesForUpgrade()
	{
		return $this->getModulesByAction(self::ACTION_UPGRADE);
	}


	/**
	 * Get modules by action.
	 *
	 * @param $action
	 * @return IModule[]
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function getModulesByAction($action)
	{
		if (!isset(self::$actions[$action])) {
			throw new InvalidArgumentException("Action '{$action}' not exists");
		}

		$ret = array();
		foreach ($this->findModules() as $name => $module) {
			if ($this->getAction($module) === $action) {
				$ret[$name] = $module;
			}
		}
		return $ret;
	}


	/**
	 * Get modules by status.
	 *
	 * @param $action
	 * @return IModule[]
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function getModulesByStatus($status)
	{
		if (!isset(self::$statuses[$status])) {
			throw new InvalidArgumentException("Status '{$status}' not exists.");
		}

		$ret = array();
		foreach ($this->findModules() as $name => $module) {
			if ($this->getStatus($module) === $status) {
				$ret[$name] = $module;
			}
		}
		return $ret;
	}


	/**
	 * @param $class
	 * @return string
	 */
	protected function formatClass($class)
	{
		return '\\' . trim($class, '\\');
	}


	/**
	 * @param $class
	 * @param $path
	 * @return IModule
	 */
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


	/**
	 * @param $file
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function getModuleClassByFile($file)
	{
		$classes = $this->getClassesFromFile($file);

		if (count($classes) !== 1) {
			throw new InvalidArgumentException("File '{$file}' must contain only one class.");
		}

		return $classes[0];
	}


	/**
	 * @param $file
	 * @return array
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function getClassesFromFile($file)
	{
		if (!file_exists($file)) {
			throw new InvalidArgumentException("File '{$file}' does not exist.");
		}

		$classes = array();

		$namespace = 0;
		$tokens = token_get_all(file_get_contents($file));
		$count = count($tokens);
		$dlm = false;
		for ($i = 2; $i < $count; $i++) {
			if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] == "phpnamespace" || $tokens[$i - 2][1] == "namespace")) ||
				($dlm && $tokens[$i - 1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING)
			) {
				if (!$dlm) $namespace = 0;
				if (isset($tokens[$i][1])) {
					$namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
					$dlm = true;
				}
			} elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING)) {
				$dlm = false;
			}
			if (($tokens[$i - 2][0] == T_CLASS || (isset($tokens[$i - 2][1]) && $tokens[$i - 2][1] == "phpclass"))
				&& $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING
			) {
				$class_name = $tokens[$i][1];
				$classes[] = $namespace . '\\' . $class_name;
			}
		}
		return $classes;
	}
}

