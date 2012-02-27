<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\ComponentVerifiers;

use Venne;
use Venne\Security\IComponentVerifier;
use Nette\Security\User;
use Nette\Reflection\ClassType;
use Nette\Application\Application;
use Venne\Application\UI\Presenter;


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ComponentVerifier extends \Nette\Object implements IComponentVerifier
{


	/** @var User */
	protected $user;

	/** @var Application */
	protected $application;



	/**
	 * @param \Nette\Security\User $user
	 * @param \Nette\Application\Application $application
	 */
	function __construct(User $user, Application $application)
	{
		$this->user = $user;
		$this->application = $application;
	}



	/**
	 * @param \Reflector $element
	 * @return bool
	 */
	public function isAllowed(\Reflector $element)
	{
		if ($element instanceof ClassType && $element->is('\Venne\Application\UI\Presenter')) {
			return $this->validatePresenter($element);
		}
		return true;
	}



	/**
	 * @param \Reflector $element
	 * @return bool
	 */
	protected function validatePresenter($element)
	{
		if (!$element->hasAnnotation("secured")) {
			return true;
		}

		// startup
		$secured = $element->getAnnotation("secured");
		$resource = isset($secured["resource"]) ? $secured["resource"] : $element->getName();
		$resource = substr($resource, 0, 4) == "App\\" ? substr($resource, 4) : $resource;
		if (!$this->user->isAllowed($resource)) {
			return false;
		}


		// methods
		$presenter = $this->application->getPresenter();
		$methods = array(
			"action" . ucfirst($presenter->getAction()),
			"handle" . ucfirst($presenter->signal[0]),
		);
		foreach ($methods as $method) {
			if ($element->hasMethod($method) && $element->getMethod($method)->hasAnnotation("resource")) {
				$resource = $element->getMethod($method)->getAnnotation("resource");
				if (!$this->user->isAllowed($resource)) {
					return false;
				}
			}
		}


		return true;
	}

}
