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
	public $mode = "";



	/**
	 * @return void
	 */
	public function startup()
	{
		/* Check database */
		if (!$this->context->doctrineContainer->checkConnection()) {
			if (substr($this->getName(), 0, 13) != "System:Admin:" && $this->getName() != "Core:Admin:Login") {
				$this->redirect(":System:Admin:Database:");
			}
			$this->flashMessage("Database connection not found. Please fix it.", "warning");
		}

		/* Check updates */
		foreach ($this->context->findByTag("module") as $module => $item) {
			if (!version_compare($this->context->{$module}->getVersion(), $this->context->parameters["modules"][lcfirst(substr($module, 0, -6))]["version"], '==')) {
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



	/**
	 * Common render method.
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this->setTitle("Venne:CMS");
		$this->setRobots(self::ROBOTS_NOINDEX | self::ROBOTS_NOFOLLOW);

		$this->template->adminMenu = new \App\CoreModule\Event\EventArgs;
		$this->context->eventManager->dispatchEvent(\App\CoreModule\Events::onAdminMenu, $this->template->adminMenu);
		$this->template->adminMenu = $this->template->adminMenu->getNavigations();
	}

}

