<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\InstallationModule\AdminModule;

use \Nette\Application\UI\Form;

/**
 * @author Josef Kříž
 */
class DefaultPresenter extends \Venne\Application\UI\InstallationPresenter {



	public function startup()
	{

		parent::startup();

		/*
		 * Extensions
		 */
		$modules = array("gd", "gettext", "iconv", "json", "pdo", "pdo_mysql");
		foreach ($modules as $item) {
			if (!extension_loaded($item)) {
				$this->flashMessage("Module " . $item . " is not enabled.", "warning");
			}
		}

		/*
		 * Writable
		 */
		$paths = array(
			$this->getContext()->params["wwwDir"] . "/public/",
			$this->getContext()->params["dataDir"] . "/",
			$this->getContext()->params["themesDir"] . "/",
			$this->getContext()->params["appDir"] . "/",
			$this->getContext()->params["flagsDir"]
		);
		foreach ($paths as $item) {
			if (!is_writable($item)) {
				$this->flashMessage("Path " . $item . " is not writable.", "warning");
			}
		}

		if (file_exists($this->getContext()->params["flagsDir"] . "/installed")) {
			$this->setView("finish");
		} else {
			$this->setView("default");
			if ($this->context->params["admin"]["password"]) {
				$this->setView("database");
				if ($this->context->params["database"]["dbname"]) {
					$this->setView("website");

					if (count($this->context->doctrineContainer->schemaManager->listTables()) == 0) {
						$this->context->moduleManager->setSection($this->mode);
						$this->context->moduleManager->installModule("core");
					}

					if ($this->context->params["website"]["theme"]) {
						$this->setView("finish");
					}
				}
			}
		}
	}



	public function createComponentFormAccount($name)
	{
		$form = new \App\SystemModule\SystemAccountForm($this, $name, "common");
		$form->setSuccessLink("this");
		$form->setSubmitLabel("Next");
		return $form;
	}



	public function createComponentFormDatabase($name)
	{
		$form = new \App\SystemModule\SystemDatabaseForm(false, true);
		$form->setSuccessLink("this");
		$form->setSubmitLabel("Install");
		return $form;
	}



	public function createComponentFormWebsite($name)
	{
		return $this->context->websiteForm;
	}



	public function renderFinish()
	{
		umask(0000);
		@file_put_contents($this->context->params["flagsDir"] . "/installed", "");
	}



	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->websiteUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
		$this->template->installationMode = true;
		$this->template->hideMenuItems = true;

		$this->setTitle("Venne:CMS | Installation");
		$this->setKeywords("installation");
		$this->setDescription("Installation");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);
	}

}
