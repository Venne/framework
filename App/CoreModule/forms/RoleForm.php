<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SecurityModule;

use Venne\ORM\Column;
use Nette\Utils\Html;

/**
 * @author Josef Kříž
 */
class RoleForm extends \Venne\Forms\EntityForm {



	public function startup()
	{
		parent::startup();
		$this->addGroup("Role");
		$this->addText("name", "Name");
		$this->addManyToOne("parent", "Parent")
				->setPrompt("root");
	}

}
