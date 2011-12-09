<?php

/**
 * Venne:CMS (version 2.0-dev released on $WCDATE$)
 *
 * Copyright (c) 2011 Josef Kříž pepakriz@gmail.com
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Testing;


/**
 * @author     Josef Kříž
 */
class TestCase extends \PHPUnit_Framework_TestCase {

	/** @var Venne\DI\Container */
	protected $context;
	
	/** @var \Venne\Configurator */
	protected $configurator;
	
	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		$this->configurator = \Nette\Environment::getConfigurator();
		$this->context = \Nette\Environment::getContext();
		parent::__construct($name, $data, $dataName);
	}

}
