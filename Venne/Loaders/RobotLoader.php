<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Loaders;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RobotLoader extends \Nette\Loaders\RobotLoader
{

	/**
	 * Get classes by Subclass
	 *
	 * @param string $subclass
	 * @param string $prefix
	 * @param array $ignore
	 * @return array of class => filename
	 */
	public function getIndexedClassesBySubclass($subclass, $prefix = "", $ignore = array())
	{
		$classes = array();
		$ignore = (array) $ignore;
		foreach ($this->getIndexedClasses() as $key => $item) {
			if (strpos($key, "Test") !== false || strpos($key, "/Testing/") !== false) {
				continue;
			}
			if (in_array($key, $ignore)) {
				continue;
			}
			if ($prefix && strpos($key, $prefix) !== 0) {
				continue;
			}
			$class = "\\{$key}";

			$classReflection = new \Nette\Reflection\ClassType($class);
			try {
				if ($classReflection->isSubclassOf($subclass)) {
					$classes[$key] = $item;
				}
			} catch (\Exception $e) {

			}
		}
		return $classes;
	}

}

