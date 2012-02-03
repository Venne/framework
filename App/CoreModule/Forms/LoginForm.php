<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Forms;

use Venne\ORM\Column;
use Nette\Utils\Html;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginForm extends \Venne\Application\UI\Form {


	public function startup()
	{
		$this->addText('username', 'Login')->setRequired('Please provide a username.');
		$this->addPassword('password', 'Password')->setRequired('Please provide a password.');
		$this->addCheckbox('remember', 'Remember me on this computer');
		$this->addSubmit("_submit", "Login");
	}



	public function handleSuccess()
	{
		try {
			$values = $this->getValues();
			$this->presenter->user->login($values->username, $values->password);

			if ($values->remember) {
				$this->presenter->user->setExpiration('+ 14 days', FALSE);
			} else {
				$this->presenter->user->setExpiration('+ 20 minutes', TRUE);
			}

			$backlink = $this->presenter->getParameter('backlink');
			$backlink = $this->presenter->getApplication()->restoreRequest($backlink);
			if ($backlink) {
				try {
					$this->presenter->redirect($this->presenter->getApplication()->restoreRequest($backlink));
				} catch (InvalidLinkException $e) {
					$this->presenter->redirect(':Core:Admin:Default:');
				}
			} else {
				$this->presenter->redirect(':Core:Admin:Default:');
			}
		} catch (\Nette\Security\AuthenticationException $e) {
			$this->getPresenter()->flashMessage($e->getMessage(), "warning");
		}
	}

}
