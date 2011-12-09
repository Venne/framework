<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\LayoutModule;

use Venne\ORM\Column;
use Nette\Utils\Html;

/**
 * @author Josef Kříž
 */
class LayoutForm extends \Venne\Forms\EntityForm{
	
	public function startup()
	{
		parent::startup();
		
		$data = $this->presenter->context->scannerService->getModules();
		
		$this->addGroup("Layout");
		
		$this->addSelect("layout", "Layout", $this->presenter->context->scannerService->getLayouts())->addRule(self::FILLED, "Enter layout");
		
		$this->addGroup("Position");
		
		\DependentSelectBox\DependentSelectBox::$disableChilds = false;
		$this->addSelect("module", "Module")
				->setItems($data, false)
				->setDefaultValue("Pages");
		$this->addDependentSelectBox("presenter", "Presenter", $this["module"], array($this, "getValuesPresenter"))->setDefaultValue("Default");
		$this->addDependentSelectBox("action", "Action", $this["presenter"], array($this, "getValuesAction"))->setDefaultValue("default");
		
		for ($i = 0; $i < 4; $i++) {
			$this->addGroup("Param " . ($i + 1))->setOption('container', Html::el('fieldset')
							->id("par$i")
							->class('collapsible'));
			$this->addDependentSelectBox("param_$i", "Parameter", $this["presenter"], array($this, "getValuesParams"));
			$this->addText("value_$i", "Value");
		}

		if ($this->getPresenter()->isAjax()) {
			$this["presenter"]->addOnSubmitCallback(array($this->getPresenter(), "invalidateControl"), "form");
			$this["action"]->addOnSubmitCallback(array($this->getPresenter(), "invalidateControl"), "form");
		}
		
	}
	
	public function getValuesPresenter($form, $dependentSelectBoxName)
	{
		$module = $form["module"]->getValue();
		
		$presenters = array();
		$data = $this->presenter->context->scannerService->getPresenters($module);
		foreach ($data as $item) {
			$presenters[ucfirst($item)] = ucfirst($item);
		}

		return $presenters;
	}


	public function getValuesAction($form, $dependentSelectBoxName)
	{
		$module = $form["module"]->getValue();
		$presenter = $form["presenter"]->getValue();

		$actions = array();
		$data = $this->presenter->context->scannerService->getActions($module, $presenter);
		foreach ($data as $item) {
			$actions[$item] = $item;
		}

		return $actions;
	}


	public function getValuesParams($form, $dependentSelectBoxName)
	{
		$module = $form["module"]->getValue();
		$presenter = $form["presenter"]->getValue();

		if(!$presenter){
			return array();
		}
		
		$params = array();
		$data = $this->presenter->context->scannerService->getParams($module, $presenter);
		foreach ($data as $item) {
			$params[$item] = $item;
		}

		return array(""=>"")+$params;
	}
	
	public function load()
	{
			$model = $this->presenter->context->layoutService;
			
			$regex = explode(":",$this->key->regex);
			
			$this["module"]->setDefaultValue($regex[0]);
			$this["presenter"]->setDefaultValue($regex[1]);
			$this["action"]->setDefaultValue($regex[2]);
			
			$this["layout"]->setDefaultValue($this->key->layout);
			
			$i = 0;
			foreach($this->key->keys as $key){
				$this["param_$i"]->setValue($key->key);
				$this["value_$i"]->setValue($key->val);
				$i++;
			}
	}


	public function save()
	{
		$values = $this->getValues();
		$model = $this->presenter->context->layoutService;
		
		$params = array();
		for ($i = 0; $i < 4; $i++) {
			if($values["param_$i"]){
				$params[$values["param_$i"]] = $values["value_$i"];
			}
		}
		
		if(!$this->key){
			$model->createLayout($values["layout"], $values["module"], $values["presenter"], $values["action"], $params);
		}else{
			$model->updateLayout($this->key->id, $values["layout"], $values["module"], $values["presenter"], $values["action"], $params);
		}
	}

}
