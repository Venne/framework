<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SecurityModule;

/**
 * @author Josef Kříž
 */
class Permission extends \Nette\Security\Permission {


	/**
	 * @Column(type="string")
	 */
	protected $resources;



	public function addResource($resource, $parent = NULL)
	{
		if ($parent) {
			$this->resources[$parent][] = $resource;
		} else {
			$this->resources["root"][] = $resource;
		}
		parent::addResource($resource, $parent);
	}



	public function getResources()
	{
		return $this->resources;
	}

}
