<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Presenters;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminPresenter extends BasePresenter
{


	/** @persistent */
	public $mode = "";



	/**
	 * @return void
	 */
	public function startup()
	{
		/* Check database */
		if (!$this->context->createCheckConnection()) {
			if ($this->getName() != "Core:Admin:System:Database" && $this->getName() != "Core:Admin:Login") {
				$this->redirect(":Core:Admin:System:Database:");
			}
			$this->flashMessage("Database connection not found. Please fix it.", "warning");
		}

		/* Check updates */
		foreach ($this->context->findByTag("module") as $module => $item) {
			if (!version_compare($this->context->{$module}->getVersion(), $this->context->parameters["modules"][lcfirst(substr($module, 0, -6))]["version"], '==')) {
				if ($this->getName() != "Core:Admin:Modules:Default" && $this->getName() != "Core:Admin:Login") {
					$this->redirect(":Core:Admin:Modules:Default:");
				}
				$this->flashMessage("Some modules need update or downgrade own database. Please fix it.", "warning");
			}
		}

		parent::startup();
	}



	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 */
	public function checkRequirements($element)
	{
		if (!$this->getUser()->loggedIn && $this->getName() != "Core:Admin:Login") {
			if ($this->getUser()->logoutReason === \Nette\Security\User::INACTIVITY) {
				$this->flashMessage(_("You have been logged out due to inactivity. Please login again."), 'info');
			}

			$this->redirect(":Core:Admin:Login:", array('backlink' => $this->getApplication()->storeRequest()));
		}

		parent::checkRequirements($element);
	}



	/**
	 * Formats layout template file names.
	 *
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		return array($this->getContext()->parameters["libsDir"] . "/App/CoreModule/layouts/administration.latte");
	}



	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this["head"]->setTitle("Venne:CMS");
		$this["head"]->setRobots($this["head"]::ROBOTS_NOINDEX | $this["head"]::ROBOTS_NOFOLLOW);

		$this->template->adminMenu = new \App\CoreModule\Events\AdminEventArgs;
		$this->context->eventManager->dispatchEvent(\App\CoreModule\Events\AdminEvents::onAdminMenu, $this->template->adminMenu);
		$this->template->adminMenu = $this->template->adminMenu->getNavigations();
	}

}

