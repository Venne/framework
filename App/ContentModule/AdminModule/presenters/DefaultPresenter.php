<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\ContentModule\AdminModule;

use \Nette\Application\UI\Form;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class DefaultPresenter extends \Venne\Application\UI\AdminPresenter {


	/** @persistent */
	public $id;

	/** @persistent */
	public $type;



	public function startup()
	{
		parent::startup();
		$this->addPath("Content", $this->link(":Content:Admin:Default:"));
	}



	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Content:Admin:Default:create"));
	}



	public function actionEdit()
	{
		$this->addPath("Edit ({$this->id})", $this->link(":Content:Admin:Default:edit"));
	}



	public function handleCreate()
	{
		$this->template->showCreate = $this->context->cmsManager->getContentTypes();
	}



	public function createComponentForm($name)
	{
		$entity = $this->context->cmsManager->getContentEntity($this->getParam("type"));
		$em = $this->context->entityManager;

		$form = $this->context->cmsManager->getContentForm($this->getParam("type"), $entity);
		$form->setSuccessLink("default", array("type" => null));
		$form->setFlashMessage("Page has been created");
		$form->setSubmitLabel("Create");
		$form->onSave[] = function($form) use ($em) {
					$em->persist($form->entity);
					try{
						$em->flush();
					}catch(\PDOException $ex){
						if($ex->getCode() == "23000"){
							$form->presenter->flashMessage("URL is not unique", "warning");
							$form->setSuccessLink(NULL);
							$form->setFlashMessage(NULL);
						}else{
							throw $ex;
						}
					}
				};
		return $form;
	}



	public function createComponentFormEdit($name)
	{
		$em = $this->context->entityManager;
		$pageEntity = $this->context->pageRepository->find($this->getParam("id"));
		$entity = $this->context->cmsManager->getContentEntity($pageEntity->type);
		$entity = $em->getRepository(get_class($entity))->findOneBy(array("page" => $pageEntity->id));

		$form = $this->context->cmsManager->getContentForm($pageEntity->type, $entity);
		$form->setSuccessLink("this");
		$form->setFlashMessage("Page has been updated");
		$form->setSubmitLabel("Update");
		$form->onSave[] = function($form) use ($em) {
					$em->persist($form->entity);
					try{
						$em->flush();
					}catch(\PDOException $ex){
						if($ex->getCode() == "23000"){
							$form->presenter->flashMessage("URL is not unique", "warning");
							$form->setSuccessLink(NULL);
							$form->setFlashMessage(NULL);
						}else{
							throw $ex;
						}
					}
				};
		return $form;
	}



	public function handleDelete($id)
	{
		$this->context->pageRepository->delete($this->context->pageRepository->find($this->id));
		$this->flashMessage("Page has been deleted", "success");
		$this->redirect("this", array("id" => NULL));
	}



	public function renderDefault()
	{
		$this->template->table = $this->context->pageRepository->findBy(array("translationFor" => NULL));
	}

}
