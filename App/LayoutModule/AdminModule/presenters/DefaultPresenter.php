<?php

namespace App\LayoutModule\AdminModule;

use Nette\Utils\Html;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class DefaultPresenter extends \Venne\Application\UI\AdminPresenter {


	/** @persistent */
	public $key;


	public function startup()
	{
		parent::startup();

		$this->addPath("Layout", $this->link(":Layout:Admin:Default:"));
		$this->template->items = $this->context->layoutService->repository->findAll();
	}
	
	public function actionCreate()
	{
		$this->addPath("New item", $this->link(":Layout:Admin:Default:create"));
	}


	public function actionEdit()
	{
		$this->addPath("edit" . " (" . $this->key . ")", $this->link(":Layout:Admin:Default:edit"));
	}
	
	public function createComponentForm($name)
	{
		$form = new \App\LayoutModule\LayoutForm($this, $name);
		$form->setSuccessLink("default");
		$form->setFlashMessage("Layout has been saved");
		$form->setSubmitLabel("Create");
		return $form;
	}
	
	public function createComponentFormEdit($name)
	{
		$form = new \App\LayoutModule\LayoutForm($this, $name, $this->context->layoutService->repository->find($this->getParam("key")));
		$form->setSuccessLink("this");
		$form->setFlashMessage("Repository has been updated");
		$form->setSubmitLabel("Update");
		return $form;
	}

	public function handleDelete($key)
	{
		$this->context->layoutService->removeLayout($key);
		$this->flashMessage("Layout has been deleted", "success");
		$this->redirect("this");
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->setTitle("Venne:CMS | Layout administration");
		$this->setKeywords("layout administration");
		$this->setDescription("Layout administration");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);
	}

}