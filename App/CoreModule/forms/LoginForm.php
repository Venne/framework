<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\SecurityModule;

use Venne\ORM\Column;
use Nette\Utils\Html;

/**
 * @author Josef Kříž
 */
class LoginForm extends \Venne\Forms\EditForm {



	public function startup()
	{
		$this->addText('username', 'Username:')
				->setRequired('Please provide a username.');

		$this->addPassword('password', 'Password:')
				->setRequired('Please provide a password.');

		$this->addCheckbox('remember', 'Remember me on this computer');
	}



	public function fireEvents()
	{
		try {
			$values = $this->getValues();
			$this->presenter->user->login($values->username, $values->password);

			if ($values->remember) {
				$this->presenter->user->setExpiration('+ 14 days', FALSE);
			} else {
				$this->presenter->user->setExpiration('+ 20 minutes', TRUE);
			}

			$backlink = $this->presenter->getParam('backlink');
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
			$this->addError($e->getMessage());
		}
	}

}
