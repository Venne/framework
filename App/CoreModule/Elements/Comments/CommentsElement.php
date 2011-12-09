<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Elements;

use Venne;

/**
 * @author Josef Kříž
 */
class CommentsElement extends \Venne\ContentExtension\BaseElement {


	public function startup()
	{
		parent::startup();

		$item = $this->context->services->comments->getRepository()->findOneBy(
				array(
					"moduleItemId" => $this->moduleItemId,
					"moduleName" => $this->moduleName,
				));
		if ($item) {
			$this->template->show = true;

			$this->template->items = $this->context->services->commentItems->getRepository()->findBy(
					array(
						"key" => $this->moduleName . "-" . $this->moduleItemId
					));
		}
	}


	public function createComponentForm($name)
	{
		$form = new Venne\Application\UI\Form($this, $name);
		$form->addText("author", "Name")->addRule(Venne\Application\UI\Form::FILLED, "Enter name");
		$form->addTextArea("text", "Comment")->addRule(Venne\Application\UI\Form::FILLED, "Enter comment");
		$form->addSubmit("submit", "Send");
		$form->onSuccess[] = array($this, "handleSave");
		return $form;
	}


	public function handleSave($form)
	{
		$entity = $this->context->services->commentItems->create($form->getValues(), true);
		$entity->key = $this->moduleName . "-" . $this->moduleItemId;
		$entity->order = 1;
		$this->context->doctrineContainer->entityManager->flush();

		$this->flashMessage("Comment has been saved", "success");
		$this->redirect("this");
	}


	public function handleDelete($id)
	{
		$item = $this->template->items = $this->context->services->commentItems->getRepository()->find($id);
		
		$this->context->services->commentItems->delete($item);

		$this->flashMessage("Comment has been removed", "success");
		$this->redirect("this");
	}

}
