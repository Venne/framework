<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Presenters;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BasePresenter extends \Venne\Application\UI\Presenter
{

	/** @persistent */
	public $lang;

	/** @var array of array */
	protected $paths = array();


	public function __construct(\Nette\DI\Container $container)
	{
		\Venne\Panels\Stopwatch::start();
		parent::__construct($container);
	}


	/**
	 * @return Events\EventArgs
	 */
	protected function getEventArgs()
	{
		$args = new \Venne\Application\UI\Events\EventArgs();
		$args->setPresenter($this);
		return $args;
	}


	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();


		// Startup event
		$this->context->eventManager->dispatchEvent(\Venne\Application\UI\Events\Events::onStartup, $this->getEventArgs());


		// Language
		if (count($this->context->parameters["website"]["languages"]) > 1) {
			if (!$this->lang && !$this->getParameter("lang")) {
				$this->lang = $this->getDefaultLanguageAlias();
			}
		} else {
			$this->lang = $this->context->parameters["website"]["defaultLanguage"];
		}


		// Stopwatch
		\Venne\Panels\Stopwatch::stop("base startup");
		\Venne\Panels\Stopwatch::start();
	}


	/**
	 * @return string
	 */
	protected function getDefaultLanguageAlias()
	{
		$httpRequest = $this->context->httpRequest;

		$lang = $httpRequest->getCookie('lang');
		if (!$lang) {
			$lang = $httpRequest->detectLanguage($this->context->parameters["website"]["languages"]);
			if (!$lang) {
				$lang = $this->context->parameters["website"]["defaultLanguage"];
			}
		}
		return $lang;
	}



	/**
	 * Redirect to other language.
	 *
	 * @param string $alias
	 */
	public function handleChangeLanguage($alias)
	{
		$this->redirect("this", array("lang" => $alias));
	}



	/**
	 * Get module
	 *
	 * @return \Venne\Module\IModule
	 */
	public function getModule()
	{
		return $this->context->{$this->getModuleName() . "Theme"};
	}



	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		return lcfirst(substr($this->name, 0, strpos($this->name, ":")));
	}


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		// Stopwatch
		\Venne\Panels\Stopwatch::stop("module startup and action");
		\Venne\Panels\Stopwatch::start();


		parent::beforeRender();
	}



	/**
	 * @param  Nette\Application\IResponse  optional catched exception
	 * @return void
	 */
	public function shutdown($response)
	{
		parent::shutdown($response);
		\Venne\Panels\Stopwatch::stop("template render");
		\Venne\Panels\Stopwatch::start();
	}



	/**
	 * @param string $name
	 * @param string $url
	 */
	public function addPath($name, $url)
	{
		$this->paths[] = array("name" => $name, "url" => $url);
	}



	/**
	 * @return array
	 */
	public function getPaths()
	{
		return $this->paths;
	}



	/**
	 * @return \App\CoreModule\Components\Head\HeadControl
	 */
	public function createComponentHead()
	{
		$head = $this->context->core->createHeadControl();
		return $head;
	}



	/**
	 * @return \App\CoreModule\Components\Panel\PanelControl
	 */
	public function createComponentVennePanel()
	{
		$head = $this->context->core->createPanelControl();
		return $head;
	}

}
