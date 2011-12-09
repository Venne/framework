<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SecurityModule\AdminModule;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class UsersPresenter extends BasePresenter {


	/** @persistent */
	public $id;


	public function startup()
	{
		parent::startup();
		$this->addPath("Users", $this->link(":Security:Admin:Users:"));
		$this->template->table = $this->context->userRepository->findAll();
	}


	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Security:Admin:Users:create"));
	}


	public function actionEdit()
	{
		$this->addPath("edit" . " (" . $this->id . ")", $this->link(":Security:Admin:Users:edit"));
	}


	public function handleDelete($id)
	{
		$item = $this->context->userRepository->find($id);
		$em = $this->context->doctrineContainer->entityManager;
		$em->remove($item);
		$em->flush();
		$this->flashMessage("User has been deleted", "success");
		$this->redirect("this");
	}


	public function createComponentForm($name)
	{
		$repository = $this->context->userRepository;
		$entity = $this->context->userRepository->createNew();
		$em = $this->context->doctrineContainer->entityManager;
		$entityFormMapper = $this->context->doctrineContainer->entityFormMapper;
		$presenter = $this;
		
		$form = \App\SecurityModule\UserForm::create($repository, $entityFormMapper, $em);
		$form->setSuccessLink("default");
		$form->setSubmitLabel("Vytvořit");
		$form->onSave[] = function($form) use ($repository, $presenter){
			$presenter->flashMessage("User has been created", "success");
			$presenter->redirect("default");
		};
		return $form;
	}


	public function createComponentFormEdit($name)
	{
		$repository = $this->context->userRepository;
		$entity = $this->context->userRepository->find($this->getParam("id"));
		$em = $this->context->doctrineContainer->entityManager;
		$entityFormMapper = $this->context->doctrineContainer->entityFormMapper;
		$presenter = $this;
		
		$form = \App\SecurityModule\UserForm::edit($repository, $entityFormMapper, $em, $entity);
		$form->onSave[] = function($form) use ($repository, $presenter){
			$presenter->flashMessage("User has been updated", "success");
			$presenter->redirect("this");
		};
		return $form;
	}


	public function renderDefault()
	{
		
	}

}
