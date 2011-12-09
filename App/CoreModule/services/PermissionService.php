<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule;

use Venne;
use Nette\Object;

/**
 * @author Josef Kříž
 */
class PermissionService extends Object {


	/** @var \Venne\DI\Container */
	protected $context;

	/** @var \Doctrine\ORM\EntityManager */
	public $entityManager;



	public function __construct($context, $moduleName, \Doctrine\ORM\EntityManager $entityManager)
	{
		$this->context = $context;
		$this->entityManager = $entityManager;
	}



	/**
	 * @return \Venne\Doctrine\ORM\BaseRepository 
	 */
	protected function getRepository()
	{
		return $this->entityManager->getRepository("\\App\\CoreModule\\PermissionEntity");
	}

}
