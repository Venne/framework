<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application\UI;

use Venne;

/**
 * @author Josef Kříž
 */
class AdminPresenter extends \Venne\Application\UI\Presenter {


	/** @persistent */
	public $mode;


	public function startup()
	{
		/*
		 * Set default mode
		 */
		if(!$this->mode){
			$this->mode = $this->context->params['mode'];
		}
		
		/*
		 * Login
		 */
		if (!$this->getUser()->isLoggedIn() && $this->getName() != "Core:Admin:Login") {
			$this->redirect(":Core:Admin:Login:");
		}

		/*
		 * Check database
		 */
		if (!$this->context->doctrineContainer->checkConnection()) {
			if (substr($this->getName(), 0, 13) != "System:Admin:" && $this->getName() != "Core:Admin:Login") {
				$this->redirect(":System:Admin:Database:");
			}
			$this->flashMessage("Database connection not found. Please fix it.", "warning");
		}

		/*
		 * Check updates
		 */
		foreach ($this->context->modules->getModules() as $module => $item) {
			if (!version_compare($this->context->modules->{$module}->getVersion(), $item["version"], '==')) {
				if ($this->getName() != "Modules:Admin:Default" && $this->getName() != "Core:Admin:Login") {
					$this->redirect(":Modules:Admin:Default:");
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
			if ($this->getUser()->logoutReason === \Nette\Http\User::INACTIVITY) {
				$this->flashMessage(_("You have been logged out due to inactivity. Please login again."), 'info');
			}

			$this->redirect(":Core:Admin:Login:", array('backlink' => $this->getApplication()->storeRequest()));
		}

		parent::checkRequirements($element);
	}



	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->adminMenu = new \App\CoreModule\Event\EventArgs;
		$this->context->doctrineContainer->eventManager->dispatchEvent(\App\CoreModule\Events::onAdminMenu, $this->template->adminMenu);
		$this->template->adminMenu = $this->template->adminMenu->getNavigations();
	}

}

