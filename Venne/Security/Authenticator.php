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



	function __construct($adminLogin, $adminPassword)
	{
		$this->adminLogin = $adminLogin;
		$this->adminPassword = $adminPassword;
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
		list($username, $password) = $credentials;

		if (!$username OR !$password)
			throw new AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);

		if ($this->adminLogin == $username && $this->adminPassword == $password) {
			return new \Nette\Security\Identity($username, array("admin"));
		}

		throw new AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
	}

}
