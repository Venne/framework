<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Module;

use Venne;

/**
 * @author Josef Kříž
 */
class Container extends Venne\DI\Container {


	protected $_moduleUpdates;

	/** @var \Venne\DI\Container */
	protected $context;

	/** @var array */
	protected $_modules;



	public function __construct($context)
	{
		$this->context = $context;
	}



	/**
	 * @return bool
	 */
	public function checkModuleUpgrades()
	{
		if (!$this->_moduleUpdates) {
			$this->_moduleUpdates = false;
			foreach ($this->getModules() as $module => $item) {
				if (!version_compare($this->context->modules->{$module}->getVersion(), $item["version"], '==')) {
					$this->_moduleUpdates = true;
					break;
				}
			}
		}
		return $this->_moduleUpdates;
	}



	public function getModules()
	{
		if (!$this->_modules) {
			$this->_modules = array();
			foreach ($this->context->params['modules'] as $name => $item) {
				if (isset($item["run"]) && $item["run"]) {
					$this->_modules[$name] = $item;
				}
			}
		}
		return $this->_modules;
	}

}