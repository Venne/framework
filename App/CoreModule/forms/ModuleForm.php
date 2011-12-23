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
use Venne\Forms\Form;
use Venne\Forms\Mapping\ConfigFormMapper;

/**
 * @author Josef Kříž
 */
class ModuleForm extends \Venne\Forms\ConfigForm {


	public function __construct(ConfigFormMapper $mapper, $moduleName)
	{
		parent::__construct($mapper);
		$this->setModuleName($moduleName);
	}
	

	public function setModuleName($moduleName)
	{
		$this->setRoot("parameters.modules.$moduleName");
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup("Basic setup");
		$this->addCheckbox("run", "Run")->setDefaultValue(true);
	}

}
