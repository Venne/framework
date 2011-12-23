<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\ModulesModule\AdminModule;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class DefaultPresenter extends BasePresenter {


	/** @persistent */
	public $key;



	public function actionDefault()
	{
		$this->template->modules = array();
		foreach ($this->context->scannerService->modules as $item) {
			$class = "\\App\\" . ucfirst($item) . "Module\\Module";
			$this->template->modules[$item] = new $class;
		}
	}



	public function createComponentForm($name)
	{
		$module = $this->context->moduleManager->getModuleInstance($this->key);

		$form = $module->getForm($this->context);
		$form->setSuccessLink("default");
		$form->setFlashMessage("Changes has been saved");
		return $form;
	}



	public function handleActivate($key)
	{
		$this->context->moduleManager->activateModule($key);
		$this->flashMessage("Module has been activated", "success");
		$this->redirect("this");
	}



	public function handleDeactivate($key)
	{
		$this->context->moduleManager->deactivateModule($key);
		$this->flashMessage("Module has been deactivated", "success");
		$this->redirect("this");
	}



	public function handleInstall($key, $confirm = null)
	{
		$moduleManager = $this->context->moduleManager;

		try {
			$moduleManager->installModule($key, $confirm);
			$this->flashMessage("Module has been installed", "success");
			$this->redirect("this");
		} catch (\App\CoreModule\DependencyNotExistsException $ex) {
			$this->flashMessage($ex->getMessage(), "warning");
			$this->redirect("this");
		} catch (\App\CoreModule\DependencyException $ex) {
			$this->template->showDialog = $ex->dependencies;
			$this->template->dialogModule = $key;
		}
	}



	public function handleUpgrade($key)
	{
		$this->context->moduleManager->upgradeModule($key);
		$this->flashMessage("Module has been upgraded", "success");
		$this->redirect("this");
	}



	public function handleDowngrade($key)
	{
		$this->context->moduleManager->downgradeModule($key);
		$this->flashMessage("Module has been upgraded", "success");
		$this->redirect("this");
	}



	public function handleUninstall($key, $confirm)
	{
		$moduleManager = $this->context->moduleManager;

		try {
			$moduleManager->uninstallModule($key, $confirm);
			$this->flashMessage("Module has been uninstalled", "success");
			$this->redirect("this");
		} catch (\App\CoreModule\DependencyException $ex) {
			$this->template->showUninstallDialog = $ex->dependencies;
			$this->template->dialogModule = $key;
		}
	}

}
