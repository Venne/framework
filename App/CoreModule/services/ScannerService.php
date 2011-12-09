<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne;
use Nette\Object;

/**
 * @author Josef Kříž
 */
class ScannerService extends Object {


	/** @var \Venne\DI\Container */
	protected $context;



	public function __construct($context)
	{
		$this->context = $context;
	}



	public function getModules()
	{
		$arr = array();
		$paths = array(
			$this->context->params["appDir"],
			$this->context->params["libsDir"] . '/App'
		);
		foreach ($paths as $path) {
			foreach (\Nette\Utils\Finder::findDirectories("*Module")->in($path) as $file) {
				if (file_exists($path . "/" . $file->getBaseName() . "/Module.php")) {
					$module = lcfirst(substr($file->getBaseName(), 0, -6));
					$arr[] = $module;
				}
			}
		}
		return $arr;
	}



	public function getLinksOfModules()
	{
		return $this->getLinksOfModulesRecursion($this->context->params["appDir"]) + $this->getLinksOfModulesRecursion($this->context->params["libsDir"] . '/App');
	}



	protected function getLinksOfModulesRecursion($dir)
	{
		$arr = array();
		foreach (\Nette\Utils\Finder::findDirectories("*Module")->in($dir) as $file) {
			$module = substr($file->getBaseName(), 0, -6);
			$arr[] = $module;
			$sub = $this->getLinksOfModulesRecursion($dir . "/" . $file->getBaseName());
			foreach ($sub as $item) {
				$arr[] = $module . ":" . $item;
			}
		}
		return $arr;
	}



	public function getLinksOfPresenters($module)
	{
		$data = array();
		$dir = $this->context->params["appDir"];
		$module = explode(":", $module);
		foreach ($module as $item) {
			$dir .= "/" . $item . "Module";
		}
		if (file_exists($dir . "/presenters")) {
			foreach (\Nette\Utils\Finder::findFiles("*Presenter.php")->in($dir . "/presenters") as $file) {
				$data[] = substr($file->getBaseName(), 0, -13);
			}
		}
		return $data;
	}



	public function getLinksOfModulesPresenters()
	{
		$data = array();
		$arr = $this->getLinksOfModules();
		foreach ($arr as $module) {
			$presenters = $this->getLinksOfPresenters($module);
			foreach ($presenters as $presenter) {
				$data[] = $module . ":" . $presenter;
			}
		}
		return $data;
	}



	public function getLinksOfActions($module, $presenter)
	{
		$data = array();
		$dir = $this->context->params["appDir"];
		$module = explode(":", $module);
		foreach ($module as $item) {
			$dir .= "/" . $item . "Module";
		}
		$dir .= "/templates/" . ucfirst($presenter);
		if (file_exists($dir)) {
			foreach (\Nette\Utils\Finder::findFiles("*")->from($dir) as $file) {
				$data[] = substr($file->getBaseName(), 0, -6);
			}
		}
		return $data;
	}



	public function getLinksOfParams($module, $presenter)
	{
		$data = array();
		$dir = $this->context->params["appDir"];
		$module = explode(":", $module);
		foreach ($module as $item) {
			$dir .= "/" . $item . "Module";
		}
		$file = $dir . "/presenters/" . ucfirst($presenter) . "Presenter.php";

		if (file_exists($file)) {
			$text = file_get_contents($file);
			preg_match_all('/@persistent(.*?)\\n(.*?)public(.*?)\$(.*?)[;= ]/', $text, $matches);

			foreach ($matches[4] as $item) {
				$data[] = $item;
			}
		}
		return $data;
	}



	public function getThemes()
	{
		$data = array();
		foreach (\Nette\Utils\Finder::findDirectories("*")->in($this->context->params["wwwDir"] . "/themes/") as $file) {
			$data[$file->getBaseName()] = $file->getBaseName();
		}
		return $data;
	}



	public function getLayouts()
	{
		$data = array();
		foreach (\Nette\Utils\Finder::findFiles("@*.latte")->in($this->context->params["wwwDir"] . "/themes/" . $this->context->params["website"]["theme"] . "/layouts/") as $file) {
			$data[substr($file->getBaseName(), 1, -6)] = substr($file->getBaseName(), 1, -6);
		}
		return $data;
	}



	/**
	 *
	 * @param string $subclass
	 * @param array $ignore 
	 */
	public function searchClassesBySubclass($subclass, $prefix = "", $ignore = array())
	{
		$classes = array();
		$robotLoader = $this->context->robotLoader;
		foreach ($robotLoader->getIndexedClasses() as $key => $item) {
			if ($key == "Venne\Testing\TestCase") {
				continue; // because Class 'PHPUnit_Framework_TestCase' not found
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
					$classes[] = $key;
				}
			} catch (\Exception $e) {
				
			}
		}
		return $classes;
	}

}

