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
class ApplicationPresenter extends BasePresenter {


	/** @persistent */
	public $key;



	public function startup()
	{
		parent::startup();
		$this->addPath("Application", $this->link(":Core:Admin:System:Application:"));
	}



	public function createComponentApplicationForm()
	{
		$form = $this->context->createCore_systemApplicationForm();
		$form->setRoot("");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Application settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}

}
