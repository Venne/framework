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
use Nette\Security\IAuthorizator;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class User extends \Nette\Security\User
{



	/**
	 * Has a user effective access to the Resource?
	 * If $resource is NULL, then the query applies to all resources.
	 * @param  string|Presenter  resource
	 * @param  string  privilege
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		if ($resource instanceof Presenter) {
			return $this->isPresenterAllowed($resource);
		}

		if ($resource instanceof Control) {
			return $this->isControlAllowed($resource);
		}

		return parent::isAllowed($resource, $privilege);
	}



	/**
	 * @param Presenter $presenter
	 * @return boolean 
	 */
	protected function isPresenterAllowed(Presenter $presenter)
	{
		$element = $presenter->getReflection();

		// is not secured
		if (!$element->hasAnnotation("secured")) {
			return true;
		}

		// startup
		$secured = $element->getAnnotation("secured");
		$resource = isset($secured["resource"]) ? $secured["resource"] : $element->getName();
		if (!parent::isAllowed($resource)) {
			return false;
		}


		// methods
		$methods = array(
			"action" . ucfirst($presenter->getAction()),
			"handle" . ucfirst($presenter->signal[0]),
		);
		foreach ($methods as $method) {
			if ($element->hasMethod($method) && $element->getMethod($method)->hasAnnotation("resource")) {
				$resource = $element->getMethod($method)->getAnnotation("resource");
				if (!parent::isAllowed($resource)) {
					return false;
				}
			}
		}

		return true;
	}



	protected function isControlAllowed(Control $control)
	{
		return true;
	}

}
