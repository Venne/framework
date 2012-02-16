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
 * @Table(name="`user`")
 *
 * @property-read integer $id
 * @property string $email
 * @property-write string $password
 * @property-read array $roles
 * @property string $key
 */
class UserEntity extends \Nette\Security\Identity implements \Venne\Doctrine\ORM\IEntity
{

	/**
	 * @id
	 * @generatedValue
	 * @column(type="integer")
	 */
	protected $id;

	/**
	 * @Column(type="boolean")
	 */
	protected $enable;

	/**
	 * @Column(type="string", unique=true)
	 */
	protected $email;

	/**
	 * @Column(type="string")
	 */
	protected $password;

	/**
	 * @Column(type="string", name="`key`", nullable=true)
	 */
	protected $key;

	/**
	 * @Column(type="string")
	 */
	protected $salt;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ManyToMany(targetEntity="RoleEntity", indexBy="id", cascade={"persist", "remove", "detach"})
	 * @JoinTable(name="users_roles",
	 *	  joinColumns={@JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *	  inverseJoinColumns={@JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *	  )
	 */
	protected $roles;


	/**
	 * @OneToMany(targetEntity="LoginEntity", mappedBy="user")
	 */
	protected $logins;


	/**
	 * @Form(type="manyToMany", targetEntity="\App\CoreModule\Entities\RoleEntity")
	 */
	protected $roleEntities;



	public function __construct()
	{
		$this->roles = new \Doctrine\Common\Collections\ArrayCollection();
		$this->logins = new \Doctrine\Common\Collections\ArrayCollection();
		$this->enable = false;
		$this->login = "";
		$this->password = "";
		$this->email = "";
		$this->generateNewSalt();
	}



	/**
	 * Invalidate all user logins.
	 */
	public function invalidateLogins()
	{
		foreach ($this->logins as $login) {
			$user->valid = false;
		}
	}



	/**
	 * Set password.
	 *
	 * @param $password
	 */
	public function setPassword($password)
	{
		if (strlen($password) < 5) {
			throw new \Nette\InvalidArgumentException('Minimal length of password is 5 chars.');
		}

		$this->password = $this->getHash($password);
	}



	/**
	 * Get hash of password.
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}



	/**
	 * Verify the password.
	 *
	 * @param $password
	 * @return bool
	 */
	public function verifyByPassword($password)
	{
		if (!$this->isEnable()) {
			return false;
		}

		if ($this->password == $this->getHash($password)) {
			return true;
		}

		return false;
	}



	/**
	 * Disable user and verify by key.
	 *
	 * @param $key
	 */
	public function disableByKey()
	{
		$this->generateNewKey();
	}



	/**
	 * Verify user by key.
	 *
	 * @param $key
	 */
	public function enableByKey($key)
	{
		if ($this->key == $key) {
			$this->key = NULL;
			return true;
		}
		return false;
	}



	/**
	 * Check if user is enable.
	 *
	 * @return bool
	 */
	public function isEnable()
	{
		if (!$this->key && $this->enable) {
			return true;
		}
		return false;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}



	/******************************** Getters and setters **************************************/


	public function getId()
	{
		return $this->id;
	}



	public function setId($id)
	{
		$this->id = $id;
	}



	/**
	 * Returns a list of roles that the user is a member of.
	 *
	 * @return array
	 */
	public function getRoles()
	{
		$ret = array();
		foreach ($this->roles as $entity) {
			$ret[] = $entity->name;
		}
		return $ret;
	}



	public function getRoleEntities()
	{
		return $this->roles;
	}



	/**
	 * Sets a list of roles that the user is a member of.
	 *
	 * @param  array
	 */
	public function setRoleEntities($roles)
	{
		$this->roles = $roles;
		$this->invalidateLogins();
	}



	public function setLogins($logins)
	{
		$this->logins = $logins;
	}



	public function getLogins()
	{
		return $this->logins;
	}



	public function setEnable($enable)
	{
		$this->enable = $enable;
		$this->invalidateLogins();
	}



	public function getEnable()
	{
		return $this->enable;
	}



	public function setKey($key)
	{
		$this->key = $key;

	}



	public function getKey()
	{
		return $this->key;
	}



	public function setEmail($email)
	{
		$this->email = $email;
	}



	public function getEmail()
	{
		return $this->email;
	}



	/******************************** protected function ***************************************/

	/**
	 * Generate random salt.
	 */
	protected function generateNewSalt()
	{
		$this->salt = \Nette\Utils\Strings::random(8);
	}



	/**
	 * Generate random key.
	 */
	protected function generateNewKey()
	{
		$this->key = \Nette\Utils\Strings::random(30);
	}



	/**
	 * Get hash of password.
	 *
	 * @param $password
	 * @return string
	 */
	protected function getHash($password)
	{
		return md5($this->salt . $password);
	}


}
