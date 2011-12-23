<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Venne;

/**
 * @author Josef Kříž
 */
class PagePresenter extends FrontPresenter {


	/** @persistent */
	public $url = "";

	/** @var Venne\Doctrine\ORM\PageEntity */
	protected $page;



	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();
		$this->page = $this->getParam("page");
	}



	/**
	 * Common render method.
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this->setTitle($this->page->title);
		$this->setRobots($this->page->robots);
	}

}

