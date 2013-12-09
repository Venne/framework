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

use Exception;
use Nette\Config\Adapters\PhpAdapter;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Finder;
use Nette\Utils\LimitedScope;
use Venne\Caching\CacheManager;
use Venne\Module\DependencyResolver\Problem;
use Venne\Module\DependencyResolver\Solver;

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

	const MODULE_REQUIRE = 'require';

	const STATUS_UNINSTALLED = 'uninstalled';

	const STATUS_INSTALLED = 'installed';

	const STATUS_UNREGISTERED = 'unregistered';

	const ACTION_INSTALL = 'install';

	const ACTION_UNINSTALL = 'uninstall';

	const ACTION_UPGRADE = 'upgrade';

	const ACTION_NONE = '';

	/** @var array */
	public $onInstall;

	/** @var array */
	public $onUninstall;

	/** @var array */
	public $onUpgrade;

	/** @var array */
	public $onRegister;

	/** @var array */
	public $onUnregister;

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

	/** @var array */
	protected static $moduleFiles = array(
		'Module.php',
		'.Venne.php',
	);

	/** @var Container|\SystemContainer */
	protected $context;

	/** @var CacheManager */
	protected $cacheManager;

	/** @var string */
	protected $libsDir;

	/** @var string */
	protected $configDir;

	/** @var string */
	protected $modulesDir;

	/** @var array */
	protected $modules;

	/** @var IModule[] */
	protected $_findModules;

	/** @var IModule[] */
	protected $_modules;

	/** @var int */
	protected $_systemContainer = 1;


	/**
	 * @param Container $context
	 * @param CacheManager $cacheManager
	 * @param $libsDir
	 * @param $configDir
	 * @param $modulesDir
	 */
	public function __construct(Container $context, CacheManager $cacheManager, $libsDir, $configDir, $modulesDir)
	{
		$this->context = $context;
		$this->cacheManager = $cacheManager;
		$this->libsDir = $libsDir;
		$this->configDir = $configDir;
		$this->modulesDir = $modulesDir;

		$this->reloadInfo();
	}


	/**
	 * Reload info.
	 */
	protected function reloadInfo()
	{
		$data = $this->loadModuleConfig();
		$this->modules = $data['modules'];
		$this->_findModules = NULL;
		$this->_modules = NULL;
	}


	/**
	 * Reload system container.
	 */
	protected function reloadSystemContainer()
	{
		/** @var $configurator Configurator */
		$configurator = $this->context->configurator;
		$class = $this->context->parameters['container']['class'] . $this->_systemContainer++;
		LimitedScope::evaluate($configurator->buildContainer($dependencies, $class));

		/** @var context Container */
		$this->context = new $class;
		$this->context->parameters = (include $this->configDir . '/settings.php') + $this->context->parameters;
		$this->context->initialize();
		$this->context->addService("configurator", $configurator);
	}


	/**
	 * Create instance of module.
	 *
	 * @param $name
	 * @return IModule
	 */
	public function createInstance($name)
	{
		if (isset($this->modules[$name])) {
			$class = $this->modules[$name][self::MODULE_CLASS];
			if (!class_exists($class)) {
				$path = $this->context->expand($this->modules[$name][self::MODULE_PATH]);
				require_once $path . '/Module.php';
			}
			return new $class;
		}

		$modules = $this->findModules();
		if (isset($modules[$name])) {
			return $modules[$name];
		}

		throw new InvalidArgumentException("Module '{$name}' does not exist.");
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

		$this->reloadInfo();
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
	 * @throws InvalidArgumentException
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
	 * @throws InvalidArgumentException
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
	 * @param $action
	 * @param IModule $module
	 * @param bool $withDependencies
	 * @return mixed
	 */
	public function doAction($action, IModule $module, $withDependencies = FALSE)
	{
		return $this->{$action}($module, $withDependencies);
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
				self::MODULE_PATH => $this->getFormattedPath($module->getPath()),
				self::MODULE_AUTOLOAD => $this->getFormattedPath($module->getAutoload()),
				self::MODULE_REQUIRE => $module->getRequire(),
			);
		}
		$this->saveModuleConfig($modules);

		$this->reloadInfo();
		$this->onRegister($this, $module);
	}


	/**
	 * Unregistration of module.
	 *
	 * @param $name
	 */
	public function unregister($name)
	{
		if (!isset($this->modules[$name])) {
			throw new InvalidArgumentException("Module '{$name}' is already unregistered");
		}

		$modules = $this->loadModuleConfig();
		unset($modules['modules'][$name]);
		$this->saveModuleConfig($modules);

		$this->reloadInfo();
		$this->onUnregister($this, $name);
	}


	/**
	 * Installation of module.
	 *
	 * @param IModule $module
	 */
	public function install(IModule $module, $force = FALSE)
	{
		if ($this->getStatus($module) === self::STATUS_INSTALLED) {
			throw new InvalidArgumentException("Module '{$module->getName()}' is already installed");
		}

		if (!$force) {
			$dependencyResolver = $this->createSolver();
			$dependencyResolver->testInstall($module);
		}

		foreach ($module->getInstallers() as $class) {

			$this->reloadSystemContainer();

			try {
				$installer = $this->context->createInstance($class);
				$installer->install($module);
			} catch (Exception $e) {
				foreach ($module->getInstallers() as $class2) {
					if ($class === $class2) {
						break;
					}

					$installer = $this->context->createInstance($class2);
					$installer->uninstall($module);
				}

				throw $e;
			}
		}

		$modules = $this->loadModuleConfig();
		$modules['modules'][$module->getName()] = array(
			self::MODULE_STATUS => self::STATUS_INSTALLED,
			self::MODULE_ACTION => self::ACTION_NONE,
			self::MODULE_CLASS => $module->getClassName(),
			self::MODULE_VERSION => $module->getVersion(),
			self::MODULE_PATH => $this->getFormattedPath($module->getPath()),
			self::MODULE_AUTOLOAD => $this->getFormattedPath($module->getAutoload()),
			self::MODULE_REQUIRE => $module->getRequire(),
		);
		$this->saveModuleConfig($modules);

		$this->reloadInfo();
		$this->reloadSystemContainer();
		$this->cacheManager->clean();
		$this->onInstall($this, $module);
	}


	/**
	 * Uninstallation of module.
	 *
	 * @param IModule $module
	 */
	public function uninstall(IModule $module, $force = FALSE)
	{
		if ($this->getStatus($module) === self::STATUS_UNINSTALLED) {
			throw new InvalidArgumentException("Module '{$module->getName()}' is already uninstalled");
		}

		if (!$force) {
			$dependencyResolver = $this->createSolver();
			$dependencyResolver->testUninstall($module);
		}

		foreach ($module->getInstallers() as $class) {

			$this->reloadSystemContainer();

			try {
				$installer = $this->context->createInstance($class);
				$installer->uninstall($module);
			} catch (Exception $e) {
				foreach ($module->getInstallers() as $class2) {
					if ($class === $class2) {
						break;
					}

					$installer = $this->context->createInstance($class2);
					$installer->install($module);
				}

				throw $e;
			}
		}

		$this->setAction($module, self::ACTION_NONE);
		$this->setStatus($module, self::STATUS_UNINSTALLED);

		$this->reloadInfo();
		$this->reloadSystemContainer();
		$this->cacheManager->clean();
		$this->onUninstall($this, $module);
	}


	/**
	 * Upgrade module.
	 *
	 * @param IModule $module
	 * @param bool $withDependencies
	 */
	public function upgrade(IModule $module, $force = FALSE)
	{
		if ($this->getStatus($module) !== self::STATUS_INSTALLED) {
			throw new InvalidArgumentException("Module '{$module->getName()}' must be installed");
		}

		$modules = $this->loadModuleConfig();
		if ($module->getVersion() === $modules['modules'][$module->getName()][self::MODULE_VERSION]) {
			throw new InvalidArgumentException("Module '{$module->getName()}' is current");
		}

		if (!$force) {
			$dependencyResolver = $this->createSolver();
			$dependencyResolver->testUpgrade($module);
		}

		foreach ($module->getInstallers() as $class) {
			try {
				/** @var $installer IInstaller */
				$installer = $this->context->createInstance($class);
				$installer->upgrade($module, $this->modules[$module->getName()][self::MODULE_VERSION], $module->getVersion());
			} catch (Exception $e) {
				foreach ($module->getInstallers() as $class2) {
					if ($class === $class2) {
						break;
					}

					$installer = $this->context->createInstance($class2);
					$installer->downgrade($module, $module->getVersion(), $this->modules[$module->getName()][self::MODULE_VERSION]);
				}

				throw $e;
			}
		}

		$modules['modules'][$module->getName()] = array(
			self::MODULE_STATUS => self::STATUS_INSTALLED,
			self::MODULE_ACTION => self::ACTION_NONE,
			self::MODULE_CLASS => $module->getClassName(),
			self::MODULE_VERSION => $module->getVersion(),
			self::MODULE_PATH => $this->getFormattedPath($module->getPath()),
			self::MODULE_AUTOLOAD => $this->getFormattedPath($module->getAutoload()),
			self::MODULE_REQUIRE => $module->getRequire(),
		);

		$this->saveModuleConfig($modules);

		$this->reloadInfo();
		$this->reloadSystemContainer();
		$this->cacheManager->clean();
		$this->onUpgrade($this, $module);
	}


	/**
	 * @param IModule $module
	 * @return DependencyResolver\Problem
	 */
	public function testInstall(IModule $module)
	{
		$problem = new Problem;
		$dependencyResolver = $this->createSolver();
		$dependencyResolver->testInstall($module, $problem);
		return $problem;
	}


	/**
	 * @param IModule $module
	 * @return DependencyResolver\Problem
	 */
	public function testUninstall(IModule $module)
	{
		$problem = new Problem;
		$dependencyResolver = $this->createSolver();
		$dependencyResolver->testUninstall($module, $problem);
		return $problem;
	}


	/**
	 * @param IModule $module
	 * @return DependencyResolver\Problem
	 */
	public function testUpgrade(IModule $module)
	{
		$problem = new Problem;
		$dependencyResolver = $this->createSolver();
		$dependencyResolver->testUpgrade($module, $problem);
		return $problem;
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
					foreach (self::$moduleFiles as $moduleFile) {
						foreach (Finder::findFiles($moduleFile)->in($dir2) as $file) {
							$this->findModulesClosure($file);
						}
					}
				}
			}

			foreach (Finder::findDirectories('*')->in($this->modulesDir) as $dir2) {
				foreach (self::$moduleFiles as $moduleFile) {
					foreach (Finder::findFiles($moduleFile)->in($dir2) as $file) {
						$this->findModulesClosure($file);
					}
				}
			}
		}

		return $this->_findModules;
	}


	private function findModulesClosure($file)
	{
		$class = $this->getModuleClassByFile($file->getPathname());
		$module = $this->createInstanceOfModule($class, dirname($file->getPathname()));
		$this->_findModules[$module->getName()] = $module;
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
				$path = $this->context->expand($args[self::MODULE_PATH]);
				if (file_exists($path)) {
					$this->_modules[$name] = $this->createInstanceOfModule($args[self::MODULE_CLASS], $path);
				}
			}
		}

		return $this->_modules;
	}


	/**
	 * @param IModules[] $modules
	 * @param IModule $module
	 * @throws InvalidArgumentException
	 */
	public function matchModulesWithModule($modules, IModule $module)
	{
		foreach ($modules as $sourceModule) {
			foreach ($sourceModule->getRequire() as $name => $require) {
				if ($name !== $module->getName()) {
					continue;
				}

				$requires = VersionHelpers::normalizeRequire($require);
				foreach ($requires as $items) {
					foreach ($items as $operator => $version) {
						if (!version_compare($module->getVersion(), $version, $operator)) {
							throw new InvalidArgumentException("Module '{$sourceModule->getName()}' depend on '{$module->getName()}' with version '{$require}'. Current version is '{$module->getVersion()}'.");
						}
					}
				}
			}
		}
	}


	/**
	 * @return IModule[]
	 */
	protected function getModulesForRegister()
	{
		$activeModules = $this->getModules();
		$modules = $this->findModules();
		$diff = array_diff(array_keys($modules), array_keys($activeModules));

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
			$path = $this->context->expand($args[self::MODULE_PATH]);
			if (!file_exists($path)) {
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
	 * @throws InvalidArgumentException
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
	 * @throws InvalidArgumentException
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
		return '\\' . ltrim($class, '\\');
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
	protected function saveModuleConfig($data)
	{
		$config = new PhpAdapter;
		file_put_contents($this->getModuleConfigPath(), $config->dump($data));
	}


	/**
	 * @param $file
	 * @return string
	 * @throws InvalidArgumentException
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
	 * @throws InvalidArgumentException
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
		$dlm = FALSE;
		for ($i = 2; $i < $count; $i++) {
			if ((isset($tokens[$i - 2][1]) && ($tokens[$i - 2][1] == "phpnamespace" || $tokens[$i - 2][1] == "namespace")) ||
				($dlm && $tokens[$i - 1][0] == T_NS_SEPARATOR && $tokens[$i][0] == T_STRING)
			) {
				if (!$dlm) $namespace = 0;
				if (isset($tokens[$i][1])) {
					$namespace = $namespace ? $namespace . "\\" . $tokens[$i][1] : $tokens[$i][1];
					$dlm = TRUE;
				}
			} elseif ($dlm && ($tokens[$i][0] != T_NS_SEPARATOR) && ($tokens[$i][0] != T_STRING)) {
				$dlm = FALSE;
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


	/**
	 * @return Solver
	 */
	protected function createSolver()
	{
		$config = $this->loadModuleConfig();
		return new Solver($this->getModules(), $this->getModulesByStatus(self::STATUS_INSTALLED), $config['modules'], $this->libsDir, $this->modulesDir);
	}


	/**
	 * @param $path
	 * @return string
	 */
	private function getFormattedPath($path)
	{
		$path = str_replace('\\', '/', $path);
		$libsDir = str_replace('\\', '/', $this->libsDir);
		$modulesDir = str_replace('\\', '/', $this->modulesDir);

		$tr = array(
			$libsDir => '%libsDir%',
			$modulesDir => '%modulesDir%',
		);

		return str_replace(array_keys($tr), array_merge($tr), $path);
	}
}

