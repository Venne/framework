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
	public static function rmdir($dirname, $recursive = FALSE)
	{
		if (!$recursive) {
			return rmdir($dirname);
		}

		$dirContent = Finder::find('*')->from($dirname)->childFirst();
		foreach ($dirContent as $file) {
			if ($file->isDir()) {
				@rmdir($file->getPathname());
			} else {
				@unlink($file->getPathname());
			}
		}

		@rmdir($dirname);
		return TRUE;
	}


	/**
	 * Copy directory of file.
	 *
	 * @param $source
	 * @param $dest
	 * @return bool
	 */
	public static function copy($source, $dest, $mode = 0777)
	{
		if (is_file($source)) {
			return copy($source, $dest);
		}

		$status = TRUE;

		if (!is_dir($dest)) {
			umask(0000);
			mkdir($dest, $mode, TRUE);
		}

		$dir = dir($source);
		while (FALSE !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			if ($dest !== "$source/$entry") {
				if (self::copy("$source/$entry", "$dest/$entry", $mode) === FALSE) {
					$status = FALSE;
				}
			}
		}
		$dir->close();

		return $status;
	}


	/**
	 * Get relative path.
	 *
	 * @static
	 * @param $from
	 * @param $to
	 * @return string
	 */
	public static function getRelativePath($from, $to, $directorySeparator = NULL)
	{
		$directorySeparator = $directorySeparator ? : ((substr(PHP_OS, 0, 3) === 'WIN') ? '\\' : '/');

		if ($directorySeparator !== '/') {
			$from = str_replace($directorySeparator, '/', $from);
			$to = str_replace($directorySeparator, '/', $to);
		}

		$from = substr($from, -1) !== '/' ? $from . '/' : $from;
		$to = substr($to, -1) !== '/' ? $to . '/' : $to;
		$from = explode('/', $from);
		$to = explode('/', $to);
		$relPath = $to;

		foreach ($from as $depth => $dir) {
			if ($dir === $to[$depth]) {
				array_shift($relPath);
			} else {
				$remaining = count($from) - $depth;
				if ($remaining > 1) {
					$padLength = (count($relPath) + $remaining - 1) * -1;
					$relPath = array_pad($relPath, $padLength, '..');
					break;
				} else {
					$relPath[0] = './' . $relPath[0];
				}
			}
		}
		$relPath = implode('/', $relPath);
		$relPath = substr($relPath, -1) === '/' ? substr($relPath, 0, -1) : $relPath;

		if ($directorySeparator !== '/') {
			$relPath = str_replace('/', $directorySeparator, $relPath);
		}

		return $relPath;
	}
}

