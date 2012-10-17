<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\Composer\Installers;

use Venne;
use Venne\Module\ModuleManager;
use Venne\Config\Configurator;
use Venne\Utils\File;
use Nette\DI\Container;
use Nette\Config\Adapters\NeonAdapter;
use Nette\Config\Adapters\PhpAdapter;
use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Autoload\AutoloadGenerator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleInstaller extends LibraryInstaller
{


	/** @var Container|\SystemContainer */
	protected $container;

	/** @var Container|\SystemContainer */
	protected $_container;

	/** @var string */
	protected $rootDir;

	/** @var \Composer\Repository\RepositoryInterface */
	protected $repo;

	/** @var array */
	protected $actions = array();


	/**
	 * {@inheritDoc}
	 */
	public function __construct(IOInterface $io, Composer $composer, $type = 'library')
	{
		parent::__construct($io, $composer, $type);

		$this->rootDir = dirname($this->vendorDir);
		$this->type = 'venne-module';
	}


	/**
	 * {@inheritDoc}
	 */
	public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		try {
			$this->repo = $repo;
			$this->registerRobotLoader();
			$container = $this->getContainer();

			parent::install($repo, $package);

			$name = $this->getModuleNameByPackage($package);
			$extra = $package->getExtra();


			// enable module in config
			$orig = $modules = $this->loadModuleConfig();
			if (!array_search($name, $modules['modules'])) {
				$modules['modules'][$name] = array(
					'version' => $package->getVersion(),
					'status' => 'installed',
					'path' => str_replace($this->vendorDir, '%libsDir%', $this->getInstallPath($package)),
				);
			}
			$this->saveModuleConfig($modules);
			$this->actions[] = function ($self) use ($orig) {
				$self->saveModuleConfig($orig);
			};


			// create resources dir
			$resourcesDir = $this->getContainer()->parameters['resourcesDir'];
			$moduleDir = $resourcesDir . "/{$name}Module";
			$targetDir = $this->getInstallPath($package) . '/Resources/public';
			if (!file_exists($moduleDir) && file_exists($targetDir)) {
				umask(0000);
				if (symlink(File::getRelativePath($resourcesDir, $targetDir), $moduleDir) === false) {
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
			if (isset($extra['venne']['configuration'])) {
				$adapter = new \Nette\Config\Adapters\NeonAdapter();
				$orig = $data = $this->loadConfig();
				$data = array_merge_recursive($data, $extra['venne']['configuration']); //\Nette\Utils\Arrays::mergeTree($data, $extra['venne']['configuration']);
				$this->saveConfig($data);

				$this->actions[] = function ($self) use ($orig) {
					$self->saveConfig($orig);
				};
			}

			// run extra installers
			if (isset($extra['venne']['installers'])) {
				foreach ($extra['venne']['installers'] as $class) {
					$class = '\\' . $class;

					/** @var $class BaseInstaller */
					$class = new $class($this->io, $this->composer, $container);
					$class->install($repo, $package);
				}
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

			parent::uninstall($repo, $package);

			throw $e;
		}
	}


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
		return $arr1;
	}


	/**
	 * {@inheritDoc}
	 */
	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		$this->repo = $repo;
		$this->registerRobotLoader();
		$container = $this->getContainer();

		$name = $this->getModuleNameByPackage($package);
		$extra = $package->getExtra();

		// run extra installers
		if (isset($extra['venne']['installers'])) {
			foreach ($extra['venne']['installers'] as $class) {
				$class = '\\' . $class;

				/** @var $class BaseInstaller */
				$class = new $class($this->io, $this->composer, $container);
				$class->uninstall($repo, $package);
			}
		}

		// update main config.neon
		if (isset($extra['venne']['configuration'])) {
			$adapter = new \Nette\Config\Adapters\NeonAdapter();
			$orig = $data = $this->loadConfig();
			$data = $this->getRecursiveDiff($data, $extra['venne']['configuration']);
			$this->saveConfig($data);

			$this->actions[] = function ($self) use ($orig) {
				$self->saveConfig($orig);
			};
		}

		// remove resources dir
		$resourcesDir = $this->getContainer()->parameters['resourcesDir'] . "/{$name}Module";
		if (file_exists($resourcesDir)) {
			if (is_link($resourcesDir)) {
				unlink($resourcesDir);
			} else {
				File::rmdir($resourcesDir, true);
			}
		}

		// remove module from config
		$modules = $this->loadModuleConfig();
		unset($modules['modules'][$name]);
		$this->saveModuleConfig($modules);

		parent::uninstall($repo, $package);
	}


	protected function registerRobotLoader()
	{
		// Load nette
		if (!defined('VENNE')) {
			$loader = $this->vendorDir . '/nette/nette/Nette/loader.php';
			if (file_exists($loader)) {
				include_once $loader;
			}
		}

		// Find packages
		$autoloads = array();
		foreach ($this->repo->getPackages() as $pkg) {
			if ($pkg->getName() != 'nette/nette') {
				$autoloads[] = array($pkg, parent::getInstallPath($pkg)); // FIXME: ugly!
			}
		}

		// Class loader for custom installers
		$generator = new AutoloadGenerator;
		$map = $generator->parseAutoloads($autoloads);
		$classLoader = $generator->createLoader($map);
		$classLoader->register();
	}


	/**
	 * @return Container|\SystemContainer
	 */
	protected function getContainer()
	{
		if (!$this->_container) {
			if (!defined('VENNE')) {
				$this->registerRobotLoader();
				\Nette\Diagnostics\Debugger::enable(\Nette\Diagnostics\Debugger::DEVELOPMENT);
			}

			$configurator = new Configurator($this->rootDir);
			$configurator->enableLoader();
			$this->_container = $configurator->getContainer();
		}
		return $this->_container;
	}


	/**
	 * @return ModuleManager
	 */
	protected function getModuleManager()
	{
		return $this->getContainer()->venne->moduleManager;
	}


	/**
	 * @param PackageInterface $package
	 * @return string
	 */
	protected function getModuleNameByPackage(PackageInterface $package)
	{
		return substr($package->getName(), 6, -7);
	}


	/**
	 * @return string
	 */
	protected function getModuleConfigPath()
	{
		return $this->getContainer()->parameters['configDir'] . '/settings.php';
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
	 * @return string
	 */
	protected function getConfigPath()
	{
		return $this->getContainer()->parameters['configDir'] . '/config.neon';
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
