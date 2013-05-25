<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security;

use Venne;
use Nette;
use Nette\Security\AuthenticationException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{


	/** @var string */
	protected $adminLogin;

	/** @var string */
	protected $adminPassword;


	/**
	 * @param $adminLogin
	 * @param $adminPassword
	 */
	public function __construct($adminLogin, $adminPassword)
	{
		$this->adminLogin = $adminLogin;
		$this->adminPassword = $adminPassword;
	}


	/**
	 * Performs an authentication
	 *
	 * @param  array
	 * @return \Nette\Security\Identity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		if (!$username OR !$password) {
			throw new AuthenticationException('The username or password is not filled.', self::INVALID_CREDENTIAL);
		}

		if ($this->adminLogin != $username) {
			throw new AuthenticationException('The username is incorrect.', self::INVALID_CREDENTIAL);
		}

		if ($this->adminPassword != $password) {
			throw new AuthenticationException('The password is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		return new \Nette\Security\Identity($username, array('admin'));
	}
}
