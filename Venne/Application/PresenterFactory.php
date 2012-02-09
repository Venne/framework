<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application;

use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @author Patrik Votoček
 */
class PresenterFactory implements \Nette\Application\IPresenterFactory {

	const DEFAULT_NAMESPACE = 'App';

	/** @var Nette\DI\IContainer */
	private $container;

	protected $caseSensitive = false;



	/**
	 * @param Nette\DI\IContainer
	 */
	public function __construct(\Nette\DI\IContainer $container)
	{
		$this->container = $container;
	}



	/**
	 * Create new presenter instance.
	 *
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$class = $this->getPresenterClass($name);
		$presenter = new $class($this->container);
		//$presenter->setContext($this->container);
		//$class = str_replace(":", "", $class);
		//$presenter = $this->container->getService($class);
		//$presenter->setDoctrineContainer($this->container->doctrineContainer);
		//$presenter->setContext($this->container);
		return $presenter;
	}



	/**
	 * Format presenter class with prefixes
	 *
	 * @param string
	 * @return string
	 * @throws \Nette\Application\InvalidPresenterException
	 */
	private function formatPresenterClasses($name)
	{
		$class = NULL;
		$namespaces = isset($this->container->parameters['namespaces']) ? $this->container->parameters['namespaces'] : array(static::DEFAULT_NAMESPACE);
		foreach ($namespaces as $namespace) {
			$class = $this->formatPresenterClass($name, $namespace);
			if (class_exists($class)) {
				break;
			}
		}

		//die($class);
		if (!class_exists($class)) {
			$class = $this->formatPresenterClass($name, reset($namespaces));
			throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
		}


		return $class;
	}



	/**
	 * Get presenter class name
	 *
	 * @param string
	 * @return string
	 * @throws \Nette\Application\InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (!is_string($name) || !preg_match("#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#", $name)) {
			throw new \Nette\Application\InvalidPresenterException("Presenter name must be an alphanumeric string, '$name' is invalid.");
		}

		$class = $this->formatPresenterClasses($name);
		$reflection = \Nette\Reflection\ClassType::from($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
		}
		if ($reflection->isAbstract()) {
			throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
		}

		//		// canonicalize presenter name
		//		$realName = $this->unformatPresenterClass($class);
		//		if ($name !== $realName) {
		//			throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
		//		}

		// canonicalize presenter name
		$realName = $this->unformatPresenterClass($class);
		if ($name !== $realName) {
			if ($this->caseSensitive) {
				throw new InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			} else {
				$this->cache[$name] = array($class, $realName);
				$name = $realName;
			}
		} else {
			$this->cache[$name] = array($class, $realName);
		}

		return $class;
	}



	/**
	 * Formats presenter class name from its name.
	 *
	 * @param string presenter name
	 * @param string
	 * @return string
	 */
	public function formatPresenterClass($presenter, $namespace = 'App')
	{
		return $namespace . "\\" . str_replace(':', "Module\\", $presenter . 'Presenter');
	}



	/**
	 * Formats presenter name from class name.
	 *
	 * @param string presenter class
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		$active = "";
		$namespaces = isset($this->container->parameters['namespaces']) ? $this->container->parameters['namespaces'] : array(static::DEFAULT_NAMESPACE);
		foreach ($namespaces as $namespace) {
			if (Strings::startsWith($class, $namespace)) {
				$current = $namespace . "\\";
				if (!$active || strlen($active) < strlen($current)) {
					$active = $current;
				}
			}
		}

		$class = Strings::startsWith('\\', $class) ? substr($class, 1) : $class;
		if (strlen($active)) {
			return str_replace("Module\\", ':', substr($class, strlen($active), -9));
		} else {
			return str_replace("Module\\", ':', substr($class, 0, -9));
		}
	}

}