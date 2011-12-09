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
 * @Table(name="permission")
 * 
 * @property string $resource
 * @property string $privilege
 * @property bool $allow
 * @property \Venne\Modules\Role $role
 */
class PermissionEntity extends \Venne\Doctrine\ORM\BaseEntity {


	/**
	 * @Column(type="string")
	 */
	protected $resource;

	/**
	 * @Column(type="string", nullable=true)
	 */
	protected $privilege;

	/**
	 * @Column(type="boolean")
	 */
	protected $allow;

	/**
	 * @ManyToOne(targetEntity="roleEntity", inversedBy="id")
	 * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $role;



	/**
	 * @return string 
	 */
	public function getResource()
	{
		return $this->resource;
	}



	/**
	 * @param string $resource 
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
	}



	/**
	 * @return string 
	 */
	public function getRole()
	{
		return $this->role;
	}



	/**
	 * @param string $role 
	 */
	public function setRole($role)
	{
		$this->role = $role;
	}



	/**
	 * @return string 
	 */
	public function getAllow()
	{
		return $this->allow;
	}



	/**
	 * @param string $allow 
	 */
	public function setAllow($allow)
	{
		$this->allow = $allow;
	}



	public function getPrivilege()
	{
		return $this->privilege;
	}



	public function setPrivilege($privilege)
	{
		$this->privilege = $privilege;
	}

}
