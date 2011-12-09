<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\AdminModule;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class AboutPresenter extends \Venne\Application\UI\AdminPresenter
{

	public function renderDefault()
	{
		$this->setTitle("Venne:CMS | About Venne:CMS");
		$this->setKeywords("about Venne:CMS");
		$this->setDescription("About Venne:CMS");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);
	}
	
}
