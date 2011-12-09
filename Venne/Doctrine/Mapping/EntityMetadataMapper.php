<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
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
	 * @param string $assocation
	 * @return bool
	 */
	protected function hasAssocation($entity, $assocation)
	{
		return $this->getMetadata($entity)->hasAssociation($assocation);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return Collection
	 */
	public function getAssocation($entity, $assocation)
	{
		$meta = $this->getMetadata($entity);
		if (!$this->hasAssocation($entity, $assocation)) {
			throw new Nette\InvalidArgumentException("Entity '" . get_class($entity) . "' has no association '" . $assocation . "'.");
		}

		return $meta->getFieldValue($entity, $assocation);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 */
	protected function clearAssociation($entity, $assocation)
	{
		$this->getAssocation($entity, $assocation)->clear();
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @param object $data
	 */
	protected function addAssociationElement($entity, $assocation, $element)
	{
		$meta = $this->getMetadata($entity);
		$assocMapping = $meta->getAssociationMapping($assocation);

		if (!$entity instanceof $assocMapping['targetEntity']) {
			$declaringClass = $meta->getReflectionProperty($assocation)->getDeclaringClass();
			throw new Nette\InvalidArgumentException("Collection " . $declaringClass->getName() . '::$' . $assocation . " cannot contain entity of type '" . get_class($entity) . "'.");
		}

		$this->getAssocation($entity, $assocation)->add($element);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return array
	 */
	protected function getAssociationElements($entity, $assocation)
	{
		$collection = $this->getMetadata($entity)->getFieldValue($entity, $assocation);
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