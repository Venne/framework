<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tests\Module;

use Venne;
use Venne\Testing\TestCase;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ModuleTest extends TestCase
{


	/** @var \Venne\Module\Module */
	protected $module;



	public function setup()
	{
		$this->module = new \TestModule\Module;
	}

	
	/**
	 * @return array
	 */
	public function dataModules()
	{
		return array(
			array(new \TestModule\Module(), 'test'),
			array(new \Test2Module\Module(), 'test2'),
			array(new \Test3Module\Module(), 'test4'),
		);
	}


	/**
	 * @dataProvider dataModules
	 *
	 * @param Venne\Module\IModule $module
	 * @param string $name
	 */
	public function testGetName(Venne\Module\IModule $module, $name)
	{
		$this->assertEquals($name, $module->getName());
	}



	public function testGetPath()
	{
		$this->assertEquals(__DIR__, $this->module->getPath());
	}

}

namespace TestModule;

class Module extends \Venne\Module\Module
{
	
}

namespace Test2Module;

class Module extends \Venne\Module\Module
{
	
}

namespace Test3Module;

class Module extends \Venne\Module\Module
{
	protected $name = "test4";
}

