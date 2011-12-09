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

use Venne\ORM\Column;
use Nette\Utils\Html;

/**
 * @author Josef Kříž
 */
class LanguageForm extends \Venne\Forms\EntityForm {



	public function startup()
	{
		parent::startup();
		$this->addGroup("Language");
		$this->addText("name", "Name");
		$this->addText("alias", "Alias");
		$this->addText("short", "Short");
	}

}
