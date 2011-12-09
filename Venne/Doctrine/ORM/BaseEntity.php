<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\ORM;

use Nette\Object;

/**
 * @author Josef Kříž
 */
class BaseEntity extends Object {


	/**
	 * @Id @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;



	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}



	/**
	 * @param integer $id 
	 */
	public function setId($id)
	{
		$this->id = $id;
	}



	public function __construct()
	{
		
	}

}

