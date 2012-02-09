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
 * @Entity(repositoryClass="\App\CoreModule\Repositories\UserRepository")
 * @Table(name="user")
 *
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $salt
 * @property array $roles
 */
class UserEntity extends \Nette\Security\Identity implements \Venne\Doctrine\ORM\IEntity {


	protected $roleNames;

	protected $data = array();



	public function __construct()
	{
		$this->roles = new \Doctrine\Common\Collections\ArrayCollection();
		$this->login = "";
		$this->password = "";
		$this->email = "";
		$this->invalid = true;

		$this->salt = \Nette\Utils\Strings::random(8);
		$this->key = \Nette\Utils\Strings::random(30);
	}



	/**
	 * @id
	 * @generatedValue
	 * @column(type="integer")
	 */
	public $id;

	/**
	 * @Column(type="boolean")
	 */
	public $enable;

	/**
	 * @Column(type="string")
	 */
	public $email;

	/**
	 * @Column(type="string")
	 */
	protected $password;

	/**
	 * @Column(type="string", name="`key`")
	 */
	public $key;

	/**
	 * @Column(type="string")
	 */
	public $salt;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ManyToMany(targetEntity="RoleEntity", indexBy="id", cascade={"persist", "remove", "detach"})
	 * @JoinTable(name="users_roles",
	 *	  joinColumns={@JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *	  inverseJoinColumns={@JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *	  )
	 */
	public $roles;


	/**
	 * @OneToMany(targetEntity="loginEntity", mappedBy="user")
	 */
	protected $logins;



	public function invalidateLogins()
	{
		foreach ($this->logins as $login) {
			$user->valid = false;
		}
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}



	/**
	 * @param type \Venne\Modules\Role
	 */
	public function addRole($role)
	{
		$this->roles[] = $role;
	}



	/**
	 * @param type \Venne\Modules\Role
	 */
	public function removeRole($role)
	{
		$this->roles->removeElement($role);
	}



	public function getRoleEntities()
	{
		return $this->roles;
	}



	/**
	 * Sets a list of roles that the user is a member of.
	 *
	 * @param  array
	 * @return Identity  provides a fluent interface
	 */
	public function setRoles(array $roles)
	{
		$this->roleNames = $roles;
		$this->invalid = true;
		return $this;
	}



	/**
	 * Returns a list of roles that the user is a member of.
	 *
	 * @return array
	 */
	public function getRoles()
	{
		if (!$this->roleNames) {
			$ret = array();
			foreach ($this->roles as $role) {
				$ret[] = $role->name;
			}
			$this->roleNames = $ret;
		}
		return $this->roleNames;
	}



	/**
	 * Returns a user data.
	 *
	 * @return array
	 */
	public function getData()
	{
		return array("name" => $this->name, "email" => $this->email, "salt" => $this->salt, "id" => $this->id);
	}



	/**
	 * Returns a user data.
	 *
	 * @return array
	 */
	public function setData($array)
	{
		$this->name = $array["name"];
		$this->email = $array["email"];
		$this->salt = $array["salt"];
		$this->id = $array["id"];
	}



	public function getId()
	{
		return $this->name;
	}



	public function setPassword($password)
	{
		if (!$this->salt) {
			$this->salt = \Nette\Utils\Strings::random(8);
		}

		$this->password = md5($this->salt . $password);
	}



	public function getPassword()
	{
		return $this->password;
	}



	public function setInvalid($invalid)
	{
		$this->invalid = $invalid;
	}



	public function getInvalid()
	{
		return $this->invalid;
	}



	public function getLogins()
	{
		return $this->logins;
	}


}
