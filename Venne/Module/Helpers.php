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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
final class Helpers
{

	/** @var array */
	private $modules;


	public function __construct($modules)
	{
		$this->modules = & $modules;
	}


	/**
	 * Expands @fooModule/path/....
	 * @static
	 * @param $path
	 * @param array $modules
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	public function expandPath($path, $localPrefix = '')
	{
		if (substr($path, 0, 1) !== '@') {
			return $path;
		}

		if (($pos = strpos($path, 'Module')) !== FALSE) {
			$module = lcfirst(substr($path, 1, $pos - 1));

			if (!isset($this->modules[$module])) {
				throw new \Nette\InvalidArgumentException("Module '{$module}' does not exist.");
			}

			return $this->modules[$module]['path'] . ($localPrefix ? '/' . $localPrefix : '') . substr($path, $pos + 6);
		}
	}


	/**
	 * Expands @fooModule/path/....
	 * @static
	 * @param $path
	 * @param array $modules
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	public function expandResource($path)
	{
		if (substr($path, 0, 1) !== '@') {
			return $path;
		}

		$pos = strpos($path, 'Module');
		$module = lcfirst(substr($path, 1, $pos - 1));

		return 'resources/' . $module . 'Module' . substr($path, $pos + 6);
	}
}

