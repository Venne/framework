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
 * @Entity(repositoryClass="\App\CoreModule\Repositories\RoleRepository")
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
	 * @OneToMany(targetEntity="RoleEntity", mappedBy="parent", cascade={"persist", "remove", "detach"})
	 */
	protected $childrens;

	/**
	 * @ManyToOne(targetEntity="RoleEntity", inversedBy="id", cascade={"persist", "remove", "detach"})
	 * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 * @OrderBy({"order" = "ASC"})
	 */
	protected $parent;

	/**
	 * @OneToMany(targetEntity="PermissionEntity", mappedBy="role")
	 */
	protected $permissions;

	/**
	 * @ManyToMany(targetEntity="UserEntity", mappedBy="roles")
	 */
	protected $users;



	public function __toString()
	{
		return $this->name;
	}



	public function __construct()
	{
		$this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->users = new \Doctrine\Common\Collections\ArrayCollection();
		$this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
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



	/**
	 * @return \Nette\Security\Permission
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}



	/**
	 * @param $users
	 */
	public function setUsers($users)
	{
		$this->users = $users;
	}



	/**
	 * @return mixed
	 */
	public function getUsers()
	{
		return $this->users;
	}


}
