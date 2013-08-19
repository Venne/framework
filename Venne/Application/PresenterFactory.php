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

use Nette\Callback;
use Nette\DI\Container;
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

	/** @var Container|\SystemContainer */
	protected $container;


	/**
	 * @param $baseDir
	 * @param Container $container
	 */
	public function __construct($baseDir, Container $container)
	{
		parent::__construct($baseDir, $container);

		$this->container = $container;
	}


	/**
	 * @param $class
	 * @param $name
	 */
	public function addPresenter($class, $name)
	{
		$this->presentersByClass[$class] = $name;
		$this->presentersByName[$name] = $class;
	}


	/**
	 * @return array
	 */
	public function getPresenters()
	{
		return $this->presentersByClass;
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
			$presenter = $presenter instanceof Callback ? $presenter->invoke() : $presenter;
		} else if (isset($this->presentersByName[$presenterName . 'Factory'])) {
			$presenter = $this->container->getService($presenterName . 'Factory')->invoke();
		} else {
			return parent::createPresenter($name);
		}

		foreach (array_reverse(get_class_methods($presenter)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->container->callMethod(array($presenter, $method));
			}
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
		return Strings::replace($presenter, '/(^|:)+(.)/', function ($match) {
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
		if (substr($name, -7) == 'Factory') {
			$name = substr($name, 0, -7);
		}

		return Strings::replace(substr($name, 0, -9), '/(^|\\.)+(.)/', function ($match) {
			return ('.' === $match[1] ? ':' : '') . strtoupper($match[2]);
		});
	}


	/**
	 * @param $name
	 * @return string
	 */
	public function getPresenterClass(& $name)
	{
		$service = $this->formatServiceNameFromPresenter($name);

		if (isset($this->presentersByName[$service])) {
			return $this->presentersByName[$service];
		} else if (isset($this->presentersByName[$service . 'Factory'])) {
			return $this->presentersByName[$service . 'Factory'];
		} else {
			return parent::getPresenterClass($name);
		}
	}


	/**
	 * @param $presenter
	 * @return string
	 */
	public function formatPresenterClass($presenter)
	{
		$name = $this->formatServiceNameFromPresenter($presenter);

		if (isset($this->presentersByName[$name])) {
			return get_class($this->container->getService($name));
		} else {
			return parent::formatPresenterClass($presenter);
		}
	}


	/**
	 * @param $class
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		if (isset($this->presentersByClass[$class])) {
			$name = $this->presentersByClass[$class];
			return $this->formatPresenterFromServiceName($name);
		} else {
			return parent::unformatPresenterClass($class);
		}
	}


	/**
	 * @param $presenter
	 * @return string
	 */
	public function formatPresenterFile($presenter)
	{
		$service = $this->formatPresenterFromServiceName($presenter);

		if ($this->container->hasService($service)) {
			return get_class($this->container->getService($service));
		}

		return parent::formatPresenterFile($presenter);
	}
}

