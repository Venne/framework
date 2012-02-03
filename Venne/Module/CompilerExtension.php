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
use Nette\DI\ContainerBuilder;
use Nette\Utils\Finder;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CompilerExtension extends Venne\Config\CompilerExtension {


	/** @var array */
	protected $classes = array();

	/** @var array */
	protected $configs = array();



	function __construct($modulePath, $moduleNamespace)
	{
		$classes = array(self::CONTROL => array(), self::SUBSCRIBER => array(), self::SERVICE => array(), self::MACRO => array(), self::FORM => array(), self::MANAGER => array(), self::REPOSITORY => array(), self::WIDGET => array());

		foreach (Finder::findFiles("*.php")->from($modulePath)->exclude(".git", "Resources") as $file) {
			$relative = $file->getRealpath();
			$relative = str_replace("libs-all/venne/App", "libs/App", $relative); // hack for symlinks
			$relative = strtr($relative, array($modulePath => '', '/' => '\\'));
			$class = $moduleNamespace . '\\' . ltrim(substr($relative, 0, -4), '\\');
			$class = str_replace("presenters\\", "", $class);

			try{
				$refl = \Nette\Reflection\ClassType::from($class);

				foreach (array_keys($classes) as $item) {
					if ($item == self::REPOSITORY) {
						continue;
					}

					if (\Nette\Utils\Strings::endsWith($class, ucfirst($item))) {
						$classes[$item][$class] = lcfirst(substr($class, strrpos($class, "\\") + 1));
					}
				}

				if ($refl->isSubclassOf("\\Venne\\Doctrine\\ORM\\IEntity") && $refl->hasAnnotation("Entity")) {
					$anot = $refl->getAnnotation("Entity");
					$classes[self::REPOSITORY][$class] = substr($anot["repositoryClass"], 0, 1) == "\\" ? substr($anot["repositoryClass"], 1) : $anot["repositoryClass"];
				}
			}catch(\ReflectionException $ex){

			}
		}

		foreach (Finder::findFiles("*.neon")->from($modulePath) as $file) {
			$this->configs[] = $file->getRealpath();
		}

		$this->classes = $classes;
	}



	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig();


		/* configs */
		foreach ($this->configs as $config) {
			\Nette\Config\Compiler::parseServices($container, $this->loadFromFile($config));
		}


		/* services */
		foreach ($this->classes[self::SERVICE] as $class => $name) {
			$this->compileService($class, $this->prefix($name));
		}


		/* repositories */
		foreach ($this->classes[self::REPOSITORY] as $class => $name) {
			$this->compileRepository($name, $this->prefix($class));
		}


		/* managers */
		foreach ($this->classes[self::MANAGER] as $class => $name) {
			$this->compileManager($class, $this->prefix($name));
		}


		/* macros */
		foreach ($this->classes[self::MACRO] as $class => $name) {
			$this->compileMacro($class, $this->prefix($name));
		}


		/* subscribers */
		foreach ($this->classes[self::SUBSCRIBER] as $class => $name) {
			$this->compileSubscriber($class, $this->prefix($name));
		}


		/* controls */
		foreach ($this->classes[self::CONTROL] as $class => $name) {
			$this->compileControl($class, $this->prefix($name));
		}


		/* widgets */
		foreach ($this->classes[self::WIDGET] as $class => $name) {
			$this->compileWidget($class, $name);
		}


		/* forms */
		foreach ($this->classes[self::FORM] as $class => $name) {
			$this->compileForm($class, $this->prefix($name));
		}
	}

}

