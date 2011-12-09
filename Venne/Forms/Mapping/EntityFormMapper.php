<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Mapping;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Venne;
use Doctrine\Common\Persistence\ObjectManager;
use Nette;
use Nette\ComponentModel\IComponent;
use SplObjectStorage;



/**
 * @author Filip Procházka
 */
class EntityFormMapper extends Venne\Doctrine\Mapping\EntityMetadataMapper
{

	/** @var SplObjectStorage */
	private $assignment;



	/**
	 * @param ObjectManager $workspace
	 * @param TypeMapper $typeMapper
	 */
	public function __construct(ObjectManager $workspace, \Venne\Doctrine\Mapping\TypeMapper $typeMapper)
	{
		parent::__construct($workspace, $typeMapper);
		$this->assignment = new SplObjectStorage();
	}



	/**
	 * @param object $entity
	 * @param IComponent $component
	 * @return BaseMapper
	 */
	public function assing($entity, IComponent $component)
	{
		$this->assignment->attach($entity, $component);
		return $this;
	}



	/**
	 * @return SplObjectStorage
	 */
	public function getAssignment()
	{
		return $this->assignment;
	}



	/**
	 * @param object $entity
	 * @return IComponent
	 */
	public function getComponent($entity)
	{
		if (!$this->assignment->contains($entity)) {
			return NULL;
		}

		return $this->assignment->offsetGet($entity);
	}



	/************************ load & save to component ************************/



	/**
	 * @return array
	 */
	public function load()
	{
		$entities = array();
		foreach ($this->getAssignment() as $entity) {
			$container = $this->getComponent($entity);
			$entities[] = $entity;

			// fields
			foreach ($container->getControls() as $control) {
				if ($this->hasProperty($entity, $control->name)) {
					$this->loadProperty($entity, $control->name, $control->value);
				}
			}
		}

		return $entities;
	}



	/**
	 * @return array
	 */
	public function save()
	{
		$entities = array();
		foreach ($this->getAssignment() as $entity) {
			$container = $this->getComponent($entity);
			$entities[] = $entity;

			// fields
			foreach ($container->getControls() as $control) {
				if ($this->hasProperty($entity, $control->name)) {
					$control->value = $this->saveProperty($entity, $control->name);
				}
			}
		}

		return $entities;
	}



	/************************ validation ************************/



	/**
	 * @todo finish
	 *
	 * @param Validation\Result $result
	 * @param EntityForm $entityForm
	 */
	public function assignResult(Validation\Result $validationResult, EntityForm $entityForm)
	{
		foreach ($validationResult as $error) {
			if ($error->getInvalidObject()) {
				$container = $this->getComponent($error->getInvalidObject());

				if ($container) {
					if ($error->getPropertyName() && $control = $container->getComponent($error->getPropertyName(), FALSE)) {
						$control->addError($error->getMessage());
						continue;
					}

					$container->getForm()->addError('Error in ' . get_class($entity) . ': ' . $error->getMessage());
					continue;
				}
			}

			$entityForm->addError('Error in ' . get_class($entity) . ': ' . $error->getMessage());
		}
	}

}