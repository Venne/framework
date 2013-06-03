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

use Venne\Module\IModule;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface IInstaller
{

	/**
	 * @param IModule $module
	 */
	public function install(IModule $module);


	/**
	 * @param IModule $module
	 */
	public function uninstall(IModule $module);


	/**
	 * @param IModule $module
	 */
	public function upgrade(IModule $module, $from, $to);


	/**
	 * @param IModule $module
	 */
	public function downgrade(IModule $module, $from, $to);
}

