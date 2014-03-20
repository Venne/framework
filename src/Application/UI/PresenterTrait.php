<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\IPresenterFactory;
use Nette\InvalidArgumentException;
use Venne\Security\IControlVerifier;
use Venne\Templating\ITemplateConfigurator;
use Venne\Widget\WidgetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait PresenterTrait
{

	/** @var ITemplateConfigurator|NULL */
	private $templateConfigurator;

	/** @var IControlVerifier */
	private $controlVerifier;

	/** @var WidgetManager|NULL */
	private $widgetManager;

	/** @var IPresenterFactory|NULL */
	private $presenterFactory;


	/**
	 * @param IPresenterFactory $presenterFactory
	 * @param WidgetManager $widgetManager
	 * @param IControlVerifier $controlVerifier
	 * @param ITemplateConfigurator $configurator
	 */
	public function injectVenneControl(
		IPresenterFactory $presenterFactory,
		WidgetManager $widgetManager = NULL,
		IControlVerifier $controlVerifier = NULL,
		ITemplateConfigurator $configurator = NULL
	)
	{
		$this->widgetManager = $widgetManager;
		$this->presenterFactory = $presenterFactory;
		$this->controlVerifier = $controlVerifier;
		$this->templateConfigurator = $configurator;
	}


	/**
	 * @return NULL|ITemplateConfigurator
	 */
	public function getTemplateConfigurator()
	{
		return $this->templateConfigurator;
	}


	/**
	 * @return NULL|WidgetManager
	 */
	public function getWidgetManager()
	{
		return $this->widgetManager;
	}


	/**
	 * Checks authorization.
	 *
	 * @return void
	 */
	public function checkRequirements($element)
	{
		if ($this->controlVerifier) {
			$this->controlVerifier->checkRequirements($element);
		}
	}


	/**
	 * @param string|null $class
	 *
	 * @return \Nette\Templating\Template
	 */
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->configure($template);
		}

		return $template;
	}


	/**
	 * @param \Nette\Templating\Template $template
	 *
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->prepareFilters($template);
		} else {
			$template->registerFilter(new \Nette\Latte\Engine);
		}
	}


	/**
	 * Component factory. Delegates the creation of components to a createComponent<Name> method.
	 *
	 * @param  string      component name
	 * @return IComponent  the created component (optionally)
	 */
	protected function createComponent($name)
	{
		// parent
		if (($control = parent::createComponent($name)) == TRUE) {
			return $control;
		}

		// widget from widgetManager
		if ($this->widgetManager && $this->widgetManager->hasWidget($name)) {
			return $this->widgetManager->getWidget($name);
		}

		throw new InvalidArgumentException("Component or widget with name '$name' does not exist.");
	}


	/**
	 * @param type $destination
	 */
	public function isAllowed($resource = NULL, $privilege = NULL)
	{
		return $this->getUser()->isAllowed($resource, $privilege);
	}


	/**
	 * @param  string   destination in format "[[module:]presenter:]action" or "signal!" or "this"
	 * @param  array|mixed
	 * @return bool
	 */
	public function isAuthorized($destination)
	{
		if ($destination == 'this') {
			$class = get_class($this);
			$action = $this->action;
		} elseif (substr($destination, -1, 1) == '!') {
			$class = get_class($this);
			$action = $this->action;
			$do = substr($destination, 0, -1);
		} elseif (ctype_lower(substr($destination, 0, 1))) {
			$class = get_class($this);
			$action = $destination;
		} else {
			if (substr($destination, 0, 1) === ':') {
				$link = substr($destination, 1);
				$link = substr($link, 0, strrpos($link, ':'));
				$action = substr($destination, strrpos($destination, ':') + 1);
			} else {
				$link = substr($this->name, 0, strrpos($this->name, ':'));
				$link = $link . ($link ? ':' : '') . substr($destination, 0, strrpos($destination, ':'));
				$action = substr($destination, strrpos($destination, ':') + 1);
			}
			$action = $action ? : 'default';

			$class = $this->presenterFactory->getPresenterClass($link);
		}

		$schema = $this->controlVerifier->getControlVerifierReader()->getSchema($class);

		if (isset($schema['action' . ucfirst($action)])) {
			$classReflection = new \Nette\Reflection\ClassType($class);
			$method = $classReflection->getMethod('action' . ucfirst($action));

			try {
				$this->controlVerifier->checkRequirements($method);
			} catch (ForbiddenRequestException $e) {
				return FALSE;
			}
		}

		if (isset($do) && isset($schema['handle' . ucfirst($do)])) {
			$classReflection = new \Nette\Reflection\ClassType($class);
			$method = $classReflection->getMethod('handle' . ucfirst($do));

			try {
				$this->controlVerifier->checkRequirements($method);
			} catch (ForbiddenRequestException $e) {
				return FALSE;
			}
		}

		return TRUE;
	}

}
