<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\ORM;

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseEntity extends Object implements IEntity {


	/**
	 * @Id
	 * @Column(type="integer")
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

