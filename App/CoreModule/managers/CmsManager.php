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
use Venne\DI\Container;
use Venne\Forms\PageForm;
use \Closure;

/**
 * @author Josef Kříž
 */
class CmsManager extends Object {


	/** @var Container */
	protected $context;

	/** @var \Doctrine\Common\EventManager */
	protected $evm;

	/** @var array */
	protected $contentTypes = array();

	/** @var array */
	protected $elements = array();



	public function __construct(Container $context)
	{
		$this->context = $context;
		$this->evm = $this->context->doctrineContainer->eventManager;
	}

	/* --------------------- Configuration ------------------------- */



	public function addEventListener($events, $listener)
	{
		$this->evm->addEventListener($events, $listener);
	}



	public function addEventSubscriber($subscriber)
	{
		$this->evm->addEventSubscriber($subscriber);
	}



	public function addContentType($name, $label, array $pageParams, Closure $formFactory, Closure $entityFactory)
	{
		$this->contentTypes[$name] = array(
			"label" => $label,
			"form" => $formFactory,
			"entity" => $entityFactory,
			"params" => $pageParams
		);
	}



	public function addRoute(Application\IRouter $route)
	{
		$container->application->router[] = $route;
	}



	public function addService($name, Closure $factory)
	{
		$this->context->addService($name . "Service", $factory);
	}



	public function addRepository($name, Closure $factory)
	{
		$this->context->addService($name . "Repository", $factory);
	}



	public function addManager($name, Closure $factory)
	{
		$this->context->addService($name . "Manager", $factory);
	}



	public function addElement($name, Closure $factory)
	{
		$this->elements[$name] = $factory;
	}

	/* ----------------------- get --------------------------- */



	/**
	 * Get Content Types as array
	 * @return array
	 */
	public function getContentTypes()
	{
		$ret = array();
		foreach ($this->contentTypes as $key => $item) {
			$ret[$key] = $item["label"];
		}
		return $ret;
	}



	public function hasContentType($type)
	{
		return isset($this->contentTypes[$type]);
	}



	public function getContentForm($type, $entity)
	{
		$closure = $this->contentTypes[$type]["form"];
		return $closure($entity);
	}



	public function getContentEntity($type)
	{
		$closure = $this->contentTypes[$type]["entity"];
		return $closure();
	}



	public function getContentParams($type)
	{
		return $this->contentTypes[$type]["params"];
	}



	/**
	 * Return instance of element
	 * @param string $name 
	 */
	public function getElementInstance($name, $args = array())
	{
		$closure = $this->elements[$name];
		return call_user_func_array($closure, $args);
	}

}

