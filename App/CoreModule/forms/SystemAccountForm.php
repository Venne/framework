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
class SystemAccountForm extends \Venne\Forms\EditForm {


	protected $mode;



	public function __construct($mode = "common")
	{
		$this->mode = $mode;
		parent::__construct();
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup();
		$this->addText("name", "Name");
		$this->addPassword("password", "Password")->setOption("description", "minimal length is 5 char");
		$this->addPassword("password_confirm", "Confirm password");


		$this["name"]
				->addRule(self::FILLED, 'Enter name');
		$this["password"]
				->addRule(self::FILLED, 'Enter password')
				->addRule(self::MIN_LENGTH, 'Password is short', 5);
		$this["password_confirm"]
				->addRule(self::EQUAL, 'Invalid re password', $this['password']);
	}



	public function load()
	{
		$config = $this->presenter->context->configManager[$this->presenter->mode]["admin"];
		$this->setDefaults($config);
	}



	public function save()
	{
		parent::save();
		$config = $this->presenter->context->configManager;
		unset($this["password_confirm"]);
		$config[$this->presenter->mode]["admin"] = $this->getValues();
		$config->save();
	}

}
