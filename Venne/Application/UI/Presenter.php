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
use Nette\Application\UI\PresenterComponentReflection;
use Nette\DI\Container;
use Venne\Security\IControlVerifier;
use Venne\Templating\ITemplateConfigurator;
use Venne\Widget\WidgetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Presenter extends \Nette\Application\UI\Presenter
{

	/** @var ITemplateConfigurator */
	protected $templateConfigurator;

	/** @var IControlVerifier */
	protected $controlVerifier;

	/** @var WidgetManager */
	protected $widgetManager;

	/** @var array */
	protected $_flashes = array();


	public function __construct()
	{
		$context = new \Nette\DI\Container;
		$context->parameters['productionMode'] = TRUE;
		parent::__construct($context);
	}


	/**
	 * @param Container $context
	 */
	public function injectContext(Container $context)
	{
		parent::__construct($context);
	}


	/**
	 * @param WidgetManager $widgetManager
	 */
	public function injectWidgetManager(WidgetManager $widgetManager)
	{
		$this->widgetManager = $widgetManager;
	}


	/**
	 * @param IControlVerifier $controlVerifier
	 */
	public function injectControlVerifier(IControlVerifier $controlVerifier = NULL)
	{
		$this->controlVerifier = $controlVerifier;
	}


	/**
	 * @param ITemplateConfigurator $configurator
	 */
	public function injectTemplateConfigurator(ITemplateConfigurator $configurator = NULL)
	{
		$this->templateConfigurator = $configurator;
	}


	public function getTemplateConfigurator()
	{
		return $this->templateConfigurator;
	}


	/**
	 * @return \Venne\Widget\WidgetManager
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
	 * Saves the message to template, that can be displayed after redirect.
	 * @param  string
	 * @param  string
	 * @return \stdClass
	 */
	public function flashMessage($message, $type = 'info', $withoutSession = FALSE)
	{
		if ($withoutSession) {
			$this->_flashes[] = $flash = (object)array(
				'message' => $message,
				'type' => $type,
			);
		} else {
			$flash = parent::flashMessage($message, $type);
		}

		$id = $this->getParameterId('flash');
		$messages = $this->getPresenter()->getFlashSession()->$id;
		$this->getTemplate()->flashes = array_merge((array)$messages, $this->_flashes);

		return $flash;
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
		if ($this->widgetManager->hasWidget($name)) {
			return $this->widgetManager->getWidget($name)->invoke();
		}

		throw new \Nette\InvalidArgumentException("Component or widget with name '$name' does not exist.");
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

			$presenterFactory = $this->getApplication()->getPresenterFactory();
			$class = $presenterFactory->getPresenterClass($link);
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


	/**
	 * Redirect to another presenter, action or signal in AJAX mode.
	 * @param  string   destination in format "[[module:]presenter:]view" or "signal!"
	 * @param  array|mixed
	 */
	public function ajaxRedirect($destination = NULL, $args = array())
	{
		if (!$this->isAjax()) {
			$this->redirect($destination, $args);
		}

		$args['_redirectByAjax'] = TRUE;
		$this->forward($destination, $args);
	}


	protected function afterRender()
	{
		parent::afterRender();

		if ($this->getParameter('_redirectByAjax', FALSE)) {
			$this->payload->url = $this->link('this');
		}
	}
}
