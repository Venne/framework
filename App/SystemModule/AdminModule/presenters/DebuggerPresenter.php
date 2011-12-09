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
class DebuggerPresenter extends BasePresenter
{
	
	public function startup()
	{
		parent::startup();
		$this->addPath("Debugger", $this->link(":System:Admin:Debugger:"));
	}
	
	public function createComponentFormEdit($name)
	{
		$form = new \App\SystemModule\SystemDebuggerForm;
		$form->setSuccessLink("default");
		$form->setFlashMessage("Database settings has been updated");
		$form->setSubmitLabel("Update");
		return $form;
	}
	
	public function renderDefault()
	{

	}

}
