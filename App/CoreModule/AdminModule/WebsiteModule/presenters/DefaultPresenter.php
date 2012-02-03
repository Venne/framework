<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\AdminModule\WebsiteModule;

use \Nette\Application\UI\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DefaultPresenter extends BasePresenter {


	/** @persistent */
	public $id;



	public function startup()
	{
		parent::startup();
		$this->addPath("Website", $this->link(":Core:Admin:Website:Default:"));
	}



	public function createComponentWebsiteForm()
	{
		$form = $this->context->core->createWebsiteForm();
		$form->setRoot("parameters.website");
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Website has been saved", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}

}
