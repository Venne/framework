<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

use Nette\DI\Container;

/**
 * @author     Josef Kříž
 */
class Helpers extends \Nette\Object
{

	/** @var \SystemContainer|Container */
	protected $container;

	/** @var \SystemContainer|Container */
	protected $helpers = array();


	function __construct(Container $container)
	{
		$this->container = $container;
	}


	/**
	 * @param string $factory
	 */
	public function addHelper($name, $factory)
	{
		$this->helpers[$name] = $factory;
	}


	/**
	 * Try to load the requested helper.
	 * @param  string  helper name
	 */
	public function loader($helper)
	{
		if (isset($this->helpers[$helper])) {
			return callback($this->helpers[$helper], "filter");
		}
	}
}
