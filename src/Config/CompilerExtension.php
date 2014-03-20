<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Nette\DI\ContainerBuilder;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CompilerExtension extends \Nette\DI\CompilerExtension
{

	public function __construct()
	{
		trigger_error("Class " . __CLASS__ . " has been renamed to Venne\DI\CompilerExtension.", E_USER_WARNING);
	}

}

