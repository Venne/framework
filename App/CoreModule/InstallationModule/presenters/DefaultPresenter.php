<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\InstallationModule;

use Venne\Application\UI\Presenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DefaultPresenter extends Presenter {


	/**
	 * Formats layout template file names.
	 *
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		return array($this->getContext()->parameters["libsDir"] . "/App/CoreModule/layouts/administration.latte");
	}



	public function startup()
	{

		parent::startup();


		/* Resources dir */
		if (!file_exists($this->context->parameters['resourcesDir'] . "/CoreModule")) {
			$this->context->module->resourcesManager->checkResources();
		}


		/* Extensions */
		$modules = array("gd", "gettext", "iconv", "json", "pdo", "pdo_mysql");
		foreach ($modules as $item) {
			if (!extension_loaded($item)) {
				$this->flashMessage("Module " . $item . " is not enabled.", "warning");
			}
		}


		/* Writable	 */
		$paths = array($this->getContext()->parameters["wwwDir"] . "/public/", $this->getContext()->parameters["dataDir"] . "/", $this->getContext()->parameters["configDir"] . "/", $this->getContext()->parameters["tempDir"] . "/", $this->getContext()->parameters["appDir"] . "/proxies/", $this->getContext()->parameters["flagsDir"]);
		foreach ($paths as $item) {
			if (!is_writable($item)) {
				$this->flashMessage("Path " . $item . " is not writable.", "warning");
			}
		}

		if (file_exists($this->getContext()->parameters["flagsDir"] . "/installed")) {
			$this->setView("finish");
		} else {
			$this->setView("default");
			if ($this->context->parameters["administration"]["login"]["password"]) {
				$this->setView("database");
				if ($this->context->parameters["database"]["driver"]) {
					$this->setView("website");

					try{
						if (count($this->context->schemaManager->listTables()) == 0) {
							$this->context->core->moduleManager->installModule("core");
						}
					}catch(\PDOException $e){
						if($e->getCode() == "HY000"){
							$this->context->core->moduleManager->installModule("core");
						}else{
							throw $e;
						}
					}

					if ($this->context->parameters["website"]["title"]) {
						$this->setView("language");

						if ($this->context->parameters["website"]["defaultLanguage"]) {
							$this->setView("finish");
						}
					}
				}
			}
		}
	}



	public function createComponentSystemAccountForm($name)
	{
		$form = $this->context->core->createSystemAccountForm();
		$form->setRoot("parameters.administration.login");
		$form->addSubmit("_submit", "Next");
		$form->onSuccess[] = function($form)
		{
			$form->presenter->redirect("this");
		};
		return $form;
	}



	public function createComponentSystemDatabaseForm($name)
	{
		$form = $this->context->core->createSystemDatabaseForm();
		$form->setRoot("parameters.database");
		$form->addSubmit("_submit", "Install");
		$form->onSuccess[] = function($form)
		{
			$form->presenter->redirect("this");
		};
		$form->setShowTestConnection(false);
		return $form;
	}



	public function createComponentFormWebsite($name)
	{
		$form = $this->context->core->createWebsiteForm();
		$form->setRoot("parameters.website");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function createComponentFormLanguage($name)
	{
		$repository = $this->context->core->languageRepository;
		$entity = $repository->createNew();
		$em = $this->context->entityManager;
		$config = $this->context->configManager;

		$form = $this->context->core->createLanguageForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($repository, $config)
		{
			$repository->save($form->entity);
			if ($form->entity->id == 1) {
				$config["parameters"]["website"]["defaultLanguage"] = $form->entity->alias;
				$config->save();
			}
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function renderFinish()
	{
		umask(0000);
		@file_put_contents($this->context->parameters["flagsDir"] . "/installed", "");
	}



	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->hideMenuItems = true;
		$this->template->websiteUrl = $this->getHttpRequest()->getUrl()->getBaseUrl();
		$this["vennePanel"]->setVisibility(false);

		$this["head"]->setTitle("Venne:CMS");
		$this["head"]->setRobots($this["head"]::ROBOTS_NOINDEX | $this["head"]::ROBOTS_NOFOLLOW);
	}

}
