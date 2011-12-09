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
class PermissionsPresenter extends BasePresenter {


	/** @persistent */
	public $role;


	public function startup()
	{
		parent::startup();

		if (!$this->role) {
			$this->role = "guest";
		}

		$this->addPath("Permissions", $this->link(":Security:Admin:Permissions:"));

		$role = $this->context->roleRepository->findOneByName($this->role);

		$allowed = $this->context->permissionRepository->findBy(array("role"=>$role->id));
		$this->template->allowed = array();
		foreach ($allowed as $item) {
			$this->template->allowed[$item->resource][NULL] = $item;
		}

		$this->template->roles = $this->context->roleRepository->findAll();
		$role = $this->context->roleRepository->findOneByName($this->role);

		
		$permissions = new \App\SecurityModule\Permission;
		foreach($this->context->modules->getModules() as $module=>$item){
			$this->context->modules->{$module}->setPermissions($this->context, $permissions);
		}
		$this->template->permissions = $permissions->getResources();
	}


	public function createComponentFormRole($name)
	{
		$form = new \Venne\Application\UI\Form;
		$form->addGroup("Role");
		$form->addSelect("role", "Role", $this->context->roleRepository->fetchPairs("name", "name"));
		$form->addSubmit("submit", "Select");
		$form->onSuccess[] = array($this, "handleSaveRole");
		return $form;
	}


	public function handleSaveRole($form)
	{
		$this->role = $form["role"]->getValue();
		$this->redirect("this");
	}


	public function createComponentForm($name)
	{
		$form = new \Venne\Application\UI\Form($this, $name);
		$this->formRecursion($form, $this->template->permissions["root"]);
		$form->onSuccess[] = array($this, "handleSave");
		return $form;
	}


	public function formRecursion($form, $menu)
	{
		if ($menu) {
			foreach ($menu as $item) {
				$form->addSubmit("allow_" . str_replace("\\", "_", $item), "Allow");
				$form->addSubmit("deny_" . str_replace("\\", "_", $item), "Deny");
				$form->addSubmit("delete_" . str_replace("\\", "_", $item), "Delete")->getControlPrototype()->class = "grey";
				if (isset($this->template->permissions[$item])) {
					$this->formRecursion($form, $this->template->permissions[$item]);
				}
			}
		}
	}


	public function formSaveRecursion($form, $menu)
	{
		$repository = $this->context->permissionRepository;
		$session = $this->context->session->getSection("Venne.Security.Authorizator");
		
		foreach ($menu as $key => $item) {
			if ($form["allow_" . str_replace("\\", "_", $item)]->isSubmittedBy()) {
				$data = array(
					"resource" => $item,
					"role" => $this->context->roleRepository->findOneByName($this->role),
					"allow" => true
				);
				$permission = $repository->createNew(array(), $data);
				$repository->save($permission);
				$session->remove();
				
				$this->flashMessage("Permission has been saved", "success");
				$this->redirect("this");
			}
			if ($form["deny_" . str_replace("\\", "_", $item)]->isSubmittedBy()) {
				$data = array(
					"resource" => $item,
					"role" => $this->context->roleRepository->findOneByName($this->role),
					"allow" => false
				);
				$permission = $repository->createNew(array(), $data);
				$repository->save($permission);
				$session->remove();
				
				$this->flashMessage("Permission has been saved", "success");
				$this->redirect("this");
			}
			if ($form["delete_" . str_replace("\\", "_", $item)]->isSubmittedBy()) {
				$item = $this->context->permissionRepository->findOneBy(array("resource" => $item, "role" => $this->context->roleRepository->findOneByName($this->role)->id, "privilege" => NULL));
				$repository->delete($item);
				$session->remove();

				$this->flashMessage("Permission has been deleted", "success");
				$this->redirect("this");
			}
			if (isset($this->template->permissions[$item])) {
				$this->formSaveRecursion($form, $this->template->permissions[$item]);
			}
		}
	}


	public function handleSave()
	{
		$this->formSaveRecursion($this["form"], $this->template->permissions["root"]);
	}


	public function renderDefault()
	{
		$this["formRole"]["role"]->setValue($this->role);
		$this->template->role = $this->role;
	}

}
