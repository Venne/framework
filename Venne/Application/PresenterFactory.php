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
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PresenterFactory extends \Nette\Application\PresenterFactory
{

	/** @var array */
	protected $presentersByClass;

	/** @var array */
	protected $presentersByName;

	/** @var \Nette\DI\Container|\SystemContainer */
	protected $container;


	/**
	 * @param \Nette\DI\Container $container
	 */
	function __construct($baseDir, \Nette\DI\Container $container)
	{
		parent::__construct($baseDir, $container);

		$this->container = $container;
	}


	/**
	 * @param $name
	 * @param $closure
	 */
	public function addPresenter($class, $name)
	{
		$this->presentersByClass[$class] = $name;
		$this->presentersByName[$name] = $class;
	}


	/**
	 * Create new presenter instance.
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$presenterName = $this->formatServiceNameFromPresenter($name);

		if (isset($this->presentersByName[$presenterName])) {
			$presenter = $this->container->getService($presenterName);

			if (method_exists($presenter, 'setContext')) {
				$this->container->callMethod(array($presenter, 'setContext'));
			}
		} else {
			$presenter = parent::createPresenter($name);
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
	public function formatServiceNameFromPresenter($presenter)
	{
		return Strings::replace($presenter, '/(^|:)+(.)/', function ($match)
		{
			return (':' === $match[1] ? '.' : '') . strtolower($match[2]);
		}) . 'Presenter';
	}


	/**
	 * Formats service name from it's presenter name
	 *
	 * 'Bar:Foo:FooBar' => 'bar_foo_fooBarPresenter'
	 *
	 * @param string $presenter
	 * @return string
	 */
	public function formatPresenterFromServiceName($name)
	{
		return Strings::replace(substr($name, 0, -9), '/(^|\\.)+(.)/', function ($match)
		{
			return ('.' === $match[1] ? ':' : '') . strtoupper($match[2]);
		});
	}


	public function getPresenterClass(& $name)
	{
		if (isset($this->presentersByName[$name])) {
			$service = $this->getPresenterService($name);

			return get_class($this->container->getService($service));
		} else {
			return parent::getPresenterClass($name);
		}
	}


	public function formatPresenterClass($presenter)
	{
		$name = $this->formatServiceNameFromPresenter($presenter);

		if (isset($this->presentersByName[$name])) {
			return get_class($this->container->getService($name));
		} else {
			return parent::formatPresenterClass($presenter);
		}
	}


	public function unformatPresenterClass($class)
	{
		if (isset($this->presentersByClass[$class])) {
			$name = $this->presentersByClass[$class];
			return $this->formatPresenterFromServiceName($name);
		} else {
			return parent::unformatPresenterClass($class);
		}
	}


	public function formatPresenterFile($presenter)
	{
		$service = $this->formatPresenterFromServiceName($presenter);

		if ($this->container->hasService($service)) {
			return get_class($this->container->getService($service));
		}

		return parent::formatPresenterFile($presenter);
	}
}

