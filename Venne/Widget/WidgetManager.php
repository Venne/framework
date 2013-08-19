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

use Nette\Callback;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WidgetManager extends Object
{

	/** @var Callback[] */
	protected $widgets = array();


	/**
	 * @param $name
	 * @param $class
	 * @param $factory
	 * @throws InvalidArgumentException
	 */
	public function addWidget($name, $class, $factory)
	{
		if (!$factory instanceof Callback) {
			throw new InvalidArgumentException('Second argument must be callback');
		}

		if (!is_string($name)) {
			throw new InvalidArgumentException('Name of widget must be string');
		}

		$this->widgets[$name] = array(
			'factory' => $factory,
			'class' => $class,
		);
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
	 * @return Callback
	 * @throws InvalidArgumentException
	 */
	public function getWidget($name)
	{
		if (!$this->hasWidget($name)) {
			throw new InvalidArgumentException("Widget $name does not exists");
		}

		return $this->widgets[$name]['factory'];
	}
}

