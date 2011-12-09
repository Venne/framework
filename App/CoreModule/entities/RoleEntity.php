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
 * @Table(name="role")
 * 
 * @property string $name
 * @property \Doctrine\Common\Collections\ArrayCollection $childrens
 * @property RoleEntity $parent
 */
class RoleEntity extends \Venne\Doctrine\ORM\BaseEntity {


	/**
	 * @Column(type="string")
	 */
	protected $name;

	/**
	 * @OneToMany(targetEntity="roleEntity", mappedBy="parent", cascade={"persist", "remove", "detach"})
	 */
	protected $childrens;

	/**
	 * @ManyToOne(targetEntity="roleEntity", inversedBy="id", cascade={"persist", "remove", "detach"})
	 * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 * @OrderBy({"order" = "ASC"})
	 */
	protected $parent;

	/**
	 * @OneToMany(targetEntity="permissionEntity", mappedBy="role")
	 */
	protected $permissions;


	public function __toString()
	{
		return $this->name;
	}
	

	public function __construct()
	{
		$this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
	}



	/**
	 * @return string 
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $name 
	 */
	public function setName($name)
	{
		$this->name = $name;
	}



	/**
	 * @return RoleEntity
	 */
	public function getParent()
	{
		return $this->parent;
	}



	/**
	 * @param RoleEntity $parent 
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
	}



	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildrens()
	{
		return $this->childrens;
	}



	/**
	 * @param RoleEntity $childrens 
	 */
	public function addChildren($childrens)
	{
		$this->childrens[] = $childrens;
	}

}
