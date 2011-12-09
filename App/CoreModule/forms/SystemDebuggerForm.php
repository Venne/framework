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
use Venne\Forms\Form;

/**
 * @author Josef Kříž
 */
class SystemDebuggerForm extends \Venne\Forms\EditForm {


	protected $testConnection;
	protected $showTestConnection;
	protected $createStructure;



	public function startup()
	{
		parent::startup();

		$this->addGroup();
		$this->addSelect("mode", "Mode")->setItems(array("production", "development", "detect"), false);
		$this->addSelect("strict", "Strict mode")->setItems(array("yes", "no"), false);
		$this->addText("developerIp", "IPs for devel");
	}



	public function load()
	{
		$config = $this->presenter->context->configManager[$this->presenter->mode]["debugger"];
		$this->setDefaults($config);
	}



	protected function handleError()
	{
		
	}



	public function save()
	{
		parent::save();
		$values = $this->getValues();
		$config = $this->presenter->context->configManager;

		$config[$this->presenter->mode]["debugger"]["mode"] = $values["mode"];
		$config[$this->presenter->mode]["debugger"]["strict"] = $values["strict"];
		$config[$this->presenter->mode]["debugger"]["developerIp"] = $values["developerIp"];
		$config->save();
	}

}
