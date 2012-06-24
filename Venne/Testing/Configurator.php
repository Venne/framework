<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Testing;

use Venne;

/**
 * @author     Josef Kříž
 */
class Configurator extends \Venne\Config\Configurator
{

	/** @var \Venne\Config\Configurator */
	protected static $configurator;


	public function __construct($parameters = NULL, $modules = NULL, $productionMode = NULL)
	{
		parent::__construct($parameters, $modules, $productionMode);
		static::$configurator = $this;
	}


	/**
	 * @static
	 * @return \Nette\DI\Container
	 */
	public static function getTestsContainer()
	{
		return static::$configurator->getContainer();
	}


}
