<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Widget;

use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WidgetManager extends Object
{

	/** @var Container */
	private $container;

	/** @var Callback[] */
	protected $widgets = array();


	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	/**
	 * @param string $name
	 * @param string $factory
	 * @throws InvalidArgumentException
	 */
	public function addWidget($name, $factory)
	{
		if (!is_string($name)) {
			throw new InvalidArgumentException('Name of widget must be string');
		}

		if (!is_string($factory) && !method_exists($factory, 'create')) {
			throw new InvalidArgumentException('Second argument must be string or factory');
		}

		if (is_string($factory) && !$this->container->hasService($factory)) {
			throw new InvalidArgumentException("Service '$factory' does not exist");
		}

		$this->widgets[$name] = $factory;
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasWidget($name)
	{
		return isset($this->widgets[$name]);
	}


	/**
	 * @return \Callback[]
	 */
	public function getWidgets()
	{
		return $this->widgets;
	}


	/**
	 * @param string $name
	 * @return Control
	 * @throws InvalidArgumentException
	 */
	public function getWidget($name)
	{
		if (!$this->hasWidget($name)) {
			throw new InvalidArgumentException("Widget $name does not exists");
		}

		$factory = $this->widgets[$name];
		return is_string($factory) ? $this->container->getService($factory)->create() : $this->widgets[$name]->create();
	}
}

