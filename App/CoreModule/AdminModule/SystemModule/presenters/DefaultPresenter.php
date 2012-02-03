<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\AdminModule\SystemModule;

use Venne;

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
		$this->addPath("General", $this->link(":Core:Admin:System:Default:"));
	}



	public function handleDelete($key)
	{
		$key2 = array_search($key, ((array)$this->context->configManager["parameters"]["modes"]));
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



	public function createComponentSystemForm()
	{
		$form = $this->context->createCore_systemForm();
		$form->setRoot("parameters");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Global settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function createComponentSystemModeForm()
	{
		$form = $this->context->createSystemModeFormControl();
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Saved", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function createComponentSystemModeFormEdit()
	{
		$form = $this->context->createSystemModeFormControl($this->getParam("key"));
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function renderDefault()
	{
		$this->template->modes = $this->context->parameters["modes"];
	}

}
