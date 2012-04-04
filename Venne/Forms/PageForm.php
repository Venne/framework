<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms;

use Nette\Application\UI\Presenter;

/**
 * @author	 Josef Kříž
 */
class PageForm extends EntityForm
{


	/**
	 * @param Mapping\EntityFormMapper $mapper
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(Mapping\EntityFormMapper $mapper, \Doctrine\ORM\EntityManager $entityManager)
	{
		parent::__construct($mapper, $entityManager);
		$this->onSuccess[] = \callback($this, "saveParams");
	}



	public function setEntity($entity)
	{
		if (!$entity instanceof \CoreModule\Entities\BasePageEntity) {
			throw new \Nette\InvalidArgumentException;
		}

		parent::setEntity($entity);
	}



	/**
	 * Application form constructor.
	 */
	public function startup()
	{
		parent::startup();

		$this->addGroup("Meta informations");
		$this->addText("title", "Title")->setRequired('Enter title');
		$this->addSelect("layoutFile", "Layout", $this->presenter->context->core->scannerService->getLayoutFiles());
		$this->addText("keywords", "Keywords");
		$this->addText("description", "Description");
		$this->addSelect("robots", "Robots")->setItems(array("index, follow", "noindex, follow", "index, nofollow", "noindex, nofollow",), false);

		$this->addGroup("URL");
		$this->addCheckbox("mainPage", "Main page");
		if (!$this->entity->translationFor) {
			$this->addManyToOne("parent", "Parent content", NULL, NULL, array("translationFor" => NULL));
		} else {
			$arr = array();
			if ($this->entity->parent) {
				$arr[0] = $this->entity->parent;
				if ($arr[0]->translationFor) {
					$arr[] = $arr[0]->translationFor->id;
				}
				foreach ($arr[0]->translations as $ent) {
					$arr[] = $ent->id;
				}
			}
			$arr = count($arr) > 1 ? $arr : NULL;

			$this->addManyToOne("parent", "Parent content", NULL, NULL, array("id" => $arr));
		}
		$this->addText("localUrl", "URL")->setOption("description", "(example: 'contact')")->addRule(self::REGEXP, "URL can not contain '/'", "/^[a-zA-z0-9._-]*$/");


		/* URL can be empty only on main page */
		if (!$this->entity->translationFor) {
			$this["localUrl"]->addConditionOn($this["parent"], ~self::EQUAL, false)->addRule(self::FILLED, "URL can be empty only on main page");
		} else if ($this->entity->translationFor && $this->entity->translationFor->parent) {
			$this["localUrl"]->addRule(self::FILLED, "URL can be empty only on main page");
		}


		/* languages */
		$this->addGroup("Languages");
		if ($this->entity->translationFor) {
			$this->addManyToMany("languages", "Content is in");
			//	$this->addManyToOne("translationFor", "Translation for", NULL, NULL, array("translationFor" => NULL));
		}

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
		$evm = $this->presenter->context->eventManager;

		$evm->dispatchEvent(\Venne\ContentExtension\Events::onContentExtensionCreate, $this->createArgs());

		if ($obj instanceof Presenter) {
			if (!$this->isSubmitted()) {
				$evm->dispatchEvent(\Venne\ContentExtension\Events::onContentExtensionLoad, $this->createArgs());
			} else {
				$evm->dispatchEvent(\Venne\ContentExtension\Events::onContentExtensionSave, $this->createArgs());
			}
		}
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
	 *
	 * @return type
	 */
	protected function getParams()
	{
		return array();
	}

}
