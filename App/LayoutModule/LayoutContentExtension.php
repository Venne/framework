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
use Venne\ContentExtension\ContentExtensionSubscriber;

/**
 * @author Josef Kříž
 */
class LayoutContentExtension extends ContentExtensionSubscriber {


	/** @var \LayoutModule\Service */
	protected $service;



	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\App\LayoutModule\Service $service)
	{
		$this->service = $service;
	}



	public function onSave(\Nette\Forms\Container $form, $moduleName, $moduleItemId, $linkParams)
	{
		$values = $form["module_layout"]->getValues();

		$this->service->saveLayout($moduleItemId, $moduleName, $values["use"], $values["layout"], $linkParams);
	}



	public function onCreate(\Nette\Forms\Container $form)
	{
		$form->addGroup("Layout settings")->setOption('container', \Nette\Utils\Html::el('fieldset')->class('collapsible collapsed'));
		$container = $form->addContainer("module_layout");

		$container->addCheckbox("use", "Set layout")->setDefaultValue(false);
		$container->addText("layout", "Layout");
		//$container->addSelect("layout", "Layout", $this->container->cms->moduleManager->getLayouts());

		$form->setCurrentGroup();
	}



	public function onLoad(\Nette\Forms\Container $form, $moduleName, $moduleItemId, $linkParams)
	{
		$container = $form["module_layout"];
		$values = $this->service->loadLayout($moduleItemId, $moduleName);

		if ($values) {
			$container["use"]->setValue(true);
			$container["layout"]->setValue($values->layout);
		}
	}



	public function onRemove($moduleName, $moduleItemId)
	{
		
	}



	public function onRender($presenter, $moduleName)
	{
		
	}

}
