<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Nette\Object;
use Venne\ORM\Column;

/**
 * @author Josef Kříž
 */
class NavigationEntity extends Object {


	/** @var array of NavigationEntity */
	protected $childrens = array();

	/** @var string */
	protected $link;

	/** @var string */
	protected $mask;

	/** @var string */
	protected $name;

	/** @var array */
	protected $args = array();



	public function __construct($name = "")
	{
		$this->name = $name;
	}



	public function getName()
	{
		return $this->name;
	}



	public function setName($name)
	{
		$this->name = $name;
	}



	public function getChildrens()
	{
		return $this->childrens;
	}



	public function setChildrens($childrens)
	{
		$this->childrens = $childrens;
	}



	public function getLink()
	{
		return $this->link;
	}



	public function setLink($link)
	{
		$this->link = $link;
	}



	public function getMask()
	{
		if (!$this->mask) {
			return $this->link;
		}
		return $this->mask;
	}



	public function setMask($mask)
	{
		$this->mask = $mask;
	}

}
