<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SystemModule;

use Venne\ORM\Column;
use Nette\Utils\Html;

/**
 * @author Josef Kříž
 */
class SystemForm extends \Venne\Forms\EditForm {


	public function startup()
	{
		parent::startup();

		$this->addGroup();
		$this->addSelect("mode", "Mode");
	}
	
	public function setup()
	{
		$this["mode"]->setItems($this->presenter->context->configManager->getSections(), false);
	}


	public function load()
	{
		$model = \Nette\Config\NeonAdapter::load($this->presenter->context->params["appDir"] . "/global.neon");
		$this->setDefaults($model);
	}

	public function save()
	{
		parent::save();
		\Nette\Config\NeonAdapter::save($this->getValues(), $this->presenter->context->params["appDir"] . "/global.neon");
	}

}
