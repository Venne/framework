<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\WebsiteModule\AdminModule;

use \Nette\Application\UI\Form;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class DefaultPresenter extends \Venne\Application\UI\AdminPresenter {


	/** @persistent */
	public $id;
	
	/** @inject websiteForm */
	public $websiteForm;



	public function startup()
	{
		parent::startup();
		$this->addPath("Website setting", $this->link(":Website:Admin:Default:"));
	}



	public function createComponentForm($name)
	{
//		$form = new \App\WebsiteModule\WebsiteForm;
//		$form->setSuccessLink("default");
//		$form->setFlashMessage("Website has been saved");
//		$form->setSubmitLabel("Save");
//		return $form;
		
		return $this->websiteForm;
	}



	public function beforeRender()
	{
		parent::beforeRender();
		$this->setTitle("Venne:CMS | Websites administration");
		$this->setKeywords("websites administration");
		$this->setDescription("Websites administration");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);
	}

}
