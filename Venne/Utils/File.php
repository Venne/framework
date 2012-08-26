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

		@rmdir($dirname);
		return true;
	}


	/**
	 * Get relative path.
	 *
	 * @static
	 * @param $from
	 * @param $to
	 * @return string
	 */
	public static function getRelativePath($from, $to)
	{
		$from = explode('/', $from);
		$to = explode('/', $to);
		$relPath = $to;

		foreach ($from as $depth => $dir) {
			if ($dir === $to[$depth]) {
				array_shift($relPath);
			} else {
				$remaining = count($from) - $depth;
				if ($remaining > 1) {
					// add traversals up to first matching dir
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}
		return implode('/', $relPath);
	}
}

