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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserForm extends \Venne\Forms\EntityForm {

	public function startup()
	{
		parent::startup();
		$this->addGroup("User");
		$this->addCheckbox("enable", "Enable")->setDefaultValue(true);
		$this->addText("email", "E-mail")
			->addRule(\Nette\Forms\Form::EMAIL, "Enter email");
		$this->addCheckbox("password_new", "Set password");
		$this->addPassword("password", "Password")->setOption("description", "minimal length is 5 char")->addConditionOn($this['password_new'], \Nette\Forms\Form::FILLED)->addRule(\Nette\Forms\Form::FILLED, 'Enter password')->addRule(\Nette\Forms\Form::MIN_LENGTH, 'Password is short', 5);
		$this->addPassword("password_confirm", "Confirm password")->addRule(\Nette\Forms\Form::EQUAL, 'Invalid re password', $this['password']);

		$this->addGroup("Next informations");
		$this->addManyToMany("roles");
	}


//	/**
//	 * @param \Venne\Doctrine\ORM\BaseRepository $repository
//	 * @param \Venne\Forms\Mapping\EntityFormMapper $mapper
//	 * @param \Doctrine\ORM\EntityManager $em
//	 * @return UserForm
//	 */
//	public static function create($repository, $mapper, $em)
//	{
//		$form = new self($mapper, $em, $repository->createNew());
//		$form["password_new"]->setDefaultValue(true);
//		$form->onSave[] = function($form) use ($repository){
//			$form->entity->enable = 1;
//			$repository->save($form->entity);
//		};
//		return $form;
//	}
//
//	
//	public static function edit($repository, $mapper, $em, $entity)
//	{
//		$form = new self($mapper, $em, $entity);
//		$form->onSave[] = function($form) use ($repository){
//			$repository->save($form->entity);
//		};
//		return $form;
//	}

}
