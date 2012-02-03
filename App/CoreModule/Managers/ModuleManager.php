<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Managers;

use Venne;
use Nette\Object;
use Venne\Config\ConfigBuilder;
use Nette\DI\Container;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleManager extends Object {


	/** @var ConfigBuilder */
	protected $config;

	/** @var Container */
	protected $context;

	/** @var array */
	protected $_moduleUpdates;

	/** @var array */
	protected $_modules;



	public function __construct(Container $context, ConfigBuilder $config)
	{
		$this->context = $context;
		$this->config = $config;
	}



	/**
	 * Factory for module
	 *
	 * @param string $name
	 * @return Venne\Module\IModule
	 */
	public function getModuleInstance($name)
	{
		$class = "\\App\\" . ucfirst($name) . "Module\\Module";
		return new $class;
	}



	/**
	 * Get modules by depend on module
	 *
	 * @param string $name
	 * @return array[]string
	 */
	public function getActivatedModulesByDependOn($name)
	{
		$ret = array();
		foreach ($this->getActivatedModules() as $key => $module) {
			$dependencies = $this->getModuleDependencies($key);
			if (in_array($name, array_keys($dependencies))) {
				$ret[] = $key;
			}
		}
		return $ret;
	}



	/**
	 * Get module dependencies as array
	 *
	 * @param string $name
	 * @param bool $recursively
	 * @return array
	 */
	public function getModuleDependencies($name, $recursively = true)
	{
		$operators = array(">=", "<=", ">", "<", "=");
		$module = $this->getModuleInstance($name);
		$ret = array();
		foreach ($module->getDependencies() as $item) {
			foreach ($operators as $operator) {
				if (strpos($item, $operator) !== false) {
					$pos = strpos($item, $operator);
					$module = substr($item, 0, $pos);

					if (!isset($ret[$module])) {
						$ret[$module] = array();
					}

					$ret[$module][] = array("version" => substr($item, $pos + strlen($operator)), "operator" => $operator);
					break;
				}
			}
			if ($recursively) {
				$ret += $this->getModuleDependencies($module, $recursively);
			}
		}
		$ret = array_reverse($ret, true);
		return $ret;
	}



	/**
	 * Install array of modules
	 *
	 * @param array[]string $modules
	 * @param bool $checkDependencies
	 */
	public function installModules($modules, $checkDependencies = true)
	{
		foreach ($modules as $module) {
			$this->installModule($module);
		}
	}



	/**
	 * Check if module exists
	 *
	 * @param string $name
	 * @param string $version
	 * @param string $operator
	 * @return bool
	 */
	public function checkModule($name, $version = null, $operator = Null)
	{
		$class = "\\App\\" . ucfirst($name) . "Module\\Module";
		if (!class_exists($class)) {
			return false;
		}

		if ($version XOR $operator) {
			throw new \Nette\InvalidArgumentException;
		}

		if ($version) {
			$class = $this->getModuleInstance($name);
			if (!version_compare($class->getVersion(), $version, $operator)) {
				return false;
			}
		}
		return true;
	}



	/**
	 * Install module
	 *
	 * @param string $name
	 * @param bool $checkDependencies
	 * @return bool
	 */
	public function installModule($name, $withDependencies = null, $cleanCache = true)
	{
		$module = $this->getModuleInstance($name);
		$dependencies = $this->getModuleDependencies($name, false);

		if (!$withDependencies && count($dependencies) > 0) {
			$ex = new DependencyException("Module depend on modules");
			$ex->dependencies = $this->getModuleDependencies($name);
			throw $ex;
		}

		// check modules
		if ($withDependencies && count($dependencies) > 0) {
			foreach ($dependencies as $moduleName => $item2) {
				foreach ($item2 as $item) {
					if (!$this->isModuleInstalled($moduleName, $item["version"], $item["operator"])) {
						if ($this->checkModule($moduleName)) {
							if (!$this->checkModule($moduleName, $item["version"], $item["operator"])) {
								$ex = new DependencyNotExistsException("Bad version of " . $moduleName . " module. Needed: " . $item["operator"] . $item["version"] . ", is: " . $this->getModuleInstance($moduleName)->getVersion());
								$ex->dependencies = $dependencies;
								throw $ex;
							}
						} else {
							$ex = new DependencyNotExistsException("Module " . $moduleName . " not exists");
							$ex->dependencies = $dependencies;
							throw $ex;
						}
					}
				}
			}
		}

		if ($withDependencies && count($dependencies) > 0) {
			foreach ($dependencies as $moduleName => $item2) {
				foreach ($item2 as $item) {
					if (!$this->isModuleInstalled($moduleName, $item["version"], $item["operator"])) {
						if ($this->checkModule($moduleName)) {
							if ($this->checkModule($moduleName, $item["version"], $item["operator"])) {
								$this->installModule($moduleName, $withDependencies, false);
							}
						}
					}
				}
			}
		}

		$config = array("run" => true, "version" => $module->getVersion());
		$config += $this->getValuesFromContainer($module->getForm($this->context)->getComponents());

		$this->config["parameters"]["modules"][$name] = \Nette\ArrayHash::from($config, true);

		$this->config->save();

		$module->install($this->context);

		touch($this->context->parameters["flagsDir"] . "/updated");
		$this->cleanCaches($cleanCache);
	}



	protected function getValuesFromContainer($container)
	{
		$ret = array();
		foreach ($container as $key => $component) {
			if (!Strings::startsWith($key, "_")) {
				if ($component instanceof \Nette\Forms\Container) {
					$ret[$key] = $this->getValuesFromContainer($component->getComponents());
				} else if ($component instanceof \Nette\Forms\IControl) {
					$ret[$key] = $component->value;
				}
			}
		}
		return $ret;
	}



	public function uninstallModule($name, $withDependencies = null, $cleanCache = true)
	{
		$childrens = $this->getActivatedModulesByDependOn($name);
		if (count($childrens) > 0) {
			if ($withDependencies) {
				foreach ($childrens as $children) {
					$this->uninstallModule($children, $withDependencies, false);
				}
			} else {
				$ex = new DependencyException("Some modules are depending on this module");
				$ex->dependencies = $childrens;
				throw $ex;
			}
		}

		unset($this->config["parameters"]["modules"][$name]);
		$module = $this->getModuleInstance($name);
		$module->uninstall($this->context);
		$this->config->save();

		touch($this->context->parameters["flagsDir"] . "/updated");
		$this->cleanCaches($cleanCache);
	}



	public function upgradeModule($name)
	{
		$class = "\\App\\" . ucfirst($name) . "Module\\Module";
		$module = new $class;
		$this->config["parameters"]["modules"][$name]["version"] = $module->getVersion();
		$this->config->save();

		$this->cleanCaches();
	}



	public function downgradeModule($name)
	{
		$class = "\\App\\" . ucfirst($name) . "Module\\Module";
		$module = new $class;
		$this->config["parameters"]["modules"][$name]["version"] = $module->getVersion();
		$this->config->save();

		$this->cleanCaches();
	}



	/**
	 * Is module installed
	 *
	 * @param string $name
	 * @param string $version
	 * @param string $operator
	 * @return bool
	 */
	public function isModuleInstalled($name, $version = null, $operator = null)
	{
		if (!isset($this->config["parameters"]["modules"][$name])) {
			return false;
		}

		if ($version XOR $operator) {
			throw new \Nette\InvalidArgumentException;
		}

		if ($version) {
			$class = $this->getModuleInstance($name);
			if (!version_compare($class->getVersion(), $version, $operator)) {
				return false;
			}
		}
		return true;
	}



	/**
	 * @param bool $clean
	 */
	protected function cleanCaches($clean = true)
	{
		if ($clean) {
			$this->context->robotLoader->rebuild();
			$this->context->session->getSection("Venne.Security.Authorizator")->remove();
		}
	}



	/**
	 * @return bool
	 */
	public function checkModuleUpgrades()
	{
		if (!$this->_moduleUpdates) {
			$this->_moduleUpdates = false;
			foreach ($this->getActivatedModules() as $module => $item) {
				if (!version_compare($this->context->{$module . "Module"}->getVersion(), $item["version"], '==')) {
					$this->_moduleUpdates = true;
					break;
				}
			}
		}
		return $this->_moduleUpdates;
	}



	public function getActivatedModules()
	{
		if (!$this->_modules) {
			$this->_modules = array();
			foreach ($this->context->parameters['modules'] as $name => $item) {
				if (isset($item["run"]) && $item["run"]) {
					$this->_modules[$name] = $item;
				}
			}
		}
		return $this->_modules;
	}


}

