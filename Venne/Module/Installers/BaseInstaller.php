<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\Installers;

use Venne;
use Nette\Object;
use Nette\DI\Container;
use Venne\Utils\File;
use Nette\Config\Adapters\NeonAdapter;
use Venne\Module\IModule;
use Venne\Module\IInstaller;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseInstaller extends Object implements IInstaller
{

	/** @var array */
	protected $actions = array();

	/** @var string */
	protected $resourcesDir;

	/** @var string */
	protected $configDir;


	/**
	 * @param \Nette\DI\Container $context
	 */
	public function __construct(Container $context)
	{
		$this->resourcesDir = $context->parameters['resourcesDir'];
		$this->configDir = $context->parameters['configDir'];
	}


	/**
	 * @param \Venne\Module\IModule $module
	 */
	public function install(IModule $module)
	{
		try {
			$name = $module->getName();
			$configuration = $module->getConfiguration();

			// create resources dir
			$resourcesDir = $this->resourcesDir;
			$moduleDir = $resourcesDir . "/{$name}Module";
			$targetDir = $module->getPath() . '/Resources/public';
			if (!file_exists($moduleDir) && file_exists($targetDir)) {
				umask(0000);
				@mkdir(dirname($moduleDir), 0777, true);
				if (symlink(File::getRelativePath(dirname($moduleDir), $targetDir), $moduleDir) === false) {
					File::copy($targetDir, $moduleDir);
				}

				$this->actions[] = function ($self) use ($resourcesDir) {
					if (is_link($resourcesDir)) {
						unlink($resourcesDir);
					} else {
						File::rmdir($resourcesDir, true);
					}
				};
			}

			// update main config.neon
			if (count($configuration) > 0) {
				$orig = $data = $this->loadConfig();
				$data = array_merge_recursive($data, $configuration);
				$this->saveConfig($data);

				$this->actions[] = function ($self) use ($orig) {
					$self->saveConfig($orig);
				};
			}
		} catch (\Exception $e) {
			$actions = array_reverse($this->actions);

			try {
				foreach ($actions as $action) {
					$action($this);
				}
			} catch (\Exception $ex) {
				echo $ex->getMessage();
			}

			throw $e;
		}
	}


	/**
	 * @param \Venne\Module\IModule $module
	 */
	public function uninstall(IModule $module)
	{
		$name = $module->getName();
		$configuration = $module->getConfiguration();

		// update main config.neon
		if (count($configuration) > 0) {
			$orig = $data = $this->loadConfig();
			$data = $this->getRecursiveDiff($data, $configuration);
			$this->saveConfig($data);

			$this->actions[] = function ($self) use ($orig) {
				$self->saveConfig($orig);
			};
		}

		// remove resources dir
		$resourcesDir = $this->resourcesDir . "/{$name}Module";
		if (file_exists($resourcesDir)) {
			if (is_link($resourcesDir)) {
				unlink($resourcesDir);
			} else {
				File::rmdir($resourcesDir, true);
			}
		}
	}


	/**
	 * @param \Venne\Module\IModule $module
	 * @param $from
	 * @param $to
	 */
	public function upgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @param \Venne\Module\IModule $module
	 * @param $from
	 * @param $to
	 */
	public function downgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @param array $arr1
	 * @param array $arr2
	 * @return array
	 */
	protected function getRecursiveDiff($arr1, $arr2)
	{
		foreach ($arr1 as $key => $item) {
			if (!is_array($arr1[$key])) {

				// if key is numeric, remove the same value
				if (is_numeric($key)) {
					if (($pos = array_search($arr1[$key], $arr2)) !== false) {
						unset($arr1[$key]);
					}
				} // else remove the same key
				else {
					if (isset($arr2[$key])) {
						unset($arr1[$key]);
					}
				}
			} elseif (isset($arr2[$key])) {
				$arr1[$key] = $this->getRecursiveDiff($arr1[$key], $arr2[$key]);
			}
		}

		foreach ($arr1 as $key => $item) {
			if (is_array($item) && count($item) === 0) {
				unset($arr1[$key]);
			}
		}

		return $arr1;
	}


	/**
	 * @return string
	 */
	protected function getConfigPath()
	{
		return $this->configDir . '/config.neon';
	}


	/**
	 * @return array
	 */
	protected function loadConfig()
	{
		$config = new NeonAdapter();
		return $config->load($this->getConfigPath());
	}


	/**
	 * @param $data
	 */
	public function saveConfig($data)
	{
		$config = new NeonAdapter();
		file_put_contents($this->getConfigPath(), $config->dump($data));
	}
}

