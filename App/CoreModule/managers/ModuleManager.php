<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne;
use Nette\Object;
use Venne\Config\ConfigBuilder;
use Venne\DI\Container;

/**
 * @author Josef Kříž
 */
class ModuleManager extends Object {


	/** @var ConfigBuilder */
	protected $config;

	/** @var string */
	protected $section;

	/** @var Container */
	protected $context;



	public function __construct(Container $context, ConfigBuilder $config)
	{
		$this->context = $context;
		$this->config = $config;
	}



	/**
	 * @return string
	 */
	public function getSection()
	{
		return $this->section;
	}



	/**
	 * @param string $section 
	 */
	public function setSection($section)
	{
		$this->section = $section;
	}



	/**
	 * Factory for module
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
	 * @param string $name
	 * @return array[]string 
	 */
	public function getModulesByDependOn($name)
	{
		$ret = array();
		foreach ($this->context->modules->getModules() as $key => $module) {
			$dependencies = $this->getModuleDependencies($key);
			if (in_array($name, array_keys($dependencies))) {
				$ret[] = $key;
			}
		}
		return $ret;
	}



	/**
	 * Get module dependencies as array
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

					$ret[$module][] = array(
						"version" => substr($item, $pos + strlen($operator)),
						"operator" => $operator
					);
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
	 * Check is module exists
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

		$this->config[$this->section]["modules"][$name] = array(
			"run" => true,
			"version" => $module->getVersion()
		);
		$this->config->save();
		$module->install($this->context);

		$editForm = $module->getForm($this->config);
		$editForm->save();

		$this->cleanCaches($cleanCache);
	}



	public function uninstallModule($name, $withDependencies = null, $cleanCache = true)
	{
		$childrens = $this->getModulesByDependOn($name);
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

		unset($this->config[$this->section]["modules"][$name]);
		$module = $this->getModuleInstance($name);
		$module->uninstall($this->context);
		$this->config->save();

		$this->cleanCaches($cleanCache);
	}



	public function activateModule($name)
	{
		$this->config[$this->section]["modules"][$name]["run"] = true;
		$this->config->save();

		$this->cleanCaches();
	}



	public function deactivateModule($name)
	{
		$this->config[$this->section]["modules"][$name]["run"] = false;
		$this->config->save();

		$this->cleanCaches();
	}



	public function upgradeModule($name)
	{
		$class = "\\App\\" . ucfirst($name) . "Module\\Module";
		$module = new $class;
		$this->config[$this->section]["modules"][$name]["version"] = $module->getVersion();
		$this->config->save();

		$this->cleanCaches();
	}



	public function downgradeModule($name)
	{
		$class = "\\App\\" . ucfirst($name) . "Module\\Module";
		$module = new $class;
		$this->config[$this->section]["modules"][$name]["version"] = $module->getVersion();
		$this->config->save();

		$this->cleanCaches();
	}



	/**
	 * Is module installed
	 * @param string $name
	 * @param string $version
	 * @param string $operator
	 * @return bool
	 */
	public function isModuleInstalled($name, $version = null, $operator = null)
	{
		if (!isset($this->config[$this->section]["modules"][$name])) {
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

}

