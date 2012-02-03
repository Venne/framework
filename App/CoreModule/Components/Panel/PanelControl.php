<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Components\Panel;

use Venne;
use Venne\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PanelControl extends Control {


	/** @var bool */
	protected $show = true;



	public function getVisibility()
	{
		return $this->show;
	}



	public function setVisibility($show)
	{
		$this->show = $show;
	}



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
		$this->getPresenter()->context->authenticator->logout();
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



	public function render($type = NULL, $param = NULL)
	{
		if ($this->show) {
			parent::render($type, $param);
		}
	}

}
