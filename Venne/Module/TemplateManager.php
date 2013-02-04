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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property \SystemContainer|\Nette\DI\Container $context
 */
class TemplateManager extends Object
{


	/** @var array */
	protected $modules;


	public function __construct($modules)
	{
		$this->modules = & $modules;
	}


	/**
	 * @param $module
	 * @return array
	 */
	public function getLayoutsByModule($module)
	{
		$data = array();
		$path = $this->modules[$module]['path'] . '/Resources/layouts';

		if (file_exists($path)) {
			foreach (\Nette\Utils\Finder::findDirectories("*")->in($path) as $file) {
				if (file_exists($file->getPathname() . '/@layout.latte')) {
					$data[$file->getBasename()] = "@{$module}Module/layouts/{$file->getBasename()}/@layout.latte";
				}
			}
		}

		return $data;
	}


	/**
	 * @param $module
	 * @param null $layout
	 * @param null $subdir
	 * @return array
	 */
	public function getTemplatesByModule($module, $layout = NULL, $subdir = NULL)
	{
		$data = array();

		$prefix = ($layout ? "/layouts/$layout" : '');
		$suffix = ($subdir ? "/$subdir" : '');
		$path = $this->modules[$module]['path'] . "/Resources$prefix/templates$suffix";

		if (file_exists($path)) {
			foreach (\Nette\Utils\Finder::find("*")->in($path) as $file) {
				if (is_file($file->getPathname())) {
					$p = str_replace('/', '.', $subdir);
					$data[($p ? $p . '.' : '') . substr($file->getBasename(), 0, -6)] = "@{$module}Module$prefix/templates$suffix/{$file->getBasename()}";
				} else {
					$data += $this->getTemplatesByModule($module, $layout, ($subdir ? "{$subdir}/{$file->getBasename()}" : $file->getBasename()));
				}
			}
		}

		return $data;
	}


	/**
	 * Get layouts formated for selectbox.
	 *
	 * @return array
	 */
	public function getLayouts()
	{
		$data = array();

		foreach ($this->modules as $name => $item) {
			if ($layouts = $this->getLayoutsByModule($name)) {
				$data[$name] = array();
				foreach ($layouts as $layout => $file) {
					$data[$name][$file] = $layout;
				}
			}
		}

		return $data;
	}
}

