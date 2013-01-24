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

	/**
	 * Expands @fooModule/path/....
	 * @static
	 * @param $path
	 * @param array $modules
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	public static function expandPath($path, & $modules, & $paths = NULL)
	{
		if (substr($path, 0, 1) !== '@') {
			return $path;
		}

		if (($pos = strpos($path, 'Module')) !== false) {
			$module = lcfirst(substr($path, 1, $pos - 1));

			if (isset($modules[$module])) {
				return $modules[$module]['path'] . substr($path, $pos + 6);
			}

			if (isset($paths[$module . 'Dir'])) {
				return $paths[$module . 'Dir'] . substr($path, $pos + 6);
			}

			throw new \Nette\InvalidArgumentException("Module '{$module}' does not exist.");
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
	public static function expandResource($path)
	{
		if (substr($path, 0, 1) !== '@') {
			return $path;
		}

		$pos = strpos($path, 'Module');
		$module = lcfirst(substr($path, 1, $pos - 1));

		return 'resources/' . $module . 'Module' . substr($path, $pos + 6);
	}
}

