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
class DefaultPresenter extends BasePresenter
{


	/** @persistent */
	public $page;

	/** @var \Venne\Doctrine\ORM\BaseRepository */
	protected $loginRepository;



	public function startup()
	{
		parent::startup();
		$this->addPath("General", $this->link(":Core:Admin:Security:Default:"));

		$this->loginRepository = $this->context->core->loginRepository;
	}



	public function handleDelete($id)
	{
		$repository = $this->context->core->loginRepository;

		$repository->delete($repository->find($id));
		$this->flashMessage("User has been logged out");
		$this->redirect("this");
	}



	public function handleReload($id)
	{
		$repository = $this->context->core->loginRepository;
		$entity = $repository->find($id);
		$entity->reload = true;

		$repository->save($entity);
		$this->flashMessage("User has been updated");
		$this->redirect("this");
	}



	public function createComponentVp()
	{
		$vp = new \Venne\Utils\VisualPaginator;
		$pg = $vp->getPaginator();
		$pg->setItemsPerPage(20);
		$pg->setItemCount($this->loginRepository->createQueryBuilder("a")->select("COUNT(a.id)")->getQuery()->getSingleScalarResult());
		return $vp;
	}



	public function renderDefault()
	{
		$this->template->loginRepository = $this->loginRepository;
	}

}
