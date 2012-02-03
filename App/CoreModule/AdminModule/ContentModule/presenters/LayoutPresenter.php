<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\AdminModule\ContentModule;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LayoutPresenter extends BasePresenter {


	/** @persistent */
	public $id;

	/** @persistent */
	public $type;



	public function startup()
	{
		parent::startup();
		$this->addPath("Layouts", $this->link(":Core:Admin:Content:Layout:"));
	}



	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Core:Admin:Content:Default:create"));
	}



	public function actionEdit()
	{
		$this->addPath("Edit ({$this->id})", $this->link(":Core:Admin:Content:Default:edit"));
	}



	public function handleCreate()
	{
		$this->template->showCreate = $this->context->core->cmsManager->getContentTypes();
	}



	public function createComponentForm($name)
	{
		$entity = $this->context->core->cmsManager->getContentEntity($this->getParam("type"));
		$em = $this->context->entityManager;

		$form = $this->context->core->cmsManager->getContentForm($this->getParam("type"), $entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($em)
		{
			$em->persist($form->entity);
			try {
				$em->flush();
			} catch (\PDOException $ex) {
				if ($ex->getCode() == "23000") {
					$form->presenter->flashMessage("URL is not unique", "warning");
					$form->setSuccessLink(NULL);
					$form->setFlashMessage(NULL);
				} else {
					throw $ex;
				}
			}
		};
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Page has been created");
		};
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->redirect("default", array("type" => null));
		};
		return $form;
	}



	public function createComponentFormEdit($name)
	{
		$em = $this->context->entityManager;
		$pageEntity = $this->context->core->pageRepository->find($this->getParam("id"));
		$entity = $this->context->core->cmsManager->getContentEntity($pageEntity->type);
		$entity = $em->getRepository(get_class($entity))->findOneBy(array("page" => $pageEntity->id));

		$form = $this->context->core->cmsManager->getContentForm($pageEntity->type, $entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($em)
		{
			$em->persist($form->entity);
			try {
				$em->flush();
			} catch (\PDOException $ex) {
				if ($ex->getCode() == "23000") {
					$form->presenter->flashMessage("URL is not unique", "warning");
					$form->setSuccessLink(NULL);
					$form->setFlashMessage(NULL);
				} else {
					throw $ex;
				}
			}
		};
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Page has been updated");
		};
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->redirect("this");
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
		$this->template->table = $this->context->core->pageRepository->findBy(array("translationFor" => NULL));
	}

}
