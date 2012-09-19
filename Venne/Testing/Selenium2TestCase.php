<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Testing;

use Venne;
use Nette;
use Nette\ObjectMixin;

/**
 * @author     Josef Kříž
 */
abstract class Selenium2TestCase extends \PHPUnit_Extensions_Selenium2TestCase
{

	/** @var \Nette\DI\Container */
	protected $context;


	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		$this->context = Configurator::getTestsContainer();
		parent::__construct($name, $data, $dataName);
	}


	/**
	 * @return \Nette\DI\Container
	 */
	public function getContext()
	{
		return $this->context;
	}


	protected function setUp()
	{
		$parameters = $this->getContext()->parameters;

		if (!isset($parameters['selenium'])) {
			throw new \Nette\InvalidArgumentException("Set 'selenium' parameters in config.neon");
		}

		if (!isset($parameters['selenium']['browser'])) {
			throw new \Nette\InvalidArgumentException("'selenium.browser' is not set in config.neon");
		}

		if (!isset($parameters['selenium']['browserUrl'])) {
			throw new \Nette\InvalidArgumentException("'selenium.browserUrl' is not set in config.neon");
		}

		$this->setBrowser($parameters['selenium']['browser']);
		$this->setBrowserUrl($parameters['selenium']['browserUrl']);

		if (isset($parameters['selenium']['screenshotPath']) && isset($parameters['selenium']['screenshotUrl'])) {
			$this->captureScreenshotOnFailure = TRUE;
			$this->screenshotPath = $parameters['selenium']['screenshotPath'];
			$this->screenshotUrl = $parameters['selenium']['screenshotUrl'];
		}

		$this->basePath = $parameters['selenium']['browserUrl'];
	}


	/********************* Nette\Object behaviour ****************d*g**/


	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new Nette\Reflection\ClassType(get_called_class());
	}


	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		ObjectMixin::set($this, $name, $value);
	}


	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}


	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}
}
