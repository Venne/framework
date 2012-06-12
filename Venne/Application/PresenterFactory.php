<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Application;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PresenterFactory extends \Nette\Application\PresenterFactory
{


	/** @var \Nette\DI\Container */
	private $container;



	/**
	 * @param \Nette\DI\Container $container
	 * @param string $appDir
	 */
	public function __construct($appDir, \Nette\DI\Container $container)
	{
		parent::__construct($appDir, $container);

		$this->container = $container;
	}



	/**
	 * Create new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		try {
			$presenter = $this->container->getService($this->getPresenterService($name));
		} catch (\Nette\DI\MissingServiceException $e) {
			return parent::createPresenter($name);
		}
		
		if (method_exists($presenter, 'setContext')) {
			$this->container->callMethod(array($presenter, 'setContext'));
		}
		return $presenter;
	}
	
	
	/**
	 * Formats service name from it's presenter name
	 *
	 * 'Bar:Foo:FooBar' => 'bar_foo_fooBarPresenter'
	 *
	 * @param string $presenter
	 * @return string
	 */
	public function getPresenterService(& $name)
	{
		$arr = explode(":", $name);
		array_walk($arr, function(&$item, $index) use (&$arr){
			$arr[$index] = lcfirst($item);
		});
		return join(".", $arr) . 'Presenter';
	}

}

