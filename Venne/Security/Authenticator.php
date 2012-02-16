<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{


	/** @var \SystemContainer */
	private $context;



	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\Nette\DI\Container $context)
	{
		$this->context = $context;
	}



	/**
	 * Performs an authentication
	 *
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$this->invalidateSession();
		list($username, $password) = $credentials;

		if (!$username OR !$password) throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);


		/* Login from config file */
		if ($this->context->parameters["administration"]["login"]["name"] == $username && $this->context->parameters["administration"]["login"]["password"] == $password) {
			$role = $this->context->core->roleRepository->createNew();
			$role->name = "admin";

			$entity = $this->context->core->userRepository->createNew();
			$entity->id = -1;
			$entity->setRoleEntities(array($role));
			return $entity;
		}


		/* Login from DB */
		if ($this->context->createCheckConnection()) {
			$user = $this->context->core->userRepository->findOneBy(array("email" => $username, "enable" => 1));
			if ($user && $user->verifyByPassword($password)) {
				return $user;
			}
		}

		throw new \Nette\Security\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
	}



	/**
	 * Computes salted password hash.
	 *
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return md5($password . str_repeat('*enter any random salt here*', 10));
	}



	/**
	 * Logout
	 */
	public function logout()
	{
		$this->context->user->logout(true);
		$this->invalidateSession();
	}



	/**
	 * Invalidate session with permissions.
	 */
	protected function invalidateSession()
	{
		$session = $this->context->session->getSection(AuthorizatorFactory::SESSION_SECTION);
		$session->remove();
	}

}
