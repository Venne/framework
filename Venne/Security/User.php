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
use Nette\Security\AuthenticationException;

/**
 * User authentication and authorization.
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 * @property-read \SystemContainer|\Nette\DI\IContainer $context
 */
class User extends \Nette\Security\User {


	/** @var \Nette\DI\IContainer */
	private $context;

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $loginRepository;


	/** @var \App\CoreModule\Entities\LoginEntity */
	protected $login;



	/**
	 * @param \Nette\Security\IUserStorage $storage
	 * @param \Nette\DI\IContainer $context
	 */
	public function __construct(\Nette\Security\IUserStorage $storage, \Nette\DI\IContainer $context)
	{
		$this->context = $context;
		parent::__construct($storage, $context);

		if($this->context->createCheckConnection()){
			$this->loginRepository = $context->core->loginRepository;
			$this->login = $this->getLogin();

			/* generate session_id */
			if (!$this->login) {
				$user = $this->isLoggedIn() ? ($this->identity->name == "admin" ? NULL : $this->identity->id) : NULL;
				$this->login = $this->loginRepository->createNew(array($user, $this->context->session->getId()));
				$this->loginRepository->save($this->login);
				$this->invalidatePermissions();
			}

			/* reload permissions */
			else if (!$this->login->valid) {
				$this->invalidatePermissions();
			}
		}
	}



	/**
	 * Invalidate permission.
	 */
	protected function invalidatePermissions()
	{
		$session = $this->context->session->getSection(\App\CoreModule\AuthorizatorFactory::SESSION_SECTION);
		$session->remove();
		$this->login->valid = 1;
		$this->loginRepository->save($this->login);
	}



	/**
	 * Get login entity.
	 *
	 * @return \App\CoreModule\Entities\LoginEntity|NULL
	 */
	protected function getLogin()
	{
		$user = $this->isLoggedIn() ? ($this->identity->name == "admin" ? NULL : $this->identity->id) : NULL;
		$login = $this->loginRepository->findOneBy(array("user" => $user, "sessionId" => NULL));
		$sessionId = $this->context->session->getId();

		return $this->loginRepository->findOneBy(array("user" => $user, "sessionId" => $sessionId));
	}


}
