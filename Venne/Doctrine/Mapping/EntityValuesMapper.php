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

use Venne;
use Nette;

/**
 * @author Filip Procházka
 */
class EntityValuesMapper extends EntityMetadataMapper {



	/**
	 * @param object $entity
	 * @return array
	 */
	public function load($entity, $data)
	{
		foreach ($data as $property => $value) {
			if ($this->hasProperty($entity, $property)) {
				$this->loadProperty($entity, $property, $value);
				continue;
			}

			if ($this->hasAssocation($entity, $property)) {
				$this->clearAssociation($entity, $property);
				foreach ($value as $element) {
					$this->addAssociationElement($entity, $property, $element);
				}
				continue;
			}
			
			throw new Nette\InvalidArgumentException("Given data contains unknown field '" . $property . "'.");
		}
	}



	/**
	 * @param object $entity
	 * @return $data
	 */
	public function save($entity)
	{
		$data = array();
		$meta = $this->getMetadata($entity);

		foreach ($meta->getFieldNames() as $fieldName) {
			$data[$fieldName] = $this->saveProperty($entity, $fieldName);
		}

		foreach ($meta->getAssociationNames() as $assocName) {
			$data[$assocName] = $this->getAssociationElements($entity, $assocName);
		}

		return $data;
	}



	/**
	 * @param \Venne\Doctrine\ORM\BaseEntity $entity
	 * @param string $property
	 * @return bool
	 */
	public function hasProperty($entity, $property)
	{
		return isset($entity->{$property});
	}



	public function loadProperty($entity, $property, $value)
	{
		$entity->{$property} = $value;
	}

}