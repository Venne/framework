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

use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
final class Helpers
{

	/** @var array */
	private $modules;


	/**
	 * @param $modules
	 */
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
	 * @throws InvalidArgumentException
	 */
	public function expandPath($path, $localPrefix = '')
	{
		if (substr($path, 0, 1) !== '@' || ($pos = strpos($path, 'Module')) === FALSE) {
			return $path;
		}


		$module = lcfirst(substr($path, 1, $pos - 1));

		if (!isset($this->modules[$module])) {
			throw new InvalidArgumentException("Module '{$module}' does not exist.");
		}

		$path = $this->modules[$module]['path'] . ($localPrefix ? '/' . $localPrefix : '') . substr($path, $pos + 6);
		return \Nette\Utils\Strings::replace($path, '~\\\~', '/');
	}


	/**
	 * Expands @fooModule/path/....
	 * @static
	 * @param $path
	 * @param array $modules
	 * @return string
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

