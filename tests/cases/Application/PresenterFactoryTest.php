<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Tests\Application;

use Venne;
use Venne\Testing\TestCase;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PresenterFactoryTest extends TestCase
{


	/** @var Venne\Application\PresenterFactory */
	protected $presenterFactory;


	public function setUp()
	{
		$container = $this->getContext();
		$presenter = new \TestModule\HomePresenterClass($container);
		if (!$container->hasService('test.homePresenter')) {
			$container->addService('test.homePresenter', $presenter);
		}

		$this->presenterFactory = new Venne\Application\PresenterFactory(__DIR__, $container);
		$this->presenterFactory->addPresenter('TestModule\HomePresenterClass', 'test.homePresenter');
	}


	/**
	 * @return array
	 */
	public function dataServiceNamesAndPresenters()
	{
		return array(
			array('FooPackage:Foo', 'fooPackage.fooPresenter'),
			array('BarPackage:FooFoo', 'barPackage.fooFooPresenter'),
			array('BarPackage:Foo:FooBar', 'barPackage.foo.fooBarPresenter'),
			array('MocksBazPackage:Foo:FooBar', 'mocksBazPackage.foo.fooBarPresenter'),
		);
	}


	/**
	 * @dataProvider dataServiceNamesAndPresenters
	 *
	 * @param string $presenterName
	 * @param string $serviceName
	 */
	public function testServiceNameFormating($presenterName, $serviceName)
	{
		$this->assertEquals($serviceName, $this->presenterFactory->formatServiceNameFromPresenter($presenterName), 'Formating service name from presenter name');
	}


	public function testCreatePresenter()
	{
		$this->assertInstanceOf('TestModule\HomePresenterClass', $this->presenterFactory->createPresenter('Test:Home'), 'Creating presenter from service');
		$this->assertInstanceOf('TestModule\MainPresenter', $this->presenterFactory->createPresenter('Test:Main'), 'Creating presenter from class');
	}
}

namespace TestModule;

class HomePresenterClass extends \Nette\Application\UI\Presenter
{

}

class MainPresenter extends \Nette\Application\UI\Presenter
{

}

