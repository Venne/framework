<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\WebsiteModule\AdminModule;

use \Nette\Application\UI\Form;

/**
 * @author Josef Kříž
 * 
 * @secured
 */
class LanguagePresenter extends \Venne\Application\UI\AdminPresenter {


	/** @persistent */
	public $id;

	/** @inject websiteForm */
	public $websiteForm;



	public function startup()
	{
		parent::startup();
		$this->addPath("Language setting", $this->link(":Website:Admin:Language:"));
	}



	public function createComponentForm($name)
	{
		$repository = $this->context->languageRepository;
		$entity = $repository->createNew();
		$em = $this->context->doctrineContainer->entityManager;

		$form = new \App\CoreModule\LanguageForm($entity, $this->context->doctrineContainer->entityFormMapper, $em);
		$form->setSuccessLink("default");
		$form->setFlashMessage("Language has been created");
		$form->setSubmitLabel("Create");
		$form->onSave[] = function($form) use ($repository) {
					$repository->save($form->entity);
				};
		return $form;
	}



	public function createComponentFormEdit($name)
	{
		$repository = $this->context->languageRepository;
		$entity = $repository->find($this->id);
		$em = $this->context->doctrineContainer->entityManager;

		$form = new \App\CoreModule\LanguageForm($entity, $this->context->doctrineContainer->entityFormMapper, $em);
		$form->setSuccessLink("this");
		$form->setFlashMessage("Language has been updated");
		$form->setSubmitLabel("Update");
		$form->onSave[] = function($form) use ($repository) {
					$repository->save($form->entity);
				};
		return $form;
	}



	public function beforeRender()
	{
		parent::beforeRender();
		$this->setTitle("Venne:CMS | Language administration");
		$this->setKeywords("language administration");
		$this->setDescription("Language administration");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);
	}



	public function handleDelete($id)
	{
		$this->context->languageRepository->delete($this->context->languageRepository->find($this->id));
		$this->flashMessage("Page has been deleted", "success");
		$this->redirect("this", array("id" => NULL));
	}



	public function renderDefault()
	{
		$this->template->table = $this->context->languageRepository->findAll();
	}

}
