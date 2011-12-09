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

use Nette\Application\UI;

/**
 * @author     Josef Kříž
 */
class EntityForm extends \Venne\Application\UI\Form {


	/** @var array of function(Form $form, $entity); Occurs when the form is submitted, valid and entity is saved */
	public $onSave = array();

	/** @var string key of application stored request */
	private $onSaveRestore;

	/** @var Mapping\EntityFormMapper */
	private $mapper;

	/** @var object */
	protected $entity;

	/** @var \Doctrine\ORM\EntityManager */
	protected $entityManager;



	/**
	 * @param object $entity
	 * @param Mapping\EntityFormMapper $mapper
	 */
	public function __construct($entity, Mapping\EntityFormMapper $mapper, $entityManager = NULL)
	{
		$this->mapper = $mapper;
		$this->entity = $entity;
		$this->entityManager = $entityManager;

		$this->getMapper()->assing($entity, $this);
		parent::__construct();
		$this->addSubmit("_submit")->onClick;
	}



	public function setSubmitLabel($label)
	{
		$this["_submit"]->caption = $label;
	}



	/**
	 * @return Mapping\EntityFormMapper
	 */
	public function getMapper()
	{
		return $this->mapper;
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if ($obj instanceof UI\Presenter) {
			$this->getMapper()->save();
		}

		parent::attached($obj);
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

		// load data to entity
		$entities = $this->getMapper()->load();

		// ensure all in entity manager
		foreach ($entities as $entity) {
			$this->onSave($this, $entity);
		}

		parent::fireEvents();

		if ($this->onSaveRestore) {
			$this->getPresenter()->getApplication()->restoreRequest($this->onSaveRestore);
		}

		if ($this->successLink && !$this->presenter->isAjax()) {
			$this->presenter->redirect($this->successLink, $this->successLinkParams);
		}
	}

	/* -------------------------- */



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addOneToOne($name)
	{
		$entity = $this->getMapper()->getAssocation($this->getEntity(), $name);
		return $this[$name] = new Containers\Doctrine\EntityContainer($entity);
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addManyToOneContainer($name)
	{
		$entity = $this->getMapper()->getAssocation($this->getEntity(), $name);
		return $this[$name] = new Containers\Doctrine\EntityContainer($entity);
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addManyToOne($name, $label = NULL, $items = NULL, $size = NULL, array $criteria = array(), array $orderBy = NULL, $limit = NULL, $offset = NULL)
	{
		$ref = $this->entity->getReflection()->getProperty($name);
		
		if($ref->hasAnnotation("Form")){
			$ref = $ref->getAnnotation("Form");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		}else{
			$ref = $ref->getAnnotation("ManyToOne");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		}

		$items = $this->entityManager->getRepository($class)->findBy($criteria, $orderBy, $limit, $offset);

		$this[$name] = new Controls\ManyToOne($label, $items, $size);
		$this[$name]->setPrompt("---------");
		return $this[$name];
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addManyToMany($name, $label = NULL, $items = NULL, $size = NULL, array $criteria = array(), array $orderBy = NULL, $limit = NULL, $offset = NULL)
	{
		$ref = $this->entity->getReflection()->getProperty($name);
		
		if($ref->hasAnnotation("Form")){
			$ref = $ref->getAnnotation("Form");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		}else{
			$ref = $ref->getAnnotation("ManyToMany");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		}

		$items = $this->entityManager->getRepository($class)->findBy($criteria, $orderBy, $limit, $offset);

		$this[$name] = new Controls\ManyToMany($label, $items, $size);
		return $this[$name];
	}

}
