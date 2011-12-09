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
class PageForm extends EntityForm {



	/**
	 * @param object $entity
	 * @param Mapping\EntityFormMapper $mapper
	 */
	public function __construct(\App\CoreModule\BasePageEntity $entity, Mapping\EntityFormMapper $mapper, $entityManager = NULL)
	{
		parent::__construct($entity, $mapper, $entityManager);
		$this->onSave[] = \callback($this, "saveParams");
	}



	/**
	 * Application form constructor.
	 */
	public function startup()
	{
		parent::startup();
		$this->onSave[] = function($form) {
					$form->presenter->context->doctrineContainer->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onSave, $form->createArgs());
				};

		$this->addGroup();
		$this->addText("title", "Title")
				->setRequired('Enter title');
		$this->addText("keywords", "Keywords");
		$this->addText("description", "Description");
		$this->addSelect("robots", "Robots")->setItems(array(
			"index, follow",
			"noindex, follow",
			"index, nofollow",
			"noindex, nofollow",
				), false);

		$this->addGroup("Languages");
		$this->addManyToMany("languages", "Content is in");
		$this->addManyToOne("translationFor", "Translation for", NULL, NULL, array("translationFor" => NULL));
		$this->setCurrentGroup();
	}



	/**
	 * Update link params
	 */
	public function saveParams()
	{
		$this->entity->setParams($this->getParams());
		//$this->entity->type = "\\\\" . get_class($this->entity);
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		$this->presenter->context->doctrineContainer->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onCreate, $this->createArgs());
		
		if(!$this->presenter->context->params["website"]["multilang"]){
			$this->removeGroup("Languages");
		}
			
		if (!$this->isSubmitted()) {
			$this->presenter->context->doctrineContainer->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onLoad, $this->createArgs());
		}
	}



	public function createArgs()
	{
		$args = new \Venne\ContentExtension\EventArgs;
		$args->form = $this;
		$args->page = $this->entity->page;
		return $args;
	}



	public function addContentExtensionContainer($name)
	{
		return $this->addContainer("_module_" . $name);
	}



	public function getContentExtensionContainer($name)
	{
		return $this["_module_" . $name];
	}



	/**
	 * Params for new page
	 * @return type 
	 */
	protected function getParams()
	{
		return array();
	}

}
