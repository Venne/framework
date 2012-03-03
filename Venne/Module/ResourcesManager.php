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
use Nette\Caching\Cache;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ResourcesManager extends Object
{


	const CACHE = "Venne.Resources";

	/** @var \SystemContainer */
	protected $container;

	/** @var Cache */
	protected $cache;

	/** @var array */
	protected $filters = array();



	/**
	 * Constructor.
	 *
	 * @param Container $container
	 */
	function __construct(Container $container)
	{
		$this->container = $container;
		$this->cache = new Cache($this->container->cacheStorage, self::CACHE);
	}



	/**
	 * Register filter for files with file extension.
	 *
	 * @param IResourceFilter $filter
	 * @param type $fileExtension
	 */
	public function registerFilter(IResourceFilter $filter, $fileExtension)
	{
		if (!isset($this->filters[$type])) {
			$this->filters[$type] = array();
		}

		$this->filters[$type] = $filter;
	}



	/**
	 * Check changes in all modules.
	 */
	public function checkResources()
	{
		$data = $this->cache->load("resources");
		$save = false;

		if (!$data) {
			$data = array();
		}

		foreach ($this->container->findByTag("module") as $module => $item) {
			$hash = $this->md5_dir($this->container->{$module}->getPath()) . "\n";

			if (!isset($data[$module])) {
				$data[$module] = array("hash" => true);
			}

			if ($data[$module]["hash"] !== $hash || !file_exists($this->container->parameters["resourcesDir"] . "/" . $module)) {
				$save = true;
				$this->syncModule($module);
				$data[$module]["hash"] = $hash;
			}
		}

		if ($save) {
			$this->cache->save("resources", $data);
		}
	}



	/**
	 * Get md5 of directory.
	 *
	 * @param string $path
	 * @return string
	 */
	protected function md5_dir($path)
	{
		return md5($this->hash_dir($path));
	}



	/**
	 * Get unique string for directory.
	 *
	 * @param string $path
	 * @return string
	 */
	protected function hash_dir($path)
	{
		$string = "";

		if ($dh = opendir($path)) {
			while (($file = readdir($dh)) !== false) {
				if ($file == ".." || $file == ".") {
					continue;
				}

				if (is_dir($path . "/" . $file)) {
					$string .= md5($file) . "\n" . $this->md5_dir($path . "/" . $file);
				}

				if (is_file($path . "/" . $file)) {
					$string .= filemtime($path . "/" . $file) . "\n";
				}
			}
			closedir($dh);
		}

		return $string;
	}



	/**
	 * Synchronize files to %resourcesDir% folder.
	 *
	 * @param string $name
	 */
	protected function syncModule($name)
	{
		$path = $this->container->{$name}->getPath() . "/Resources/public";
		$dest = $this->container->parameters["resourcesDir"] . "/" . $name;

		$this->rmdir($dest);
		$this->copy($path, $dest);
	}



	/**
	 * Copy a file, or recursively copy a folder and its contents.
	 *
	 * @param string $source
	 * @param string $dest
	 * @return bool
	 */
	protected function copy($source, $dest)
	{
		if (!file_exists($source)) {
			return false;
		}

		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}

		if (is_file($source)) {
			$ext = pathinfo($source, PATHINFO_EXTENSION);

			if (isset($this->filters[$ext])) {
				$this->copyWithFilters($source, $dest, $ext);
			} else {
				return copy($source, $dest);
			}
		}

		if (!is_dir($dest)) {
			mkdir($dest);
		}

		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			$this->copy("$source/$entry", "$dest/$entry");
		}

		$dir->close();
		return true;
	}



	/**
	 * Copy file and apply filters.
	 *
	 * @param type $source
	 * @param type $dest
	 * @param type $ext
	 */
	protected function copyWithFilters($source, $dest, $ext)
	{
		$data = file_get_contents($source);

		foreach ($this->filters[$ext] as $filter) {
			$data = $filter->process($data);
		}

		file_put_contents($dest, $data);
	}



	/**
	 * Recursively rmdir.
	 *
	 * @param string $source
	 * @param string $dest
	 */
	protected function rmdir($directory)
	{
		if (is_dir($directory)) {
			$dirContent = \Nette\Utils\Finder::find('*')->from($directory)->childFirst();
			foreach ($dirContent as $file) {
				if ($file->isDir()) {
					@rmdir($file->getPathname());
				} else {
					@unlink($file->getPathname());
				}
			}

			@unlink($directory);
		}
	}

}

