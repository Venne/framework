<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Elements;

use Venne;

/**
 * @author Josef Kříž
 */
class HeaderElement extends \Venne\Application\UI\Element {


	public function beforeRender()
	{
		$this->template->setFile(__DIR__ . "/" . $this->key . ".latte");
		$this->template->theme = $this->presenter->context->params["website"]["theme"];
	}

}
