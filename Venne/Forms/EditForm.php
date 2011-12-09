<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms;

/**
 * @author     Josef Kříž
 */
class EditForm extends \Venne\Application\UI\Form {



	/**
	 * Application form constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->addSubmit("_submit");
	}



	/**
	 * Fires submit/click events.
	 *
	 * @todo mapper->assignResult()
	 *
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;
		}

		if ($this["_submit"]->isSubmittedBy()) {
			if($this->save() === NULL){
				if ($this->flash){
					$this->getPresenter()->flashMessage($this->flash, $this->flashStatus);
				}
				if ($this->successLink && !$this->presenter->isAjax()) {
					$this->presenter->redirect($this->successLink, $this->successLinkParams);
				}
			}
		}

		parent::fireEvents();

		//$this->getPresenter()->redirect('this');
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if ($obj instanceof \Nette\Application\UI\Presenter) {
			$this->load();
		}

		parent::attached($obj);
	}



	public function setSuccessLink($link, $params = NULL)
	{
		$this->successLink = $link;
		$this->successLinkParams = (array) $params;
	}



	public function setFlashMessage($value, $status = NULL)
	{
		if ($status) {
			$this->flashStatus = $status;
		}
		$this->flash = $value;
	}



	public function setSubmitLabel($label)
	{
		$this["_submit"]->caption = $label;
	}



	public function load()
	{
		
	}



	public function save()
	{
		
	}

}
