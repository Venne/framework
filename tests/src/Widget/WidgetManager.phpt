<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace VenneTests\Widget;

use Nette\DI\Container;
use Tester\Assert;
use Venne\Widget\WidgetManager;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WidgetManagerTest extends \Tester\TestCase
{


	/** @var WidgetManager */
	protected $widgetManager;


	public function setUp()
	{
		$container = new Container;
		$container->addService('FooControlFactory', new FooControlFactory);
		$container->addService('BarControlFactory', ($factory = new BarControlFactory));

		$this->widgetManager = new \Venne\Widget\WidgetManager($container);
		$this->widgetManager->addWidget('foo', 'FooControlFactory');
		$this->widgetManager->addWidget('bar', $factory);
	}


	public function testGetWidget()
	{
		Assert::type('VenneTests\Widget\FooControl', $this->widgetManager->getWidget('foo'));
		Assert::type('VenneTests\Widget\BarControl', $this->widgetManager->getWidget('bar'));
	}

	public function testHasWidget()
	{
		Assert::true($this->widgetManager->hasWidget('foo'));
		Assert::true($this->widgetManager->hasWidget('bar'));
		Assert::false($this->widgetManager->hasWidget('car'));
	}


	public function testAddWidget()
	{
		Assert::exception(function(){
			$this->widgetManager->addWidget(123, 'test');
		}, 'Nette\\InvalidArgumentException', 'Name of widget must be string');

		Assert::exception(function(){
			$this->widgetManager->addWidget('car', 111);
		}, 'Nette\\InvalidArgumentException', "Second argument must be string or factory");

		Assert::exception(function(){
			$this->widgetManager->addWidget('car', 'test');
		}, 'Nette\\InvalidArgumentException', "Service 'test' does not exist");
	}

}

class FooControl extends \Nette\Application\UI\Control {}
class BarControl extends \Nette\Application\UI\Control {}

class FooControlFactory {
	public function create() { return new FooControl; }
}

class BarControlFactory {
	public function create() { return new BarControl; }
}

$testCache = new WidgetManagerTest;
$testCache->run();
