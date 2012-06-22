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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleManager extends Object
{

	const RESOURCES_MODE_SYMLINK = 'symlink';
	const RESOURCES_MODE_COPY = 'copy';

	const MODULE_STATUS_INSTALLED = 'installed';
	const MODULE_STATUS_PAUSED = 'paused';

	/** @var array */
	protected $modules;

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
	public function __construct(Container $context, $modules, $moduleConfig, $resourcesMode, $resourcesDir)
	{
		$this->context = $context;
		$this->modules = $modules;
		$this->moduleConfig = $moduleConfig;
		$this->setResourcesMode($resourcesMode);
		$this->resourcesDir = $resourcesDir;
	}

	/**
	 * Return modules by status.
	 *
	 * @return array
	 */
	 public function getModules($status = NULL)
	 {
		 if (!$status) {
			 return $this->modules;
		 }

		 $ret = array();
		 foreach ($this->modules as $module => $item) {
			 if ($item['status'] == $status) {
				 $ret[$module] = $item;
			 }
		 }
		 return $ret;
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


	/**
	 * Factory for module
	 *
	 * @param string $name
	 * @return Venne\Module\IModule
	 */
	public function getModuleInstance($name)
	{
		$class = "\\" . ucfirst($name) . "Module\\Module";
		return new $class;
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

		// run installation
		$module->install($this->context);

		// create resources in public
		if (!file_exists($this->context->parameters['resourcesDir'] . "/{$name}Module")) {
			umask(0000);
			if ($this->resourcesMode == self::RESOURCES_MODE_SYMLINK) {
				@symlink("../../vendor/venne/{$name}-module/Resources/public", $this->resourcesDir . "/{$name}Module");
			} else {
				@copy($module->getPath() . "/Resources/public", $this->resourcesDir . "/{$name}Module");
			}
		}

		// enable module in config
		$config = new \Nette\Config\Adapters\NeonAdapter();
		$modules = $config->load($this->context->parameters["configDir"] . "/modules.neon");
		if (!array_search($name, $modules)) {
			$modules[$name] = array(
				'version' => $module->getVersion(),
				'status' => self::MODULE_STATUS_INSTALLED,
			);
		}
		file_put_contents($this->context->parameters["configDir"] . "/modules.neon", $config->dump($modules));

		// clean cache
		$this->cleanCaches();
	}


	public function uninstallModule($name)
	{
		$module = $this->getModuleInstance($name);

		// run uninstallation
		$module->uninstall($this->context);

		// remove resources in public
		if ($this->resourcesMode == self::RESOURCES_MODE_SYMLINK) {
			unlink($this->resourcesDir . "/{$name}Module");
		} else {
			\Venne\Utils\File::rmdir($this->resourcesDir . "/{$name}Module", true);
		}

		// remove module from config
		$config = new \Nette\Config\Adapters\NeonAdapter();
		$modules = $config->load($this->context->parameters["configDir"] . "/modules.neon");
		unset($modules[$name]);
		file_put_contents($this->context->parameters["configDir"] . "/modules.neon", $config->dump($modules));

		// cleanCache
		$this->cleanCaches();
	}


	/**
	 * @param bool $clean
	 */
	protected function cleanCaches()
	{
		$this->context->robotLoader->rebuild();
		$this->context->session->getSection("Venne.Security.Authorizator")->remove();
	}


	/**
	 * Find all modules which are located in project.
	 *
	 * @return array
	 */
	public function findAllModules()
	{
		$arr = array();
		foreach ($this->context->robotLoader->getIndexedClasses() as $key=>$class) {
			if(substr($key, strrpos($key, '\\') + 1) == 'Module'){
				$ref = \Nette\Reflection\ClassType::from($key);
				if($ref->isInstantiable()){
					$module = $ref->newInstance();
					$arr[$module->getName()] = $module;
				}
			}
		}

		return $arr;
	}

}

