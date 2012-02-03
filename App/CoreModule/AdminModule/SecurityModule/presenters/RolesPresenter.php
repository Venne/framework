<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\AdminModule\SecurityModule;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class RolesPresenter extends BasePresenter {


	/** @persistent */
	public $id;



	public function startup()
	{
		parent::startup();
		$this->addPath("Roles", $this->link(":Core:Admin:Security:Roles:"));

		$this->template->items = $this->context->core->roleRepository->findBy(array("parent" => NULL));
	}



	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Security:Admin:Roles:create"));
	}



	public function actionEdit()
	{
		$this->addPath("edit" . " (" . $this->id . ")", $this->link(":Security:Admin:Roles:edit"));
	}



	public function createComponentForm($name)
	{
		$form = new \Venne\Application\UI\Form;
		$this->formRecursion($form, $this->template->items);
		$form->onSuccess[] = array($this, "handleSave");
		return $form;
	}



	public function createComponentFormSort($name)
	{
		$form = new \Venne\Application\UI\Form;
		$form->addHidden("hash");
		$form->addSubmit("Save", "Save")->onClick[] = array($this, "handleSortSave");
		return $form;
	}



	public function formRecursion($form, $menu)
	{
		if ($menu) {
			foreach ($menu as $item) {
				$form->addSubmit("settings_" . $item->id, "Settings");
				$form->addSubmit("delete_" . $item->id, "Delete")->getControlPrototype()->class = "grey";
				if ($item->childrens) $this->formRecursion($form, $item->childrens);
			}
		}
	}



	public function formSaveRecursion($form, $menu)
	{
		foreach ($menu as $key => $item) {
			if ($form["delete_" . $item->id]->isSubmittedBy()) {
				$this->context->core->roleRepository->delete($this->context->core->roleRepository->find($item->id));
				$this->flashMessage("Role has been deleted", "success");
				$this->redirect("this");
			}
			if ($form["settings_" . $item->id]->isSubmittedBy()) {
				$this->redirect("edit", array("id" => $item->id));
			}

			if ($item->childrens) $this->formSaveRecursion($form, $item->childrens);
		}
	}



	public function handleSave()
	{
		$this->formSaveRecursion($this["form"], $this->template->items);
	}



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
			$data[$depend][] = array("id" => $id, "order" => $order, "role_id" => $depend);
		}
		$this->context->core->roleRepository->setStructure($data);
		$this->flashMessage("Structure has been saved.", "success");
		$this->redirect("this");
	}



	public function createComponentFormRole()
	{
		$repository = $this->context->core->roleRepository;
		$entity = $repository->createNew();

		$form = $this->context->core->createRoleForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Create");
		$form->onSuccess[] = function($form) use ($repository)
		{
			$repository->save($form->entity);
			$form->getPresenter()->flashMessage("Role has been saved", "success");
			$form->getPresenter()->redirect("default");
		};
		return $form;
	}



	public function createComponentFormRoleEdit($name)
	{
		$repository = $this->context->core->roleRepository;
		$entity = $repository->find($this->getParam("id"));

		$form = $this->context->core->createRoleForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Update");
		$form->onSuccess[] = function($form) use ($repository)
		{
			$repository->save($form->entity);
			$form->getPresenter()->flashMessage("Role has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}

}
