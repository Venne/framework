<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Mapping;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Venne;
use Venne\Tools\Mixed;
use Nette;


/**
 * @author Filip Procházka
 */
abstract class EntityMetadataMapper extends Nette\Object
{

	/** @var ObjectManager */
	private $workspace;

	/** @var TypeMapper */
	private $typeMapper;



	/**
	 * @param ObjectManager $workspace
	 * @param TypeMapper $typeMapper
	 */
	public function __construct(ObjectManager $workspace, TypeMapper $typeMapper)
	{
		$this->workspace = $workspace;
		$this->typeMapper = $typeMapper;
	}



	/**
	 * @param string|object $entity
	 * @return ClassMetadata
	 */
	protected function getMetadata($entity)
	{
		$entity = is_object($entity) ? get_class($entity) : $entity;
		return $this->workspace->getClassMetadata($entity);
	}



	/**
	 * @param string|object $entity
	 * @return ClassMetadata
	 */
	public function getEntityMetadata($entity)
	{
		$entity = is_object($entity) ? get_class($entity) : $entity;
		return $this->workspace->getClassMetadata($entity);
	}



	/************************ fields ************************/


	/**
	 * @param object $entity
	 * @param string $field
	 * @param mixed $data
	 * @return void
	 */
	protected function loadField($entity, $field, $data)
	{
		$meta = $this->getMetadata($entity);
		$propMapping = $meta->getFieldMapping($field);

		$data = $this->typeMapper->load($meta->getFieldValue($entity, $field), $data, $propMapping['type']);
		$meta->setFieldValue($entity, $field, $data);
	}



	/**
	 * @param object $entity
	 * @param string $field
	 * @return mixed
	 */
	protected function saveField($entity, $field)
	{
		$meta = $this->getMetadata($entity);
		$propMapping = $meta->getFieldMapping($field);

		return $this->typeMapper->save($meta->getFieldValue($entity, $field), $propMapping['type']);
	}



	/**
	 * @param object $entity
	 * @param string $field
	 */
	protected function hasField($entity, $field)
	{
		return $this->getMetadata($entity)->hasField($field);
	}



	/************************ associations ************************/


	/**
	 * @param object $entity
	 * @param string $association
	 * @return bool
	 */
	public function hasAssociation($entity, $association)
	{
		return $this->getMetadata($entity)->hasAssociation($association);
	}



	/**
	 * @param object $entity
	 * @param string $association
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getAssociation($entity, $association)
	{
		$meta = $this->getMetadata($entity);
		if (!$this->hasAssociation($entity, $association)) {
			throw new Nette\InvalidArgumentException("Entity '" . get_class($entity) . "' has no association '" . $association . "'.");
		}

		return $meta->getFieldValue($entity, $association);
	}



	/**
	 * @param object $entity
	 * @param string $association
	 */
	public function clearAssociation($entity, $association)
	{
		$this->getAssociation($entity, $association)->clear();
	}



	/**
	 * @param object $entity
	 * @param string $association
	 * @param object $element
	 */
	public function addAssociationElement($entity, $association, $element)
	{
		$meta = $this->getMetadata($entity);
		$assocMapping = $meta->getAssociationMapping($association);

		if (!$entity instanceof $assocMapping['targetEntity']) {
			$declaringClass = $meta->getReflectionProperty($association)->getDeclaringClass();
			throw new Kdyby\InvalidArgumentException("Collection " . $declaringClass->getName() . '::$' . $association . " cannot contain entity of type '" . get_class($entity) . "'.");
		}

		$this->getAssociation($entity, $association)->add($element);
	}



	/**
	 * @param object $entity
	 * @param string $association
	 * @return array
	 */
	public function getAssociationElements($entity, $association)
	{
		$collection = $this->getMetadata($entity)->getFieldValue($entity, $association);
		return $collection->toArray();
	}



	/************* MY *************/

	public function hasProperty($entity, $name)
	{
		return isset($entity->{$name});
	}



	public function loadProperty($entity, $name, $value)
	{
		$entity->{$name} = $value;
	}



	public function saveProperty($entity, $name)
	{
		return $entity->{$name};
	}

}