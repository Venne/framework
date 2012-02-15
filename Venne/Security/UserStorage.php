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
use Nette\Http\Session;
use App\CoreModule\Entities\LoginEntity;
use App\CoreModule\Entities\UserEntity;
use Nette\Security\IIdentity;
use Venne\Doctrine\ORM\BaseRepository;


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserStorage extends \Nette\Http\UserStorage
{


	/** @var BaseRepository */
	protected $repository;

	/** @var Session */
	private $sessionHandler;


	/** @var LoginEntity */
	protected $login = false;



	public function  __construct(Session $sessionHandler, BaseRepository $loginRepository)
	{
		$this->sessionHandler = $sessionHandler;
		$this->repository = $loginRepository;
		parent::__construct($sessionHandler);
	}



	public function setAuthenticated($state)
	{
		parent::setAuthenticated($state);

		if ($state) {
			$identity = $this->getIdentity();

			$login = $this->repository->createNew(array($identity->id != -1 ? $identity : NULL, $this->sessionHandler->getId()));
			$this->repository->save($login);
		}

		return $this;
	}



	public function isAuthenticated()
	{
		$ret = parent::isAuthenticated();
		if (!$ret) {
			return false;
		}

		$login = $this->getLogin();
		return (bool)$login;
	}



	/**
	 * @return \App\CoreModule\Entities\LoginEntity|bool
	 */
	protected function getLogin()
	{
		if ($this->login === false) {
			$identity = $this->getIdentity();
			if (!$identity) {
				return NULL;
			}
			$id = $identity->id != -1 ? $identity->id : NULL;

			$this->login = $this->repository->findOneBy(array("user" => $id, "sessionId" => $this->sessionHandler->getId()));

			if ($this->login && $this->login->reload) {
				$this->login->reload = false;
				$this->invalidatePermissions();
				$this->repository->save($this->login);
			}
		}

		return $this->login;
	}



	/**
	 * Invalidate permission.
	 */
	protected function invalidatePermissions()
	{
		$session = $this->sessionHandler->getSection(\App\CoreModule\AuthorizatorFactory::SESSION_SECTION);
		$session->remove();
		$this->login->reload = 0;
		$this->repository->save($this->login);
	}


}
