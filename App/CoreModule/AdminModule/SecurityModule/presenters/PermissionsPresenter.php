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

use Venne\Doctrine\ORM\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class PermissionsPresenter extends BasePresenter {


	/** @persistent */
	public $role;

	/** @var BaseRepository */
	protected $permissionRepository;

	/** @var BaseRepository */
	protected $roleRepository;



	public function startup()
	{
		parent::startup();

		$this->addPath("Permissions", $this->link(":Core:Admin:Security:Permissions:"));

		if (!$this->role) {
			$this->role = "guest";
		}

		$this->permissionRepository = $this->context->core->permissionRepository;
		$this->roleRepository = $this->context->core->roleRepository;


		$role = $this->roleRepository->findOneByName($this->role);

		$allowed = $this->permissionRepository->findBy(array("role" => $role->id));
		$this->template->allowed = array();
		foreach ($allowed as $item) {
			$this->template->allowed[$item->resource][NULL] = $item;
		}

		$this->template->roles = $this->context->core->roleRepository->findAll();
		$role = $this->roleRepository->findOneByName($this->role);


		$this->template->authorizator = $this->context->authorizatorFactory->getPermissionsByRoles(array($this->role));
		$this->template->permissions = $this->getPermissions($this->template->authorizator);
	}



	public function getPermissions(\Nette\Security\Permission $permission)
	{
		$ret = array("root" => array());
		$permissions = $permission->getResources();

		foreach ($permissions as $item) {
			$parent = substr($item, 0, strrpos($item, "\\"));

			if (!$parent) {
				$ret["root"][] = $item;
			} else {
				if (!isset($ret[$parent])) {
					$ret[$parent] = array();
				}

				$ret[$parent][] = $item;
			}
		}

		return $ret;
	}



	public function createComponentFormRole($name)
	{
		$form = new \Venne\Application\UI\Form;
		$form->addGroup("Role");
		$form->addSelect("role", "Role", $this->roleRepository->fetchPairs("name", "name"));
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
		$repository = $this->permissionRepository;
		$session = $this->context->session->getSection("Venne.Security.Authorizator");

		foreach ($menu as $key => $item) {
			if ($form["allow_" . str_replace("\\", "_", $item)]->isSubmittedBy()) {
				$data = array("resource" => $item, "role" => $this->roleRepository->findOneByName($this->role), "allow" => true);
				$permission = $repository->createNew(array(), $data);
				$repository->save($permission);
				$session->remove();
				$this->permissionInvalidateForUnlogged();

				$this->flashMessage("Permission has been saved", "success");
				$this->redirect("this");
			}
			if ($form["deny_" . str_replace("\\", "_", $item)]->isSubmittedBy()) {
				$data = array("resource" => $item, "role" => $this->roleRepository->findOneByName($this->role), "allow" => false);
				$permission = $repository->createNew(array(), $data);
				$repository->save($permission);
				$session->remove();
				$this->permissionInvalidateForUnlogged();

				$this->flashMessage("Permission has been saved", "success");
				$this->redirect("this");
			}
			if ($form["delete_" . str_replace("\\", "_", $item)]->isSubmittedBy()) {
				$item = $repository->findOneBy(array("resource" => $item, "role" => $this->roleRepository->findOneByName($this->role)->id, "privilege" => NULL));
				$repository->delete($item);
				$session->remove();
				$this->permissionInvalidateForUnlogged();

				$this->flashMessage("Permission has been deleted", "success");
				$this->redirect("this");
			}
			if (isset($this->template->permissions[$item])) {
				$this->formSaveRecursion($form, $this->template->permissions[$item]);
			}
		}
	}



	public function permissionInvalidateForUnlogged()
	{
		$repository = $this->context->core->loginRepository;

		foreach ($repository->findBy(array("user"=>NULL)) as $entity){
		$entity->valid = false;
		$repository->save($entity);
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
