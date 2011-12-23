<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine;

use Venne;

/**
 * @author Josef Kříž
 * 
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 * @property-read \Doctrine\DBAL\Schema\AbstractSchemaManager $schemaManager
 * @property-read \Venne\Forms\Mapping\EntityFormMapper $entityFormMapper
 * @property-read \Doctrine\Common\EventManager $eventManager
 */
class Container extends \Nette\DI\Container {


	/** @var \Nette\DI\Container */
	private $context;



	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\Nette\DI\Container $context)
	{
		$this->context = $context;
	}



	public function checkConnectionErrorHandler()
	{
		
	}



	public function checkConnection()
	{
		$connection = $this->context->entityManager->getConnection();
		if (!$connection->isConnected()) {
			$old = set_error_handler(array($this, 'checkConnectionErrorHandler'));
			try {
				$connection->connect();
			} catch (\PDOException $ex) {
				set_error_handler($old);
				return false;
			}
			set_error_handler($old);
		}
		return true;
	}

}