<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\AdminModule\ModulesModule;

use App\CoreModule\Managers\DependencyNotExistsException;
use App\CoreModule\Managers\DependencyException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DefaultPresenter extends BasePresenter {


	/** @persistent */
	public $key;



	public function startup()
	{
		parent::startup();
		$this->addPath("Modules", $this->link(":Core:Admin:Modules:Default:", array("key" => NULL)));
	}



	public function actionEdit()
	{
		$this->addPath($this->key, $this->link(":Core:Admin:Modules:Default:edit"));
	}



	public function actionDefault()
	{
		$this->template->modules = array();
		foreach ($this->context->core->scannerService->modules as $item) {
			$class = "\\App\\" . ucfirst($item) . "Module\\Module";
			$this->template->modules[$item] = new $class;
		}
	}



	public function createComponentModulesDefaultForm()
	{
		$form = $this->context->core->createModulesDefaultForm();
		$form->setRoot("parameters.website");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Changes has been saved", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function createComponentForm($name)
	{
		$module = $this->context->core->moduleManager->getModuleInstance($this->key);

		$form = $module->getForm($this->context);
		$form->setRoot("parameters.modules.{$this->key}");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Changes has been saved", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function handleActivate($key)
	{
		$this->context->core->moduleManager->activateModule($key);
		$this->flashMessage("Module has been activated", "success");
		$this->redirect("this");
	}



	public function handleDeactivate($key)
	{
		$this->context->core->moduleManager->deactivateModule($key);
		$this->flashMessage("Module has been deactivated", "success");
		$this->redirect("this");
	}



	public function handleInstall($key, $confirm = null)
	{
		$moduleManager = $this->context->core->moduleManager;

		try {
			$moduleManager->installModule($key, $confirm);
			$this->flashMessage("Module has been installed", "success");
			$this->redirect("this");
		} catch (DependencyNotExistsException $ex) {
			$this->flashMessage($ex->getMessage(), "warning");
			$this->redirect("this");
		} catch (DependencyException $ex) {
			$this->template->showDialog = $ex->dependencies;
			$this->template->dialogModule = $key;
		}
	}



	public function handleUpgrade($key)
	{
		$this->context->core->moduleManager->upgradeModule($key);
		$this->flashMessage("Module has been upgraded", "success");
		$this->redirect("this");
	}



	public function handleDowngrade($key)
	{
		$this->context->core->moduleManager->downgradeModule($key);
		$this->flashMessage("Module has been upgraded", "success");
		$this->redirect("this");
	}



	public function handleUninstall($key, $confirm)
	{
		$moduleManager = $this->context->core->moduleManager;

		try {
			$moduleManager->uninstallModule($key, $confirm);
			$this->flashMessage("Module has been uninstalled", "success");
			$this->redirect("this");
		} catch (\App\CoreModule\AdminModule\DependencyException $ex) {
			$this->template->showUninstallDialog = $ex->dependencies;
			$this->template->dialogModule = $key;
		}
	}

}
