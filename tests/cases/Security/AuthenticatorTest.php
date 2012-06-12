<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tests\Security;

use Venne;
use Venne\Tests\TestCase;

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

		$this->assertInstanceOf("Nette\Security\Identity", $identity);
		$this->assertEquals("foo", $identity->id);
		$this->assertCount(1, $identity->roles);
		$this->assertEquals("admin", $identity->roles[0]);
	}
	
	
	/**
     * @expectedException Nette\Security\AuthenticationException
     */
    public function testAuthenticateException()
    {
		$this->authenticator->authenticate(array("foo", "bar2"));
    }

}

