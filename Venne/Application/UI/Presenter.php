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

use Venne;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\Application\ForbiddenRequestException;
use Venne\Templating\ITemplateConfigurator;
use Venne\Widget\WidgetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Presenter extends \Nette\Application\UI\Presenter
{


	/** @var ITemplateConfigurator */
	protected $templateConfigurator;

	/** @var WidgetManager */
	protected $widgetManager;

	/** @var \Nette\DI\Container */
	private $context;


	public function __construct()
	{
		$container = new \Nette\DI\Container;
		$container->parameters["productionMode"] = true;
		parent::__construct($container);
	}


	final public function injectContext(\Nette\DI\Container $context)
	{
		parent::__construct($context);

		// template configurator
		if ($context->hasService('venne.templateConfigurator')) {
			$this->setTemplateConfigurator($context->venne->templateConfigurator);
		}
	}


	public function injectWidgetManager(WidgetManager $widgetManager)
	{
		$this->widgetManager = $widgetManager;
	}


	/**
	 * @param ITemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(ITemplateConfigurator $configurator = NULL)
	{
		$this->templateConfigurator = $configurator;
	}


	public function getTemplateConfigurator()
	{
		return $this->templateConfigurator;
	}


	/**
	 * Checks authorization.
	 *
	 * @return void
	 */
	public function checkRequirements($element)
	{
		if (!$this->getUser()->isAllowed($this)) {
			throw new ForbiddenRequestException;
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
	public function createComponent($name)
	{
		// parent
		if (($control = parent::createComponent($name)) == true) {
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
	public function isAllowed($destination)
	{
		if($destination == 'this'){
			$class = get_class($this);
		} elseif (substr($destination, -1, 1) == '!') {
			$class = get_class($this);
		}  else {
			if(substr($destination, 0, 1) === ':') {
				$link = substr($destination, 1);
				$link = substr($link, 0, strrpos($link, ':'));
			} else {
				$link = substr($this->name, 0, strrpos($this->name, ':'));
				$link = $link . ($link ? ':' : '') . substr($destination, 0, strrpos($destination, ':'));
			}

			$presenterFactory = $this->getApplication()->getPresenterFactory();
			$class = $presenterFactory->getPresenterClass($link);
		}

		return $this->user->isAllowed($class);
	}
}

