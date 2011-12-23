<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne;

/**
 * @author Josef Kříž
 */
class ContentExtensionsControl extends \Venne\Application\UI\Control {

	public function viewDefault()
	{
		$args = new \Venne\ContentExtension\EventArgs;
		$args->presenter = $this;

		$this->presenter->context->eventManager->dispatchEvent(Venne\ContentExtension\Events::onRender, $args);
	}

}
