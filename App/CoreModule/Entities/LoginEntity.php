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
 * @Table(name="login")
 */
class LoginEntity extends \Venne\Doctrine\ORM\BaseEntity {


	const USER_ADMIN = NULL;

	/**
	 * @ManyToOne(targetEntity="userEntity", inversedBy="id")
	 * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $user;

	/** @Column(type="string", nullable=true) */
	protected $sessionId;

	/** @Column(type="boolean") */
	protected $valid;

	/** @Column(type="datetime") */
	protected $created;



	/**
	 * @param $user
	 * @param $sessionId
	 */
	public function __construct($user, $sessionId)
	{
		$this->user = $user;
		$this->sessionId = $sessionId;
		$this->created = new \DateTime;
		$this->valid = true;
	}


	/**
	 * @param $sessionId
	 */
	public function setSessionId($sessionId)
	{
		$this->sessionId = $sessionId;
	}



	/**
	 * @return mixed
	 */
	public function getSessionId()
	{
		return $this->sessionId;
	}



	/**
	 * @param $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}



	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}



	/**
	 * @param $valid
	 */
	public function setValid($valid)
	{
		$this->valid = $valid;
	}



	/**
	 * @return mixed
	 */
	public function getValid()
	{
		return $this->valid;
	}
}
