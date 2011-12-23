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
class BasePresenter extends \Venne\Application\UI\AdminPresenter {



	public function startup()
	{
		parent::startup();
		$this->addPath("System", $this->link(":System:Admin:Default:"));
	}

}
