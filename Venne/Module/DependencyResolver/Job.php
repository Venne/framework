<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module\DependencyResolver;

use Nette\Object;
use Venne\Module\IModule;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Job extends Object
{

	/** @var string */
	protected $action;

	/** @var IModule */
	protected $module;


	/**
	 * @param $action
	 * @param \Venne\Module\IModule $module
	 */
	public function __construct($action, IModule $module)
	{
		$this->action = $action;
		$this->module = $module;
	}


	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}


	/**
	 * @return \Venne\Module\IModule
	 */
	public function getModule()
	{
		return $this->module;
	}
}

