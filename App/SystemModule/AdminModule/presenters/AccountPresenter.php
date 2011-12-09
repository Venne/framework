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
class AccountPresenter extends BasePresenter
{
	
	public function startup()
	{
		parent::startup();
		$this->addPath("Account", $this->link(":System:Admin:Account:"));
	}
	
	public function createComponentFormEdit($name)
	{
		$form = new \App\SystemModule\SystemAccountForm($this->mode);
		$form->setSuccessLink("default");
		$form->setFlashMessage("Database settings has been updated");
		$form->setSubmitLabel("Update");
		return $form;
	}
	
	public function renderDefault()
	{

	}

}
