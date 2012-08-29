<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\ControlVerifiers;

use Venne;
use Nette\Object;
use Nette\Application\ForbiddenRequestException;
use Venne\Security\IControlVerifier;
use Venne\Security\IControlVerifierReader;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ControlVerifier extends Object implements IControlVerifier
{


	/** @var User */
	protected $user;

	/** @var IControlVerifierReader */
	protected $reader;

	/** @var array */
	protected $_annotationSchema = array();

	/** @var array */
	protected $_presenterAllowed = array();

	/** @var array */
	protected $_methodAllowed = array();


	/**
	 * @param \Nette\Security\User $user
	 */
	public function __construct(\Nette\Security\User $user, IControlVerifierReader $reader)
	{
		$this->user = $user;
		$this->reader = $reader;
	}


	/**
	 * @param IControlVerifierReader $reader
	 */
	public function setControlVerifierReader(IControlVerifierReader $reader)
	{
		$this->reader = $reader;
	}


	/**
	 * @return IControlVerifierReader
	 */
	public function getControlVerifierReader()
	{
		return $this->reader;
	}


	/**
	 * @param $element
	 * @return bool|mixed
	 */
	public function checkRequirements($element)
	{
		if ($element instanceof \Nette\Reflection\Method) {
			return $this->checkMethod($element);
		}

		if ($element instanceof \Nette\Application\UI\PresenterComponentReflection) {
			return $this->checkPresenter($element);
		}

		throw new \Nette\InvalidArgumentException("Argument must be instance of 'Nette\Reflection\Method' OR 'Nette\Application\UI\PresenterComponentReflection'");
	}


	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 * @return bool
	 */
	protected function isPresenterAllowedCached(\Nette\Application\UI\PresenterComponentReflection $element)
	{
		if (!array_key_exists($element->name, $this->_presenterAllowed)) {
			$this->_presenterAllowed[$element->name] = $this->isPresenterAllowed($element);
		}

		return $this->_presenterAllowed[$element->name];
	}


	/**
	 * @param \Nette\Reflection\Method $element
	 * @return mixed
	 */
	protected function isMethodAllowedCached(\Nette\Reflection\Method $element)
	{
		if (!array_key_exists($element->name, $this->_methodAllowed)) {
			$this->_methodAllowed[$element->name] = $this->isMethodAllowed($element);
		}

		return $this->_methodAllowed[$element->name];
	}


	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 * @return bool
	 */
	protected function checkPresenter(\Nette\Application\UI\PresenterComponentReflection $element)
	{
		return true;
	}


	/**
	 * @param \Nette\Reflection\Method $element
	 * @return bool
	 */
	protected function checkMethod(\Nette\Reflection\Method $element)
	{
		$class = $element->class;
		$name = $element->name;
		$schema = $this->reader->getSchema($class);

		// resource & privilege
		if (isset($schema[$name]['resource']) && $schema[$name]['resource']) {
			if (!$this->user->isAllowed($schema[$name]['resource'], $schema[$name]['privilege'])) {
				throw new ForbiddenRequestException("Access denied for resource: {$schema[$name]['resource']}" . ($schema[$name]['privilege'] ? " and privilege: {$schema[$name]['privilege']}" : ''));
			}
		}

		// roles
		if (isset($schema[$name]['roles']) && count($schema[$name]['roles']) > 0) {
			$userRoles = $this->user->getRoles();
			$roles = $schema[$name]['roles'];

			if (count(array_intersect($userRoles, $roles)) == 0) {
				throw new ForbiddenRequestException("Access denied for your roles: '" . implode(', ', $userRoles) . "'. Require one of: '" . implode(', ', $roles) . "'");
			}
		}

		// users
		if (isset($schema[$name]['users']) && count($schema[$name]['users']) > 0) {
			$users = $schema[$name]['users'];

			if (!in_array($this->user->getId(), $users)) {
				throw new ForbiddenRequestException("Access denied for your username: '{$this->user->getId()}'. Require: '" . implode(', ', $users) . "'");
			}
		}
	}
}
