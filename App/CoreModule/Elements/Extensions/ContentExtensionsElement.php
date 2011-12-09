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
class ContentExtensionsElement extends \Venne\Application\UI\Element {


	public function startup()
	{
		if($this->getPresenter()->contentExtensionsKey !==NULL){
			$this->template->modules = $this->getContext()->services->getServicesByInterface("\Venne\Developer\IRenderableContentExtensionModules");
			$this->template->contentExtensionsKey = $this->getPresenter()->contentExtensionsKey;
			$this->template->moduleName = $this->getPresenter()->getModuleName();
		}
	}
	
	public function render()
	{
		$args = new \Venne\ContentExtension\EventArgs;
		$args->presenter = $this;
		$this->presenter->context->doctrineContainer->eventManager->dispatchEvent(Venne\ContentExtension\Events::onRender, $args);
	}

}
