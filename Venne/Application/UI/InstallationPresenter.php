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
class InstallationPresenter extends \Venne\Application\UI\Presenter {


	/** @var string */
	public $mode = "default";



	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->hideMenuItems = true;
	}

}

