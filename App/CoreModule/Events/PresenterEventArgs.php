<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Events;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EventArgs extends \Doctrine\Common\EventArgs
{


	/** @var \Venne\Application\UI\Presenter */
	protected $presenter;



	/**
	 * @param \Venne\Application\UI\Presenter $presenter
	 */
	public function setPresenter($presenter)
	{
		$this->presenter = $presenter;
	}



	/**
	 * @return \Venne\Application\UI\Presenter
	 */
	public function getPresenter()
	{
		return $this->presenter;
	}
}
