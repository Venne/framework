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

use Nette\Templating\FileTemplate;
use Nette\Utils\Strings;
use Venne\Security\IComponentVerifier;
use Venne\Templating\ITemplateConfigurator;

/**
 * Description of Control
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read Presenter $presenter
 * @method Presenter getPresenter()
 */
class Control extends \Nette\Application\UI\Control
{

	/** @var ITemplateConfigurator */
	protected $templateConfigurator;

	/** @var bool */
	private $startupCheck;


	/**
	 * @param ITemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(ITemplateConfigurator $configurator = NULL)
	{
		$this->templateConfigurator = $configurator;
	}


	/**
	 * Descendant can override this method to customize template compile-time filters.
	 *
	 * @param  Nette\Templating\Template
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


	protected function startup()
	{
		$this->startupCheck = TRUE;
	}


	/**
	 * @param string|NULL $class
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->configure($template);
		}

		if ($template instanceof FileTemplate) {
			$template->setFile($this->formatTemplateFile());
		}

		return $template;
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
		if ($this->presenter->widgetManager->hasWidget($name)) {
			return $this->presenter->widgetManager->getWidget($name)->invoke();
		}

		throw new \Nette\InvalidArgumentException("Component or widget with name '$name' does not exist.");
	}


	/**
	 * @param \Nette\Application\IPresenter $presenter
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);

		// template configurator
		if ($this->presenter->context->hasService('venne.templateConfigurator')) {
			$this->setTemplateConfigurator($this->presenter->context->venne->templateConfigurator);
		}

		// startup check
		$this->startup();
		if (!$this->startupCheck) {
			$class = $this->getReflection()->getMethod('startup')->getDeclaringClass()->getName();
			throw new \Nette\InvalidStateException("Method $class::startup() or its descendant doesn't call parent::startup().");
		}
	}


	/**
	 * Formats component template files
	 *
	 * @param string
	 * @return array
	 */
	protected function formatTemplateFiles()
	{
		$refl = $this->getReflection();
		$list = array(
			dirname($refl->getFileName()) . '/' . $refl->getShortName() . '.latte',
		);
		return $list;
	}


	/**
	 * Format component template file
	 *
	 * @param string
	 * @return string
	 * @throws \Nette\InvalidStateException
	 */
	protected function formatTemplateFile()
	{
		$files = $this->formatTemplateFiles();
		foreach ($files as $file) {
			if (file_exists($file)) {
				return $file;
			}
		}

		throw new \Nette\InvalidStateException("No template files found");
	}
}

