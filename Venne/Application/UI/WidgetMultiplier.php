<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\Object;

/**
 * Widget for Venne:CMS
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WidgetMultiplier extends Object implements \Nette\Application\UI\ISignalReceiver, \Nette\Application\UI\IStatePersistent, \ArrayAccess, IContainer
{

	/** @var \Nette\Application\UI\Multiplier */
	protected $multiplier;

	/** @var array */
	protected $components;

	/** @var string */
	protected $parent;

	/** @var string */
	protected $name;


	public function __construct($factory)
	{
		$this->multiplier = new \Nette\Application\UI\Multiplier($factory);
	}


	public function __call($name, $args)
	{
		return call_user_func_array(array($this->multiplier->getComponent(0), $name), $args);
	}


	public function offsetExists($offset)
	{
		return (bool)$this->getComponent($offset);
	}


	public function offsetGet($offset)
	{
		return $this->getComponent($offset);
	}


	public function offsetSet($offset, $value)
	{
		$this->addComponent($component, $name);
	}


	public function offsetUnset($offset)
	{
		$component = $this->multiplier->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->multiplier->removeComponent($component);
		}
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->getComponent(0)->getName();
	}


	/**
	 * Returns the container if any.
	 * @return IContainer|NULL
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * Sets the parent of this component.
	 * @param  IContainer
	 * @param  string
	 * @return void
	 */
	public function setParent(IContainer $parent = NULL, $name = NULL)
	{
		$this->parent = $parent;
		$this->name = $name;

		$this->multiplier->setParent($parent, $name);
	}


	/**
	 * Adds the specified component to the IComponentContainer.
	 * @param  IComponent
	 * @param  string
	 * @return void
	 */
	public function addComponent(IComponent $component, $name)
	{
		$this->getComponent(0)->addComponent($component, $name);
	}


	/**
	 * Removes a component from the IComponentContainer.
	 * @param  IComponent
	 * @return void
	 */
	public function removeComponent(IComponent $component)
	{
		$this->getComponent(0)->removeComponent($component);
	}


	/**
	 * Returns single component.
	 * @param  string
	 * @return IComponent|NULL
	 */
	public function getComponent($name)
	{
		if (is_numeric($name)) {
			return $this->multiplier->getComponent($name);
		}
		if ($name == $this->name) {
			return $this->multiplier->getComponent(0);
		}
		$component = $this->multiplier->getComponent(0);
		return $component[$name];
	}


	/**
	 * Iterates over a components.
	 * @param  bool    recursive?
	 * @param  string  class types filter
	 * @return \Iterator
	 */
	public function getComponents($deep = FALSE, $filterType = NULL)
	{
		return $this->multiplier->getComponents($deep, $filterType);
	}


	/**
	 * @param  string
	 * @return void
	 */
	public function signalReceived($signal)
	{
	}


	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
	}


	/**
	 * Saves state informations for next request.
	 * @param  array
	 * @return void
	 */
	public function saveState(array & $params)
	{
	}
}
