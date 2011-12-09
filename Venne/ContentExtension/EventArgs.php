<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\ContentExtension;

use Nette;
use Venne\Forms\PageForm;
use App\CoreModule\PageEntity;

/**
 * @author Josef Kříž
 */
class EventArgs extends \Doctrine\Common\EventArgs {


	/** @var PageForm */
	public $form;

	/** @var PageEntity */
	public $page;

}
