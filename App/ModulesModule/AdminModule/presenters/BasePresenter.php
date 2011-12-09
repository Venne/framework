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
class BasePresenter extends \Venne\Application\UI\AdminPresenter
{
	
	public function startup()
	{
		parent::startup();
		$this->addPath("Modules", $this->link(":Modules:Admin:Default:"));
	}
	
	public function beforeRender()
	{
		parent::beforeRender();
		$this->setTitle("Venne:CMS | Modules administration");
		$this->setKeywords("modules administration");
		$this->setDescription("Modules administration");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);
	}

}
