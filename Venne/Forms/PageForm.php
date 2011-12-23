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
	public function __construct(Mapping\EntityFormMapper $mapper, \Doctrine\ORM\EntityManager $entityManager, \App\CoreModule\BasePageEntity $entity)
	{
		parent::__construct($mapper, $entityManager, $entity);
		$this->onSave[] = \callback($this, "saveParams");
	}



	/**
	 * Application form constructor.
	 */
	public function startup()
	{
		parent::startup();

		$this->addGroup("Meta informations");
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

		$this->addGroup("URL");
		$this->addManyToOne("parent", "Parent content");
		$this->addText("localUrl", "URL")
				->setOption("description", "(example: 'contact')")
				->addRule(self::REGEXP, "URL can not contain '/'", "/^[a-zA-z0-9._-]*$/")
				->addConditionOn($this["parent"], ~self::EQUAL, false)
				->addRule(self::FILLED, "Nesmí být prázdný");

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
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		$this->presenter->context->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onCreate, $this->createArgs());

		if (!$this->presenter->context->parameters["website"]["multilang"]) {
			$this->removeGroup("Languages");
		}

		if (!$this->isSubmitted()) {
			$this->presenter->context->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onLoad, $this->createArgs());
		}

		$this->onSave[] = function($form) {
					$form->presenter->context->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onSave, $form->createArgs());
				};
	}



	public function createArgs()
	{
		$args = new \Venne\ContentExtension\EventArgs;
		$args->form = $this;
		$args->page = $this->entity->page;
		return $args;
	}



	public function addContentExtensionContainer($name, $label)
	{
		$this->addGroup($label)->setOption('container', \Nette\Utils\Html::el('fieldset')->class('extension')->id('extension-' . $name));
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
