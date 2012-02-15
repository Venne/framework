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
 * @persistent (vp)
 */
class UsersPresenter extends BasePresenter {


	/** @persistent */
	public $id;

	/** @persistent */
	public $page;

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $userRepository;



	public function startup()
	{
		parent::startup();
		$this->addPath("Users", $this->link(":Core:Admin:Security:Users:"));
		$this->userRepository = $this->context->core->userRepository;
	}



	public function actionCreate()
	{
		$this->addPath("new item", $this->link(":Core:Admin:Security:Users:create"));
	}



	public function actionEdit()
	{
		$this->addPath("edit" . " (" . $this->id . ")", $this->link(":Core:Admin:Security:Users:edit"));
	}



	public function handleDelete($id)
	{
		$this->userRepository->delete($this->userRepository->find($id));
		$this->flashMessage("User has been deleted", "success");
		$this->redirect("this");
	}



	public function createComponentForm()
	{
		$repository = $this->userRepository;
		$entity = $this->userRepository->createNew();

		$form = $this->context->core->createUserForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($repository)
		{
			$form->entity->enable = 1;
			$repository->save($form->entity);
			$form->presenter->flashMessage("User has been created", "success");
			$form->presenter->redirect("default");
		};
		return $form;
	}



	public function createComponentFormEdit()
	{
		$repository = $this->userRepository;
		$entity = $this->userRepository->find($this->getParameter("id"));

		$form = $this->context->core->createUserForm();
		$form->setEntity($entity);
		$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form) use ($repository)
		{
			$repository->save($form->entity);
			$form->presenter->flashMessage("User has been updated", "success");
			$form->presenter->redirect("this");
		};
		return $form;
	}



	public function createComponentVp()
	{
		$vp = new \Venne\Utils\VisualPaginator;
		$pg = $vp->getPaginator();
		$pg->setItemsPerPage(20);
		$pg->setItemCount($this->userRepository->createQueryBuilder("a")->select("COUNT(a.id)")->getQuery()->getSingleScalarResult());
		return $vp;
	}



	public function renderDefault()
	{
		$this->template->userRepository = $this->userRepository;
	}

}
