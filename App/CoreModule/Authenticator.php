<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SecurityModule;

use Venne;

/**
 * @author Josef Kříž
 */
class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator {


	/** @var \Nette\DI\Container */
	private $container;



	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\Nette\DI\Container $container)
	{
		$this->container = $container;
	}



	/**
	 * Performs an authentication
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		if (!$username OR !$password)
			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);

		/*
		 * Login from config file
		 */
		$cfg = \Nette\Config\NeonAdapter::load($this->container->params["appDir"] . "/config.neon");
		if (
				$this->container->params["admin"]["name"] == $username &&
				$this->container->params["admin"]["password"] == $password
		) {
			return new \Nette\Security\Identity($username, array("admin"), array("name" => "admin"));
		}

		/*
		 * Login from DB
		 */
		if ($this->container->doctrineContainer->checkConnection()) {
			$user = $this->container->userRepository->findOneBy(array("name" => $username, "enable" => 1));
			if ($user) {
				$hash = md5($user->salt . $password);
				if ($user->password == $hash) {
					return $user;
				}
			}
		}


		throw new \Nette\Security\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
	}



	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return md5($password . str_repeat('*enter any random salt here*', 10));
	}

}
