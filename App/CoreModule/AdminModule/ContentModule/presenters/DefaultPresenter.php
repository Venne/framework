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
class DefaultPresenter extends BasePresenter
{


	/** @persistent */
	public $id;

	/** @persistent */
	public $type;



	public function startup()
	{
		parent::startup();
		$this->addPath("Pages", $this->link(":Core:Admin:Content:Default:"));
	}



	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Core:Admin:Content:Default:create"));
	}



	public function actionEdit()
	{
		$this->addPath("Edit ({$this->id})", $this->link(":Core:Admin:Content:Default:edit"));
	}



	public function actionTree()
	{
		$this->template->items = $this->context->core->pageRepository->findBy(array("parent" => NULL));
	}



	public function handleCreate()
	{
		$this->template->showCreate = $this->context->core->cmsManager->getContentTypes();
	}



	public function createComponentForm($name)
	{
		$repository = $this->context->core->pageRepository;
		$entity = $this->context->core->cmsManager->getContentEntity($this->getParameter("type"));
		$entity->languages->add($this->context->core->languageRepository->find(1));
		$em = $this->context->entityManager;

		$form = $this->context->core->cmsManager->getContentForm($this->getParameter("type"), $entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($em, $repository)
		{
			if($repository->isUnique($form->entity->page)){
				$em->persist($form->entity);
				$em->flush();

				$form->getPresenter()->flashMessage("Page has been created");
				$form->getPresenter()->redirect("default", array("type" => null));
			}else{
				$form->presenter->flashMessage("URL is not unique", "warning");
			}
		};
		return $form;
	}



	public function createComponentFormTranslate($name)
	{
		$repository = $this->context->core->pageRepository;
		$em = $this->context->entityManager;

		$pageEntity = $this->context->core->pageRepository->find($this->getParameter("id"));
		$contentEntity = $this->context->core->cmsManager->getContentEntity($pageEntity->type);
		$contentEntity = $em->getRepository(get_class($contentEntity))->findOneBy(array("page" => $pageEntity->id));

		$entity = $this->context->core->cmsManager->getContentEntity($pageEntity->type);
		$entity->type = $contentEntity->type;
		$entity->parent = $pageEntity->parent;
		$entity->translationFor = $pageEntity;


		$form = $this->context->core->cmsManager->getContentForm($pageEntity->type, $entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($em, $repository)
		{
			if($repository->isUnique($form->entity->page)){
				$em->persist($form->entity);
				$em->flush();

				$form->getPresenter()->flashMessage("Page has been created");
				$form->getPresenter()->redirect("default", array("type" => null));
			}else{
				$form->presenter->flashMessage("URL is not unique", "warning");
			}
		};
		return $form;
	}



	public function createComponentFormEdit($name)
	{
		$repository = $this->context->core->pageRepository;
		$em = $this->context->entityManager;
		$pageEntity = $this->context->core->pageRepository->find($this->getParameter("id"));
		$entity = $this->context->core->cmsManager->getContentEntity($pageEntity->type);
		$entity = $em->getRepository(get_class($entity))->findOneBy(array("page" => $pageEntity->id));

		$form = $this->context->core->cmsManager->getContentForm($pageEntity->type, $entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($em, $repository)
		{
			if($repository->isUnique($form->entity->page)){
				$em->persist($form->entity);
				$em->flush();

				$form->getPresenter()->flashMessage("Page has been updated");
				$form->getPresenter()->redirect("default", array("type" => null));
			}else{
				$form->presenter->flashMessage("URL is not unique", "warning");
			}
		};
		return $form;
	}



	public function createComponentFormSort()
	{
		$form = new \Venne\Application\UI\Form($this, "formSort");
		$form->addHidden("hash");
		$form->addSubmit("Save", "Save")->onClick[] = array($this, "handleSortSave");
		return $form;
	}



	/**
	 * @allowed(administration-navigation-edit)
	 */
	public function handleSortSave()
	{
		$data = array();
		$val = $this["formSort"]->getValues();
		$hash = explode("&", $val["hash"]);
		foreach ($hash as $item) {
			$item = explode("=", $item);
			$depend = $item[1];
			if ($depend == "root") $depend = Null;
			$id = \substr($item[0], 5, -1);
			if (!isset($data[$depend])) $data[$depend] = array();
			$order = count($data[$depend]) + 1;
			$data[$depend][] = array("id" => $id, "parent_id" => $depend);
		}
		$this->context->core->pageRepository->setStructure($data);
		$this->flashMessage("Structure has been saved.", "success");
		$this->redirect("this");
	}



	public function handleDelete($id)
	{
		$this->context->core->pageRepository->delete($this->context->core->pageRepository->find($this->id));
		$this->flashMessage("Page has been deleted", "success");
		$this->redirect("this", array("id" => NULL));
	}



	public function renderDefault()
	{
		$this->template->table = $this->context->core->pageRepository->findBy(array("translationFor" => NULL));
	}

}
