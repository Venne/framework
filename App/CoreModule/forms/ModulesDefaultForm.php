<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\ModulesModule;

use Venne\ORM\Column;
use Nette\Utils\Html;
use Venne\Application\UI\Form;

/**
 * @author Josef Kříž
 */
class ModulesDefaultForm extends \Venne\Forms\EditForm {

	public function startup()
	{
		parent::startup();
		
		$this->addGroup("Default modules");
		$this->addTextWithSelect("defaultPresenter", "Default presenter");
		$this->addTextWithSelect("errorPresenter", "Error presenter");
	}
	
	public function setup(){
		$model = $this->presenter->context->scannerService;
		
		$this["defaultPresenter"]
			->setItems($model->getLinksOfModulesPresenters(), false)
			->setDefaultValue($this->presenter->context->configManager[$this->presenter->mode]["website"]["defaultPresenter"]);
		
		$this["errorPresenter"]
			->setItems($model->getLinksOfModulesPresenters(), false)
			->setDefaultValue($this->presenter->context->configManager[$this->presenter->mode]["website"]["errorPresenter"]);
	}

	public function save()
	{
		$values = $this->getValues();

		$config = $this->presenter->context->configManager;
		$config[$this->presenter->mode]["website"]["defaultPresenter"] = $values["defaultPresenter"];
		$config[$this->presenter->mode]["website"]["errorPresenter"] = $values["errorPresenter"];
		$config->save();
	}

}
