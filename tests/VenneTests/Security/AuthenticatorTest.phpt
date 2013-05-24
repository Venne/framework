<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Security;

use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AuthenticatorTest extends TestCase
{


	/** @var Venne\Security\Authenticator */
	protected $authenticator;


	public function setup()
	{
		$this->authenticator = new \Venne\Security\Authenticator("foo", "bar");
	}


	public function testAuthenticate()
	{
		$identity = $this->authenticator->authenticate(array("foo", "bar"));

		Assert::type('Nette\Security\Identity', $identity);
		Assert::equal("foo", $identity->id);
		Assert::equal(1, count($identity->roles));
		Assert::equal("admin", $identity->roles[0]);
	}


	public function testAuthenticateException()
	{
		$authenticator = $this->authenticator;
		Assert::exception(function () use ($authenticator) {
			$authenticator->authenticate(array("foo", "bar2"));
		}, 'Nette\Security\AuthenticationException');
	}

}

\run(new AuthenticatorTest);
