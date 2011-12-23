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
			$this->getContext()->parameters["wwwDir"] . "/public/",
			$this->getContext()->parameters["dataDir"] . "/",
			$this->getContext()->parameters["themesDir"] . "/",
			$this->getContext()->parameters["configDir"] . "/",
			$this->getContext()->parameters["tempDir"] . "/",
			$this->getContext()->parameters["appDir"] . "/proxies/",
			$this->getContext()->parameters["flagsDir"]
		);
		foreach ($paths as $item) {
			if (!is_writable($item)) {
				$this->flashMessage("Path " . $item . " is not writable.", "warning");
			}
		}

		if (file_exists($this->getContext()->parameters["flagsDir"] . "/installed")) {
			$this->setView("finish");
		} else {
			$this->setView("default");
			if ($this->context->parameters["admin"]["password"]) {
				$this->setView("database");
				if ($this->context->parameters["database"]["dbname"]) {
					$this->setView("website");

					if (count($this->context->schemaManager->listTables()) == 0) {
						$this->context->moduleManager->installModule("core");
					}

					if ($this->context->parameters["website"]["theme"]) {
						$this->setView("finish");
					}
				}
			}
		}
	}



	public function createComponentSystemAccountForm($name)
	{
		$form = $this->context->createSystemAccountFormControl();
		$form->setSuccessLink("this");
		$form->setSubmitLabel("Next");
		return $form;
	}



	public function createComponentSystemDatabaseForm($name)
	{
		$form = $this->context->createSystemDatabaseFormControl();
		$form->setShowTestConnection(false);
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
		@file_put_contents($this->context->parameters["flagsDir"] . "/installed", "");
	}



	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->websiteUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
	}

}
