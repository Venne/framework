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
use Venne\Application\UI\Form;

/**
 * @author Josef Kříž
 */
class ModulesDefaultForm extends \Venne\Forms\ConfigForm {



	public function startup()
	{
		parent::startup();

		$this->addGroup("Default modules");
		$this->addTextWithSelect("defaultPresenter", "Default presenter");
		$this->addTextWithSelect("errorPresenter", "Error presenter");
	}



	public function setup()
	{
		$model = $this->presenter->context->scannerService;

		$this["defaultPresenter"]
				->setItems($model->getLinksOfModulesPresenters(), false)
				->setDefaultValue($this->presenter->context->parameters["website"]["defaultPresenter"]);

		$this["errorPresenter"]
				->setItems($model->getLinksOfModulesPresenters(), false)
				->setDefaultValue($this->presenter->context->parameters["website"]["errorPresenter"]);
	}

}
