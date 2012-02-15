<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace App\CoreModule\Subscribers;

use Doctrine\Common\EventSubscriber;
use Venne\Module\Events\Events;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleSubscriber implements EventSubscriber {

	/** @var \Venne\Module\ResourcesManager */
	protected $resourcesManager;

	/** @var \SystemContainer|\Nette\DI\Container */
	protected $context;


	/**
	 * Constructor.
	 *
	 * @param \Venne\Module\ResourcesManager $resourcesManager
	 */
	public function __construct(\Nette\DI\Container $context, \Venne\Module\ResourcesManager $resourcesManager)
	{
		$this->context = $context;
		$this->resourcesManager = $resourcesManager;
	}



	/**
	 * Array of events.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(Events::onUpdateFlag);
	}



	/**
	 * onUpdateFlag event.
	 */
	public function onUpdateFlag()
	{
		/* check resources */
		$this->resourcesManager->checkResources();

		/* invalidate all users */
		foreach($this->context->core->loginRepository->findAll() as $entity){
			$entity->reload = true;
		}
		$this->context->entityManager->flush();
	}

}
