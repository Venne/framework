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

use Nette\InvalidArgumentException;
use Nette\Object;
use Venne\Module\IModule;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Job extends Object
{

	const ACTION_INSTALL = 'install';

	const ACTION_UPGRADE = 'upgrade';

	const ACTION_UNINSTALL = 'uninstall';

	/** @var string */
	private $action;

	/** @var IModule */
	private $module;

	/** @var array */
	private static $actions = array(
		self::ACTION_INSTALL => TRUE,
		self::ACTION_UNINSTALL => TRUE,
		self::ACTION_UPGRADE => TRUE,
	);


	/**
	 * @param $action
	 * @param IModule $module
	 */
	public function __construct($action, IModule $module)
	{
		if (!isset(self::$actions[$action])) {
			throw new InvalidArgumentException("Action must be one of '" . join(', ', self::$actions) . "'. '{$action}' is given.");
		}

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
	 * @return IModule
	 */
	public function getModule()
	{
		return $this->module;
	}
}

