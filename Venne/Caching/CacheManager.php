<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Caching;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Utils\Finder;
use Venne\Utils\File;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CacheManager extends Object
{

	/** @var Cache */
	protected $cache;

	/** @var string */
	protected $cacheDir;

	/** @var string */
	protected $sessionsDir;


	/**
	 * @param IStorage $fileStorage
	 * @param $cacheDir
	 * @param $sessionsDir
	 */
	public function __construct(IStorage $fileStorage, $cacheDir, $sessionsDir)
	{
		$this->cache = new Cache($fileStorage);
		$this->cacheDir = $cacheDir;
		$this->sessionsDir = $sessionsDir;
	}


	public function clean()
	{
		foreach (Finder::find('*')->in($this->cacheDir) as $file) {
			$path = $file->getPathname();

			if (is_dir($path)) {
				File::rmdir($path, TRUE);
			} else {
				unlink($path);
			}
		}
	}


	/**
	 * @param $namespace
	 * @throws \Nette\InvalidArgumentException
	 */
	public function cleanNamespace($namespace)
	{
		$dir = $this->getDirFromNamespace($namespace);

		if (!file_exists($dir)) {
			throw new InvalidArgumentException("Namespace '{$namespace}' does not exist.");
		}

		File::rmdir($this->getDirFromNamespace($namespace), TRUE);
	}


	public function cleanSessions()
	{
		foreach (Finder::find('*')->in($this->sessionsDir) as $file) {
			$path = $file->getPathname();

			if (is_dir($path)) {
				File::rmdir($path, TRUE);
			} else {
				unlink($path);
			}
		}
	}


	/**
	 * @param $namespace
	 * @return string
	 */
	protected function getDirFromNamespace($namespace)
	{
		return $this->cacheDir . '/_' . $namespace;
	}
}

