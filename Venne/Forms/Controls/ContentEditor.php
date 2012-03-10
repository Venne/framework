<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Controls;

use Venne;

/**
 * @author	 Josef Kříž
 */
class ContentEditor extends \Nette\Forms\Controls\TextArea
{



	public function getValue()
	{
		$args = new \App\CoreModule\Events\ContentHelperArgs;
		$args->setText(parent::getValue());
		$this->getForm()->getPresenter()->getContext()->eventManager->dispatchEvent(\App\CoreModule\Events\ContentHelperEvents::onContentSave, $args);
		return $args->getText();
	}



	public function setValue($text)
	{
		$args = new \App\CoreModule\Events\ContentHelperArgs;
		$args->setText($text);
		$this->getForm()->getPresenter()->getContext()->eventManager->dispatchEvent(\App\CoreModule\Events\ContentHelperEvents::onContentLoad, $args);
		parent::setValue($args->getText());
	}

}