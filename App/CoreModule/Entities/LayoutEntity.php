<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Entities;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\Venne\Doctrine\ORM\BaseRepository")
 * @Table(name="layout")
 */ class LayoutEntity extends \Venne\Doctrine\ORM\BaseEntity {


	/** @Column(type="string") */
	protected $name;

	/** @Column(type="string") */
	protected $description;

	/** @Column(type="string", nullable=true) */
	protected $module;

	/** @Column(type="string") */
	protected $path;



	public function __toString()
	{
		return $this->name . ($this->module ? " ({$this->module})" : "");
	}



	public function __construct()
	{
		$this->name = "";
		$this->description = "";
	}



	public function getName()
	{
		return $this->name;
	}



	public function setName($name)
	{
		$this->name = $name;
	}



	public function getDescription()
	{
		return $this->description;
	}



	public function setDescription($description)
	{
		$this->description = $description;
	}



	public function getModule()
	{
		return $this->module;
	}



	public function setModule($module)
	{
		$this->module = $module;
	}



	public function getPath()
	{
		return $this->path;
	}



	public function setPath($path)
	{
		$this->path = $path;
	}

}
