<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Forms;

use Venne\ORM\Column;
use Nette\Utils\Html;
use Venne\Forms\Form;
use Venne\Forms\Mapping\ConfigFormMapper;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleForm extends \Venne\Forms\ConfigForm {


	public function __construct(ConfigFormMapper $mapper)
	{
		parent::__construct($mapper);
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
