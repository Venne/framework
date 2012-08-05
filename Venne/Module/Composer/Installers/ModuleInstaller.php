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

	const RESOURCES_MODE_SYMLINK = 'symlink';

	const RESOURCES_MODE_COPY = 'copy';

	/** @var string */
	protected $resourcesMode;

	/** @var Container|\SystemContainer */
	protected $_container;

	/** @var string */
	protected $rootDir;

	/** @var InstalledRepositoryInterface */
	protected $repo;


	/**
	 * {@inheritDoc}
	 */
	public function __construct(IOInterface $io, Composer $composer, $type = 'library')
	{
		parent::__construct($io, $composer, $type);

		$this->rootDir = dirname($this->vendorDir);
		$this->type = 'venne-module';
		$this->resourcesMode = self::RESOURCES_MODE_SYMLINK;
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

		// Load Venne
		$loader = new \Composer\Autoload\ClassLoader();
		$loader->add('Venne', $this->vendorDir . '/venne/framework');
		$loader->register();

		// Find packages
		$autoloads = array();
		foreach ($this->repo->getPackages() as $pkg) {
			if ($pkg->getName() != 'nette/nette' && $pkg->getName() != 'venne/framework') {
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
	 * {@inheritDoc}
	 */
	public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		parent::install($repo, $package);

		$this->repo = $repo;
		$this->registerRobotLoader();
		$name = $this->getModuleNameByPackage($package);
		$extra = $package->getExtra();

		// run extra installers
		if (isset($extra['venne']['installers'])) {
			foreach ($extra['venne']['installers'] as $class) {
				$class = '\\' . $class;
				$installer = new $class($this->io, $this->composer);
				$this->composer->getInstallationManager()->addInstaller($installer);
			}
		}

		// enable module in config
		$modules = $this->loadModuleConfig();
		if (!array_search($name, $modules)) {
			$modules['modules'][$name] = array(
				'version' => $package->getVersion(),
				'status' => 'installed',
				'path' => $this->getInstallPath($package),
			);
		}
		$this->saveModuleConfig($modules);

		// create resources dir
		$resourcesDir = $this->getContainer()->parameters['resourcesDir'] . "/{$name}Module";
		$targetDir = $this->getInstallPath($package) . '/Resources/public';
		if (!file_exists($resourcesDir) && file_exists($targetDir)) {
			umask(0000);
			if ($this->resourcesMode == self::RESOURCES_MODE_SYMLINK) {
				@symlink($targetDir, $resourcesDir);
			} else {
				@copy($targetDir, $resourcesDir);
			}
		}

		// update main config.neon
		if (isset($extra['venne']['configuration'])) {
			$adapter = new \Nette\Config\Adapters\NeonAdapter();
			$data = $this->loadConfig();
			$data = \Nette\Utils\Arrays::mergeTree($data, $extra['venne']['configuration']);
			$this->saveConfig($data);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
		parent::uninstall($repo, $package);

		$this->repo = $repo;
		$this->registerRobotLoader();
		$name = $this->getModuleNameByPackage($package);
		$extra = $package->getExtra();

		// run extra installers
		if (isset($extra['venne']['installers'])) {
			foreach ($extra['venne']['installers'] as $class) {
				$class = '\\' . $class;
				$installer = new $class($this->io, $this->composer);
				$this->composer->getInstallationManager()->addInstaller($installer);
			}
		}

		// remove resources dir
		$resourcesDir = $this->getContainer()->parameters['resourcesDir'] . "/{$name}Module";
		if (file_exists($resourcesDir)) {
			if ($this->resourcesMode == self::RESOURCES_MODE_SYMLINK) {
				unlink($resourcesDir);
			} else {
				\Venne\Utils\File::rmdir($resourcesDir, true);
			}
		}

		// remove module from config
		$modules = $this->loadModuleConfig();
		unset($modules['modules'][$name]);
		$this->saveModuleConfig($modules);
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
	protected function saveModuleConfig($data)
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
	protected function saveConfig($data)
	{
		$config = new NeonAdapter();
		file_put_contents($this->getConfigPath(), $config->dump($data));
	}
}
