<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\Routers;

use Nette;
use Nette\Application\Routers\RouteList;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EventArgs extends \Doctrine\Common\EventArgs {


	/** @var RouteList */
	private $routeList;

	/** @var string */
	private $prefix;



	/**
	 * @return RouteList
	 */
	public function getRouteList()
	{
		return $this->routeList;
	}



	/**
	 * @param RouteList $routeList
	 */
	public function setRouteList($routeList)
	{
		$this->routeList = $routeList;
	}



	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}



	/**
	 * @param string $prefix
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

}
