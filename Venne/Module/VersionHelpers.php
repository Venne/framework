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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class VersionHelpers
{

	/**
	 * @param $version
	 * @return array
	 */
	public static function normalizeRequire($version)
	{
		$ret = array();

		if (strpos($version, 'x') === FALSE) {
			if (substr($version, 1, 1) === '=') {
				$ret[] = array(substr($version, 0, 2) => substr($version, 2));
			} else {
				$ret[] = array(substr($version, 0, 1) => substr($version, 1));
			}
		} else {
			$ret[] = array('>=' => str_replace('x', '0', $version));
			$ret[] = array('<=' => str_replace('x', '999999', $version));
		}

		return $ret;
	}
}

