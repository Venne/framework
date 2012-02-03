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
class DatabasePresenter extends BasePresenter {


	public function startup()
	{
		parent::startup();
		$this->addPath("Database", $this->link(":Core:Admin:System:Database:"));
	}



	public function createComponentSystemDatabaseForm()
	{
		$form = $this->context->core->createSystemDatabaseForm();
		$form->setRoot("parameters.database");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Database settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function renderDefault()
	{

	}

}
