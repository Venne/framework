<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Container extends \Nette\Forms\Container implements IContainer
{

	public function add()
	{
		$args = func_get_args();
		$type = $args[0];
		unset($args[0]);

		$method = 'add' . ucfirst($type);

		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $args);
		}

		if (isset($this->getForm()->controlExtensions[$type])) {
			$obj = $this->getForm()->controlExtensions[$type];
			$args = array_merge(array($this), $args);
			return call_user_func_array(array($obj, $method), $args);
		}

		throw new \Nette\InvalidArgumentException("Type '{$type}' not exists.");
	}


	/**
	 * @param $name
	 * @param $args
	 * @return \Nette\Forms\Container|Container
	 */
	public function __call($name, $args)
	{
		if (substr($name, 0, 3) !== 'add') {
			return parent::__call($name, $args);
		}

		$args = array_merge(array(lcfirst(substr($name, 3))), $args);
		return call_user_func_array(array($this, 'add'), $args);
	}


	/**
	 * @param $name
	 * @return \Nette\Forms\Container|Container
	 */
	public function addContainer($name)
	{
		$control = new Container;
		$control->currentGroup = $this->currentGroup;
		return $this[$name] = $control;
	}
}
