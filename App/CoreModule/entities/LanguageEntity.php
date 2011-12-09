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

/**
 * @author Josef Kříž
 * @Entity(repositoryClass="\Venne\Doctrine\ORM\BaseRepository")
 * @Table(name="language")
 */
class LanguageEntity extends \Venne\Doctrine\ORM\BaseEntity {


	/** @Column(type="string") */
	protected $name;

	/** @Column(type="string") */
	protected $short;

	/** @Column(type="string") */
	protected $alias;



	public function __construct()
	{
		$this->name = "";
		$this->short = "";
		$this->alias = "";
	}



	public function __toString()
	{
		return $this->name;
	}



	public function getName()
	{
		return $this->name;
	}



	public function setName($name)
	{
		$this->name = $name;
	}



	public function getShort()
	{
		return $this->short;
	}



	public function setShort($short)
	{
		$this->short = $short;
	}



	public function getAlias()
	{
		return $this->alias;
	}



	public function setAlias($alias)
	{
		$this->alias = $alias;
	}

}
