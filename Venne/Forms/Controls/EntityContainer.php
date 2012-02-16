<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Forms\Containers\Doctrine;

use Doctrine;
use Venne;
use Nette;
use Nette\ComponentModel\IContainer;


/**
 * @author Filip Procházka
 *
 * @method Venne\Forms\EntityForm getForm() getForm()
 */
class EntityContainer extends Nette\Forms\Container
{

	/** @var object */
	private $entity;



	/**
	 * @param object $entity
	 */
	public function __construct($entity)
	{
		parent::__construct(NULL, NULL);
		$this->monitor('Venne\Forms\EntityForm');

		$this->entity = $entity;
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
		parent::attached($obj);

		if ($obj instanceof Venne\Forms\EntityForm) {
			$obj->getMapper()->assing($this->entity, $this);
		}
	}

}