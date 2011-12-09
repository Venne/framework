<?php

namespace App\CoreModule;

use Nette\Environment;

class SitemapPresenter extends \Venne\Application\UI\FrontPresenter
{
	
	public $sitemap;


	public function startup()
	{
		parent::startup();
		\Nette\Diagnostics\Debugger::$bar = false;
	}
	
	public function renderSitemap()
	{
		$this->template->xml = $this->getContext()->{$this->getParam("sitemap")}->getSitemap($this->getContext()->params["modules"][$this->getParam("sitemap") . "Module"]["sitemapPriority"]);
	}

	
	public function renderRobots()
	{
		$this->template->modules = $this->getContext()->cms->moduleManager->getSitemapModules();
		$this->template->path = str_replace("robots.txt", "", $this->getHttpRequest()->getUrl()->getAbsoluteUrl());
	}

	
}