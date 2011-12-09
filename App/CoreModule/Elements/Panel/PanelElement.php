<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\CoreModule;

use Venne;

/**
 * @author Josef Kříž
 */
class PanelElement extends \Venne\Application\UI\Element {


	public function startup()
	{
		parent::startup();
		$this->template->showAdminPanel = $this->getPresenter()->getSession("venne-panel")->closed;
	}


	public function handleLoginDo()
	{
		$data = $this["formLogin"]->getValues();
		try {
			$this->getPresenter()->getUser()->login($data['name'], $data['password']);
			$this->flashMessage("Login success");
		} catch (\Nette\Security\AuthenticationException $e) {
			$this->flashMessage("Login failed", "warning");
		}
		$this->redirect("this");
	}


	public function createComponentFormLogin($name)
	{
		$form = new \Venne\Application\UI\Form($this, $name);

		$form->addGroup("Login");
		$form->addText("name", "User name");
		$form->addPassword("password", "Password");

		$form->setCurrentGroup();
		$form->addSubmit("submit", "Login")->onClick[] = array($this, 'handleLoginDo');
		return $form;
	}


	public function handleLogin()
	{
		$this->template->login = true;
	}


	public function handleLogout()
	{
		$this->getPresenter()->getUser()->logout(true);
		$this->flashMessage("Logout success");
		$this->redirect("this");
	}


	public function handleClosePanel()
	{
		$panel = $this->getPresenter()->getSession("venne-panel");
		$panel->closed = true;
	}


	public function handleOpenPanel()
	{
		$panel = $this->getPresenter()->getSession("venne-panel");
		$panel->closed = false;
	}

}
