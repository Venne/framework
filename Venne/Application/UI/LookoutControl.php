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

/**
 * Description of Control
 *
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LookoutControl extends Control
{


	/** @var string */
	protected $view;

	/** @var array */
	protected $params;






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
	 *
	 * @param \Nette\Application\IPresenter $presenter
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);

		if ($this->presenter->context->hasService('venne_templateConfigurator')) {
			$this->setTemplateConfigurator($this->presenter->context->venne->templateConfigurator);
		}

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
	protected function formatTemplateFiles($view)
	{
		$theme = $this->presenter->context->parameters["venneModeFront"] ? $this->presenter->context->parameters["website"]["theme"] : "admin";
		$dir = dirname($this->getReflection()->getFileName());
		$list = array($this->presenter->context->parameters["wwwDir"] . "/themes/" . $theme . "/controls/" . ucfirst($this->name) . "/template.latte", $dir . "/$view.latte");
		return $list;
	}



	/**
	 * Format component template file
	 *
	 * @param string
	 * @return string
	 * @throws \Nette\InvalidStateException
	 */
	protected function formatTemplateFile($view)
	{
		$files = $this->formatTemplateFiles($view);
		foreach ($files as $file) {
			if (file_exists($file)) {
				return $file;
			}
		}

		throw new \Nette\InvalidStateException("No template files found for view '$view'");
	}



	public function render($param = NULL, $type = NULL)
	{
		$this->view = $this->view ? : "default";
		$viewMethod = "view" . ucfirst($this->view);
		$this->params = $this->params ? : func_get_args();

		call_user_func_array(array($this, 'beforeRender'), $this->params);

		ob_start();
		if (method_exists($this, $viewMethod)) {
			call_user_func_array(array($this, $viewMethod), $this->params);
		}

		$this->template->setFile($this->formatTemplateFile(lcfirst($this->view)));

		$output = ob_get_clean();
		$output = (string)$this->template;
		echo $output;

		call_user_func_array(array($this, 'afterRender'), $this->params);
	}



	protected function beforeRender()
	{

	}



	protected function afterRender()
	{

	}



	public function __call($name, $args)
	{
		if (Strings::startsWith($name, "render")) {
			$this->view = substr($name, 6);
			$this->params = $args;

			return call_user_func(array($this, 'render'));
		}
		return parent::__call($name, $args);
	}

}

