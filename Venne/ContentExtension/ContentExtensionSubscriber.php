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

use Doctrine\Common\EventSubscriber;

/**
 * @author Josef Kříž
 */
class ContentExtensionSubscriber implements EventSubscriber {



	public function getSubscribedEvents()
	{
		return array(
			Events::onCreate,
			Events::onLoad,
			Events::onSave,
			Events::onRender,
			Events::onRemove
		);
	}

}
