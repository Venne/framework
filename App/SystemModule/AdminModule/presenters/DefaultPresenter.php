<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SystemModule\AdminModule;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class DefaultPresenter extends BasePresenter {


	/** @persistent */
	public $key;



	public function handleDelete($key)
	{
		$key2 = array_search($key, ((array) $this->context->configManager["parameters"]["modes"]));
		unset($this->context->configManager["parameters"]["modes"][$key2]);
		unlink($this->context->parameters["configDir"] . "/config." . $key . ".neon");

		if ($key == $this->context->configManager["parameters"]["mode"]) {
			$this->context->configManager["parameters"]["mode"] = "default";

			$this->mode = "default";
		}

		$this->context->configManager->save();
		$this->flashMessage("Mode has been deleted", "success");
		$this->redirect("this");
	}



	public function createComponentSystemModeForm()
	{
		$form = $this->context->createSystemModeFormControl();
		$form->setSuccessLink("default");
		$form->setSubmitLabel("Create");
		return $form;
	}



	public function createComponentSystemModeFormEdit()
	{
		$form = $this->context->createSystemModeFormControl($this->getParam("key"));
		$form->setSuccessLink("default");
		$form->setSubmitLabel("Update");
		return $form;
	}



	public function renderDefault()
	{
		$this->template->modes = $this->context->parameters["modes"];
	}

}
