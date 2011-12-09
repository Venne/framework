<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Event;

use Nette;

/**
 * @author Josef Kříž
 */
class EventArgs extends \Doctrine\Common\EventArgs {


	/** @var array */
	private $list = array();



	/**
	 * @param \App\StaModule\NavigationEntity $entity 
	 */
	public function addNavigation(\App\CoreModule\NavigationEntity $entity)
	{
		$this->list[] = $entity;
	}



	/**
	 * @return array
	 */
	public function getNavigations()
	{
		return $this->list;
	}

}
