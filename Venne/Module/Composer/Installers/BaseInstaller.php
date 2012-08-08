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
use Nette\DI\Container;
use Composer\IO\IOInterface;
use Composer\Composer;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseInstaller
{

	/** @var Container|\SystemContainer */
	protected $container;

	protected $io;

	protected $composer;

	protected $vendorDir;

	protected $binDir;

	protected $downloadManager;

	protected $filesystem;


	/**
	 * {@inheritDoc}
	 */
	public function __construct(IOInterface $io, Composer $composer, Container $container, $type = 'library')
	{
		$this->vendorDir = rtrim($composer->getConfig()->get('vendor-dir'), '/');
		$this->binDir = rtrim($composer->getConfig()->get('bin-dir'), '/');
		$this->rootDir = dirname($this->vendorDir);
		$this->downloadManager = $composer->getDownloadManager();
		$this->io = $io;
		$this->composer = $composer;
		$this->container = $container;
		$this->filesystem = new Filesystem();
	}


	/**
	 * {@inheritDoc}
	 */
	public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
	}


	/**
	 * {@inheritDoc}
	 */
	public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
	{
	}


	/**
	 * {@inheritDoc}
	 */
	public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
	{
	}


	public function getInstallPath(PackageInterface $package)
	{
		$this->initializeVendorDir();
		$targetDir = $package->getTargetDir();

		return ($this->vendorDir ? $this->vendorDir . '/' : '') . $package->getPrettyName() . ($targetDir ? '/' . $targetDir : '');
	}


	protected function initializeVendorDir()
	{
		$this->filesystem->ensureDirectoryExists($this->vendorDir);
		$this->vendorDir = realpath($this->vendorDir);
	}
}
