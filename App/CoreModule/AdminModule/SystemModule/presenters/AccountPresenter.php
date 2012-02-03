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
class AccountPresenter extends BasePresenter {


	public function startup()
	{
		parent::startup();
		$this->addPath("Account", $this->link(":Core:Admin:System:Account:"));
	}



	public function createComponentSystemAccountForm()
	{
		$form = $this->context->core->createSystemAccountForm();
		$form->setRoot("parameters.administration.login");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Account settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}



	public function renderDefault()
	{

	}

}
