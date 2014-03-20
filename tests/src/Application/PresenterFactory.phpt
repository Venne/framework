<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Application;

use Nette\Configurator;
use Tester\Assert;
use Venne\Application\PresenterFactory;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PresenterFactoryTest extends \Tester\TestCase
{


	/** @var PresenterFactory */
	private $presenterFactory;


	public function setUp()
	{
		$configurator = new Configurator;
		$configurator->setTempDirectory(TEMP_DIR);

		$container = $configurator->createContainer();
		$container->addService('test.homePresenter', new HomePresenterClass($container));
		$container->addService('test.mainPresenter', new MainPresenter($container));

		$this->presenterFactory = new PresenterFactory(__DIR__, $container);
		$this->presenterFactory->addPresenter('HomePresenterClass', 'test.homePresenter');
		$this->presenterFactory->addPresenter('MainPresenter', 'test.mainPresenter');
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
	public function testServiceNameFormatting($presenterName, $serviceName)
	{
		Assert::equal($serviceName, $this->presenterFactory->formatServiceNameFromPresenter($presenterName), 'Formating service name from presenter name');
	}


	public function testCreatePresenter()
	{
		Assert::true($this->presenterFactory->createPresenter('Test:Home') instanceof HomePresenterClass);
		Assert::true($this->presenterFactory->createPresenter('Test:Main') instanceof MainPresenter);
	}
}

class HomePresenterClass extends \Nette\Application\UI\Presenter
{

}

class MainPresenter extends \Nette\Application\UI\Presenter
{

}

$testCache = new PresenterFactoryTest;
$testCache->run();
