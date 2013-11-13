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

use Nette\Config\Adapters\NeonAdapter;
use Nette\DI\Container;
use Nette\Object;
use Nette\Utils\Validators;
use Venne\Module\IInstaller;
use Venne\Module\IModule;
use Venne\Utils\File;

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
			$targetDir = new \SplFileInfo($module->getPath() . $module->getRelativePublicPath());
			$targetDir = $targetDir->getRealPath();
			if (!file_exists($moduleDir) && file_exists($targetDir)) {
				umask(0000);
				@mkdir(dirname($moduleDir), 0777, TRUE);
				if (!@symlink(File::getRelativePath(dirname($moduleDir), $targetDir), $moduleDir) && !file_exists($moduleDir)) {
					File::copy($targetDir, $moduleDir);
				}

				$this->actions[] = function () use ($resourcesDir) {
					if (is_link($resourcesDir)) {
						unlink($resourcesDir);
					} else {
						File::rmdir($resourcesDir, TRUE);
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

			// remove extension parameters
			$configuration = $module->getConfiguration();
			if (isset($configuration['extensions'])) {
				foreach ($configuration['extensions'] as $key => $values) {
					if (isset($data[$key])) {
						unset($data[$key]);
					}
				}
			}

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
				File::rmdir($resourcesDir, TRUE);
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
		$isList = Validators::isList($arr1);
		$arr2IsList = Validators::isList($arr2);

		foreach ($arr1 as $key => $item) {
			if (!is_array($arr1[$key])) {

				// if key is numeric, remove the same value
				if (is_numeric($key) && ($pos = array_search($arr1[$key], $arr2)) !== FALSE) {
					unset($arr1[$key]);
				} //

				// else remove the same key
				else if ((!$isList && isset($arr2[$key])) || ($isList && $arr2IsList && array_search($item, $arr2) !== FALSE)) {
					unset($arr1[$key]);
				} //

			} elseif (isset($arr2[$key])) {
				$arr1[$key] = $item = $this->getRecursiveDiff($arr1[$key], $arr2[$key]);

				if (is_array($item) && count($item) === 0) {
					unset($arr1[$key]);
				}
			}
		}

		if ($isList) {
			$arr1 = array_merge($arr1);
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

