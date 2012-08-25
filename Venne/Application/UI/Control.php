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
use Nette\Utils\Strings;
use Venne\Templating\ITemplateConfigurator;
use Venne\Security\IComponentVerifier;

/**
 * Description of Control
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Control extends \Nette\Application\UI\Control
{


	/** @var ITemplateConfigurator */
	protected $templateConfigurator;

	/** @var bool */
	private $startupCheck;


	protected function startup()
	{
		$this->startupCheck = TRUE;
	}


	/**
	 * @param ITemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(ITemplateConfigurator $configurator = NULL)
	{
		$this->templateConfigurator = $configurator;
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

		return $template;
	}


	/**
	 * @param string $name
	 *
	 * @return \Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$method = 'createComponent' . ucfirst($name);
		if (method_exists($this, $method)) {
			$this->checkRequirements($this->getReflection()->getMethod($method));
		}

		return parent::createComponent($name);
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


	/**
	 * Checks authorization.
	 *
	 * @return void
	 */
	public function checkRequirements($element)
	{
		if (!$this->getPresenter()->getUser()->isAllowed($this)) {
			throw new ForbiddenRequestException;
		}
	}


	/**
	 * Render control.
	 */
	public function render()
	{
		if (!$this->template->getFile()) {
			$this->template->setFile($this->formatTemplateFile());
		}

		$this->template->render();
	}
}

