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

use Nette\Utils\Html;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserForm extends \Venne\Forms\EntityForm
{

	public function startup()
	{
		parent::startup();

		$this->addGroup("User");
		$this->addCheckbox("enable", "Enable")->setDefaultValue(true);
		$this->addText("email", "E-mail")
			->addRule(\Nette\Forms\Form::EMAIL, "Enter email");
		$this->addCheckbox("password_new", "Set password");
		$this->addPassword("_password", "Password")
			->setOption("description", "minimal length is 5 char")
			->addConditionOn($this['password_new'], \Nette\Forms\Form::FILLED)
			->addRule(\Nette\Forms\Form::FILLED, 'Enter password')
			->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Password is short', 5);
		$this->addPassword("password_confirm", "Confirm password")
			->addRule(\Nette\Forms\Form::EQUAL, 'Invalid re password', $this['_password']);
		$this->addText("key", "Authentization key")->setOption("description", "If is set user cannot log in.");

		$this->addGroup("Next informations");
		$this->addManyToMany("roleEntities");
	}



	public function handleSuccess()
	{
		if ($this["password_new"]->value) {
			$this->entity->password = $this["_password"]->value;
		}
	}

}
