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
class EditorElement extends \Venne\Application\UI\Element {


	public function startup()
	{
		$this->template->text = "ahoj";
	}
	
	public function createComponentForm($name)
	{
		$form = new \Venne\Application\UI\Form($this, $name);
		$form->addTextArea("text2");
		return $form;
	}

}
