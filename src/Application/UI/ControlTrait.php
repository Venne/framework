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

use Nette\InvalidStateException;
use Nette\Templating\FileTemplate;
use Venne\Security\IComponentVerifier;
use Venne\Templating\ITemplateConfigurator;
use Venne\Widget\WidgetManager;

/**
 * Description of Control
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read Presenter $presenter
 * @method Presenter getPresenter()
 */
trait ControlTrait
{

	/** @var ITemplateConfigurator|NULL */
	private $templateConfigurator;

	/** @var WidgetManager|NULL */
	private $widgetManager;


	/**
	 * @param WidgetManager $widgetManager
	 * @param ITemplateConfigurator $configurator
	 */
	public function injectVenneControl(
		WidgetManager $widgetManager = NULL,
		ITemplateConfigurator $configurator = NULL
	)
	{
		$this->widgetManager = $widgetManager;
		$this->templateConfigurator = $configurator;
	}


	/**
	 * @return \Venne\Templating\ITemplateConfigurator
	 */
	public function getTemplateConfigurator()
	{
		return $this->templateConfigurator;
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
		if ($this->widgetManager && $this->widgetManager->hasWidget($name)) {
			return $this->widgetManager->getWidget($name);
		}

		throw new \Nette\InvalidArgumentException("Component or widget with name '$name' does not exist.");
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

