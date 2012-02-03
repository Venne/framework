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
use Venne\Application\Routers\PageRoute;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PagePresenter extends FrontPresenter
{


	/** @persistent */
	public $url = "";

	/** @var \App\CoreModule\Entities\BasePageEntity */
	public $page;



	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();
		$entity = $this->getParameter("page");


		/* generate path */
		$page = $entity;
		while ($page) {
			$this->addPath($page->title, $this->link(":" . PageRoute::DEFAULT_MODULE . ":" . PageRoute::DEFAULT_PRESENTER . ":" . PageRoute::DEFAULT_ACTION, array("url" => $page->url)));
			$page = $page->parent;
		}


		/* load page module entity */
		$this->page = $this->loadPage();
		if (!$this->page) {
			throw new \Nette\Application\BadRequestException;
		}
		$this->url = $this->page->url;
	}



	/**
	 * Load page.
	 *
	 * @return \App\CoreModule\Entities\BasePageEntity
	 */
	protected function loadPage()
	{
		$entity = $this->getParameter("page");

		$module = $this->getModuleName();
		$presenter = lcfirst(substr($this->getName(), strrpos($this->getName(), ":") + 1));
		$repository = $presenter == "default" ? $module : $presenter;
		$page = $this->context->{$module}->{$repository . "Repository"}->findOneBy(array("page" => $entity->id));
		if (!$page && !$this->url) {
			$page = $this->context->blog->blogRepository->findOneBy(array("mainPage" => true));
		}
		return $page;
	}



	/**
	 * Redirect to other language.
	 *
	 * @param string $alias
	 */
	public function handleChangeLanguage($alias)
	{
		$page = $this->page->getPageWithLanguageAlias($alias);
		$this->redirect("this", array("lang" => $alias, "url" => ($page ? $page->url : "")));
	}



	/**
	 * Get layout path.
	 *
	 * @return null|string
	 */
	protected function getLayoutPath()
	{
		if (!$this->page->layoutFile) {
			return NULL;
		}

		$pos = strpos($this->page->layoutFile, "/");
		$module = lcfirst(substr($this->page->layoutFile, 1, $pos - 1));

		if (!$this->context->hasService($module)) {
			return NULL;
		}

		return $this->context->$module->getPath() . "/layouts/" . substr($this->page->layoutFile, $pos + 1);
	}



	/**
	 * Formats layout template file names.
	 *
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$path = $this->getLayoutPath();
		return ($path ? array($path . "/@layout.latte") : parent::formatLayoutTemplateFiles());
	}



	/**
	 * Formats view template file names.
	 *
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$ret = parent::formatTemplateFiles();
		$name = $this->getName();
		$presenter = str_replace(":", "/", $this->name);

		$path = $this->getLayoutPath();
		if ($path) {
			$ret = array_merge(array(
				"$path/$presenter/$this->view.latte",
				"$path/$presenter.$this->view.latte",
			), $ret);
		}
		return $ret;
	}



	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->entity = $this->page;
		$this->setTitle($this->page->title);
		$this->setRobots($this->page->robots);
	}

}

