<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Utils;

use Venne;
use Nette\Object;
use Nette\Utils\Finder;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class File extends Object
{
	/**
	 * Removes directory.
	 *
	 * @static
	 * @param string $dirname
	 * @param bool $recursive
	 * @return bool
	 */
	public static function rmdir($dirname, $recursive = false)
	{
		if (!$recursive) {
			return rmdir($dirname);
		}

		$dirContent = Finder::find('*')->from($dirname)->childFirst();
		foreach ($dirContent as $file) {
			if ($file->isDir())
				@rmdir($file->getPathname());
			else
				@unlink($file->getPathname());
		}

		@unlink($dirname);
		return true;
	}

}

